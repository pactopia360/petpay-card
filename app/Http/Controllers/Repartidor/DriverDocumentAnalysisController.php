<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\Controller;
use App\Models\Repartidor\DriverIdentityDocument;
use App\Models\Repartidor\DriverIdentityProfile;
use App\Models\Repartidor\DriverUser;
use App\Services\Repartidor\DriverDocumentAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class DriverDocumentAnalysisController extends Controller
{
    public function analyze(
        Request $request,
        DriverIdentityDocument $document,
        DriverDocumentAiService $service
    ): JsonResponse {
        $driver = $this->driver($request);

        $this->authorizeDocument($document, $driver);

        $profile = DriverIdentityProfile::query()
            ->where('driver_user_id', $driver->id)
            ->firstOrFail();

        if ($profile->isLocked()) {
            return response()->json([
                'ok' => false,
                'message' => 'El expediente aprobado está bloqueado.',
            ], 422);
        }

        if ($document->analysis_status === 'processing') {
            return response()->json([
                'ok' => false,
                'message' => 'El documento ya se está analizando.',
            ], 409);
        }

        try {
            $analysis = $service->analyze(
                $document,
                $profile
            );

            $freshDocument = $document->fresh();

            if (! $freshDocument instanceof DriverIdentityDocument) {
                $freshDocument = $document;
            }

            return response()->json([
                'ok' => true,
                'message' => 'Documento analizado correctamente.',
                'document' => $this->documentPayload(
                    $freshDocument
                ),
                'analysis' => $analysis,
            ]);
        } catch (Throwable $exception) {
            $freshDocument = $document->fresh();

            if (! $freshDocument instanceof DriverIdentityDocument) {
                $freshDocument = $document;
            }

            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
                'document' => $this->documentPayload(
                    $freshDocument
                ),
            ], 422);
        }
    }

    public function result(
        Request $request,
        DriverIdentityDocument $document
    ): JsonResponse {
        $driver = $this->driver($request);

        $this->authorizeDocument($document, $driver);

        return response()->json([
            'ok' => true,
            'document' => $this->documentPayload($document),
        ]);
    }

    private function driver(Request $request): DriverUser
    {
        $driver = $request->user('repartidor');

        abort_unless(
            $driver instanceof DriverUser,
            401
        );

        return $driver;
    }

    private function authorizeDocument(
        DriverIdentityDocument $document,
        DriverUser $driver
    ): void {
        abort_unless(
            (int) $document->driver_user_id
                === (int) $driver->id,
            404
        );
    }

    private function documentPayload(
        DriverIdentityDocument $document
    ): array {
        return [
            'id' => $document->id,
            'document_type' => $document->document_type,
            'detected_document_type' =>
                $document->detected_document_type,
            'analysis_status' =>
                $document->analysis_status,
            'analysis_confidence' =>
                $document->analysis_confidence,
            'quality_score' =>
                $document->quality_score,
            'image_width' =>
                $document->image_width,
            'image_height' =>
                $document->image_height,
            'face_detected' =>
                $document->face_detected,
            'face_count' =>
                $document->face_count,
            'extracted_data' =>
                $document->extracted_data ?? [],
            'validation_results' =>
                $document->validation_results ?? [],
            'warnings' =>
                $document->analysis_warnings ?? [],
            'analysis_error' =>
                $document->analysis_error,
            'analyzed_at' =>
                $document->analyzed_at?->toIso8601String(),
            'requires_manual_review' =>
                $document->requiresManualReview(),
        ];
    }
}
