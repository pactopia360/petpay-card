<?php

namespace App\Services\Repartidor;

use App\Models\Repartidor\DriverIdentityDocument;
use App\Models\Repartidor\DriverIdentityProfile;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class DriverDocumentAiService
{
    public function analyze(
        DriverIdentityDocument $document,
        DriverIdentityProfile $profile
    ): array {
        $this->assertConfiguration();

        $disk = Storage::disk('local');

        if (! $disk->exists($document->path)) {
            throw new RuntimeException('El archivo del documento no existe.');
        }

        $absolutePath = $disk->path($document->path);

        if (! is_file($absolutePath) || ! is_readable($absolutePath)) {
            throw new RuntimeException('El archivo no puede leerse.');
        }

        $size = filesize($absolutePath);

        if ($size === false || $size <= 0) {
            throw new RuntimeException('El archivo está vacío.');
        }

        $maximumBytes = (int) config(
            'services.openai.max_image_bytes',
            10 * 1024 * 1024
        );

        if ($size > $maximumBytes) {
            throw new RuntimeException(
                'El archivo excede el tamaño permitido para análisis.'
            );
        }

        $mimeType = $this->detectMimeType(
            $absolutePath,
            $document->mime_type
        );

        $localInspection = $this->inspectLocalFile(
            $absolutePath,
            $mimeType
        );

        $document->forceFill([
            'analysis_status' => 'processing',
            'analysis_provider' => 'openai',
            'analysis_model' => config('services.openai.model'),
            'analysis_error' => null,
            'image_width' => $localInspection['width'],
            'image_height' => $localInspection['height'],
        ])->save();

        try {
            $response = $this->client()->post(
                rtrim(
                    (string) config('services.openai.base_url'),
                    '/'
                ).'/responses',
                [
                    'model' => config('services.openai.model'),
                    'input' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'input_text',
                                    'text' => $this->instructions(
                                        $document,
                                        $profile,
                                        $localInspection
                                    ),
                                ],
                                $this->fileContent(
                                    $absolutePath,
                                    $mimeType,
                                    $document->original_name
                                ),
                            ],
                        ],
                    ],
                    'text' => [
                        'format' => [
                            'type' => 'json_schema',
                            'name' => 'driver_document_analysis',
                            'strict' => true,
                            'schema' => $this->schema(),
                        ],
                    ],
                    'temperature' => 0,
                    'max_output_tokens' => 2500,
                ]
            );

            if (! $response->successful()) {
                throw new RuntimeException(
                    $this->apiErrorMessage(
                        $response->status(),
                        $response->json()
                    )
                );
            }

            $payload = $response->json();

            if (! is_array($payload)) {
                throw new RuntimeException(
                    'OpenAI devolvió una respuesta inválida.'
                );
            }

            $outputText = $this->extractOutputText($payload);

            $analysis = json_decode(
                $outputText,
                true,
                512,
                JSON_THROW_ON_ERROR
            );

            if (! is_array($analysis)) {
                throw new RuntimeException(
                    'La respuesta estructurada no es válida.'
                );
            }

            $normalized = $this->normalizeAnalysis(
                $analysis,
                $document
            );

            $document->forceFill([
                'analysis_status' =>
                    $normalized['requires_manual_review']
                        ? 'manual_review'
                        : 'completed',
                'analysis_provider' => 'openai',
                'analysis_model' => config('services.openai.model'),
                'ai_response_id' => $payload['id'] ?? null,
                'detected_document_type' =>
                    $normalized['detected_document_type'],
                'analysis_confidence' =>
                    $normalized['confidence'],
                'quality_score' =>
                    $normalized['quality']['score'],
                'face_count' =>
                    $normalized['face']['count'],
                'face_detected' =>
                    $normalized['face']['detected'],
                'extracted_data' =>
                    $normalized['extracted_data'],
                'validation_results' =>
                    $normalized['validation'],
                'analysis_warnings' =>
                    $normalized['warnings'],
                'analysis_error' => null,
                'analyzed_at' => now(),
            ])->save();

            return $normalized;
        } catch (Throwable $exception) {
            $document->forceFill([
                'analysis_status' => 'failed',
                'analysis_error' => $exception->getMessage(),
                'analyzed_at' => now(),
            ])->save();

            Log::error('driver_document_ai_analysis_failed', [
                'document_id' => $document->id,
                'driver_user_id' => $document->driver_user_id,
                'document_type' => $document->document_type,
                'exception' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function client(): PendingRequest
    {
        return Http::withToken(
            (string) config('services.openai.key')
        )
            ->acceptJson()
            ->asJson()
            ->connectTimeout(
                (int) config(
                    'services.openai.connect_timeout',
                    15
                )
            )
            ->timeout(
                (int) config(
                    'services.openai.timeout',
                    90
                )
            )
            ->retry(
                2,
                1000,
                function (
                    Throwable $exception
                ): bool {
                    return $exception instanceof ConnectionException;
                }
            );
    }

    private function assertConfiguration(): void
    {
        if (! config('services.openai.enabled')) {
            throw new RuntimeException(
                'El análisis documental con IA está deshabilitado.'
            );
        }

        if (! filled(config('services.openai.key'))) {
            throw new RuntimeException(
                'No está configurada la clave de OpenAI.'
            );
        }

        if (! filled(config('services.openai.model'))) {
            throw new RuntimeException(
                'No está configurado el modelo de OpenAI.'
            );
        }
    }

    private function detectMimeType(
        string $absolutePath,
        ?string $storedMimeType
    ): string {
        $detectedMimeType = mime_content_type($absolutePath);

        if (
            is_string($detectedMimeType)
            && $detectedMimeType !== ''
        ) {
            return $detectedMimeType;
        }

        if (
            is_string($storedMimeType)
            && $storedMimeType !== ''
        ) {
            return $storedMimeType;
        }

        return 'application/octet-stream';
    }

    private function inspectLocalFile(
        string $absolutePath,
        string $mimeType
    ): array {
        $width = null;
        $height = null;

        if (str_starts_with($mimeType, 'image/')) {
            $imageSize = @getimagesize($absolutePath);

            if (is_array($imageSize)) {
                $width = isset($imageSize[0])
                    ? (int) $imageSize[0]
                    : null;

                $height = isset($imageSize[1])
                    ? (int) $imageSize[1]
                    : null;
            }
        }

        return [
            'mime_type' => $mimeType,
            'width' => $width,
            'height' => $height,
            'size_bytes' => filesize($absolutePath) ?: 0,
        ];
    }

    private function fileContent(
        string $absolutePath,
        string $mimeType,
        string $originalName
    ): array {
        $binary = file_get_contents($absolutePath);

        if ($binary === false) {
            throw new RuntimeException(
                'No fue posible leer el documento.'
            );
        }

        $base64 = base64_encode($binary);

        if (str_starts_with($mimeType, 'image/')) {
            return [
                'type' => 'input_image',
                'detail' => 'high',
                'image_url' =>
                    'data:'.$mimeType.';base64,'.$base64,
            ];
        }

        if ($mimeType === 'application/pdf') {
            return [
                'type' => 'input_file',
                'filename' => $originalName,
                'file_data' =>
                    'data:application/pdf;base64,'.$base64,
            ];
        }

        throw new RuntimeException(
            'El tipo de archivo no puede analizarse con IA: '.
            $mimeType
        );
    }

    private function instructions(
        DriverIdentityDocument $document,
        DriverIdentityProfile $profile,
        array $localInspection
    ): string {
        $expectedName = trim(
            (string) optional($profile->driver)->name
        );

        return implode("\n", [
            'Analiza este documento mexicano de identidad o expediente.',
            'No inventes valores y utiliza null cuando un dato no sea visible.',
            'Documento esperado: '.$document->document_type.'.',
            'Nombre esperado del repartidor: '.$expectedName.'.',
            'CURP esperada: '.($profile->curp ?: 'No capturada').'.',
            'Tipo MIME verificado: '.$localInspection['mime_type'].'.',
            'Resolución detectada localmente: '.
                ($localInspection['width'] ?? 'desconocida').
                'x'.
                ($localInspection['height'] ?? 'desconocida').
                '.',
            'Evalúa legibilidad, brillo, enfoque, recortes y orientación.',
            'Para selfie indica únicamente presencia, cantidad y calidad de rostros.',
            'No realices identificación biométrica definitiva.',
            'Compara nombre y CURP visibles contra los datos esperados.',
            'Responde exclusivamente con el JSON solicitado.',
        ]);
    }

    private function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => [
                'detected_document_type',
                'confidence',
                'requires_manual_review',
                'extracted_data',
                'quality',
                'face',
                'validation',
                'warnings',
            ],
            'properties' => [
                'detected_document_type' => [
                    'type' => 'string',
                    'enum' => [
                        'ine_front',
                        'ine_back',
                        'curp',
                        'proof_address',
                        'selfie',
                        'driver_license',
                        'unknown',
                    ],
                ],
                'confidence' => [
                    'type' => 'number',
                    'minimum' => 0,
                    'maximum' => 100,
                ],
                'requires_manual_review' => [
                    'type' => 'boolean',
                ],
                'extracted_data' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => [
                        'full_name',
                        'curp',
                        'date_of_birth',
                        'address',
                        'postal_code',
                        'document_number',
                        'license_number',
                        'issue_date',
                        'expiration_date',
                        'issuer',
                        'raw_text',
                    ],
                    'properties' => [
                        'full_name' => [
                            'type' => ['string', 'null'],
                        ],
                        'curp' => [
                            'type' => ['string', 'null'],
                        ],
                        'date_of_birth' => [
                            'type' => ['string', 'null'],
                        ],
                        'address' => [
                            'type' => ['string', 'null'],
                        ],
                        'postal_code' => [
                            'type' => ['string', 'null'],
                        ],
                        'document_number' => [
                            'type' => ['string', 'null'],
                        ],
                        'license_number' => [
                            'type' => ['string', 'null'],
                        ],
                        'issue_date' => [
                            'type' => ['string', 'null'],
                        ],
                        'expiration_date' => [
                            'type' => ['string', 'null'],
                        ],
                        'issuer' => [
                            'type' => ['string', 'null'],
                        ],
                        'raw_text' => [
                            'type' => ['string', 'null'],
                        ],
                    ],
                ],
                'quality' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => [
                        'score',
                        'is_readable',
                        'is_blurry',
                        'is_dark',
                        'is_cropped',
                        'orientation',
                    ],
                    'properties' => [
                        'score' => [
                            'type' => 'number',
                            'minimum' => 0,
                            'maximum' => 100,
                        ],
                        'is_readable' => [
                            'type' => 'boolean',
                        ],
                        'is_blurry' => [
                            'type' => 'boolean',
                        ],
                        'is_dark' => [
                            'type' => 'boolean',
                        ],
                        'is_cropped' => [
                            'type' => 'boolean',
                        ],
                        'orientation' => [
                            'type' => 'string',
                            'enum' => [
                                'correct',
                                'rotated_left',
                                'rotated_right',
                                'upside_down',
                                'unknown',
                            ],
                        ],
                    ],
                ],
                'face' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => [
                        'detected',
                        'count',
                        'quality',
                    ],
                    'properties' => [
                        'detected' => [
                            'type' => 'boolean',
                        ],
                        'count' => [
                            'type' => 'integer',
                            'minimum' => 0,
                            'maximum' => 20,
                        ],
                        'quality' => [
                            'type' => [
                                'string',
                                'null',
                            ],
                            'enum' => [
                                'good',
                                'acceptable',
                                'poor',
                                null,
                            ],
                        ],
                    ],
                ],
                'validation' => [
                    'type' => 'object',
                    'additionalProperties' => false,
                    'required' => [
                        'document_type_matches',
                        'name_matches',
                        'curp_matches',
                        'is_expired',
                    ],
                    'properties' => [
                        'document_type_matches' => [
                            'type' => 'boolean',
                        ],
                        'name_matches' => [
                            'type' => ['boolean', 'null'],
                        ],
                        'curp_matches' => [
                            'type' => ['boolean', 'null'],
                        ],
                        'is_expired' => [
                            'type' => ['boolean', 'null'],
                        ],
                    ],
                ],
                'warnings' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'string',
                    ],
                ],
            ],
        ];
    }

    private function extractOutputText(array $payload): string
    {
        if (
            isset($payload['output_text'])
            && is_string($payload['output_text'])
            && $payload['output_text'] !== ''
        ) {
            return $payload['output_text'];
        }

        foreach ($payload['output'] ?? [] as $outputItem) {
            if (! is_array($outputItem)) {
                continue;
            }

            foreach ($outputItem['content'] ?? [] as $contentItem) {
                if (
                    is_array($contentItem)
                    && isset($contentItem['text'])
                    && is_string($contentItem['text'])
                    && $contentItem['text'] !== ''
                ) {
                    return $contentItem['text'];
                }
            }
        }

        throw new RuntimeException(
            'OpenAI no devolvió contenido de análisis.'
        );
    }

    private function normalizeAnalysis(
        array $analysis,
        DriverIdentityDocument $document
    ): array {
        $confidence = max(
            0,
            min(
                100,
                (float) ($analysis['confidence'] ?? 0)
            )
        );

        $qualityScore = max(
            0,
            min(
                100,
                (float) data_get(
                    $analysis,
                    'quality.score',
                    0
                )
            )
        );

        $warnings = array_values(
            array_filter(
                array_map(
                    static fn ($warning): string =>
                        trim((string) $warning),
                    $analysis['warnings'] ?? []
                )
            )
        );

        $detectedType = (string) (
            $analysis['detected_document_type']
            ?? 'unknown'
        );

        $manualReview = (bool) (
            $analysis['requires_manual_review']
            ?? false
        );

        if ($confidence < 70 || $qualityScore < 60) {
            $manualReview = true;
        }

        if ($detectedType !== $document->document_type) {
            $manualReview = true;
            $warnings[] =
                'El tipo detectado no coincide con el tipo seleccionado.';
        }

        return [
            'detected_document_type' => $detectedType,
            'confidence' => round($confidence, 2),
            'requires_manual_review' => $manualReview,
            'extracted_data' => is_array(
                $analysis['extracted_data'] ?? null
            )
                ? $analysis['extracted_data']
                : [],
            'quality' => [
                'score' => round($qualityScore, 2),
                'is_readable' => (bool) data_get(
                    $analysis,
                    'quality.is_readable',
                    false
                ),
                'is_blurry' => (bool) data_get(
                    $analysis,
                    'quality.is_blurry',
                    false
                ),
                'is_dark' => (bool) data_get(
                    $analysis,
                    'quality.is_dark',
                    false
                ),
                'is_cropped' => (bool) data_get(
                    $analysis,
                    'quality.is_cropped',
                    false
                ),
                'orientation' => (string) data_get(
                    $analysis,
                    'quality.orientation',
                    'unknown'
                ),
            ],
            'face' => [
                'detected' => (bool) data_get(
                    $analysis,
                    'face.detected',
                    false
                ),
                'count' => max(
                    0,
                    (int) data_get(
                        $analysis,
                        'face.count',
                        0
                    )
                ),
                'quality' => data_get(
                    $analysis,
                    'face.quality'
                ),
            ],
            'validation' => is_array(
                $analysis['validation'] ?? null
            )
                ? $analysis['validation']
                : [],
            'warnings' => array_values(
                array_unique($warnings)
            ),
        ];
    }

    private function apiErrorMessage(
        int $status,
        mixed $payload
    ): string {
        $message = is_array($payload)
            ? data_get($payload, 'error.message')
            : null;

        if (is_string($message) && $message !== '') {
            return "OpenAI respondió HTTP {$status}: {$message}";
        }

        return "OpenAI respondió con error HTTP {$status}.";
    }
}
