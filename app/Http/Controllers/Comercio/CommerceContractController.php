<?php

namespace App\Http\Controllers\Comercio;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceContract;
use App\Models\Comercio\CommerceContractDocument;
use App\Models\Comercio\CommerceContractEvent;
use App\Models\Comercio\CommerceIdentityProfile;
use App\Models\Comercio\CommerceUser;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use RuntimeException;
use ZipArchive;

class CommerceContractController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $commerce = $this->commerce();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'contract_type' => ['required', 'string', 'max:100'],
            'group_key' => ['required', Rule::in(['corporate', 'compliance', 'financial'])],
            'version' => ['required', 'string', 'max:30'],
            'branch_id' => ['nullable', 'integer'],
            'representative_name' => ['nullable', 'string', 'max:190'],
            'representative_email' => ['nullable', 'email', 'max:190'],
            'representative_position' => ['nullable', 'string', 'max:120'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'original_file' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:15360'],
        ]);

        $path = $request->file('original_file')
            ? $request->file('original_file')->store('commerce/contracts/originals', 'local')
            : null;

        $contract = CommerceContract::query()->create([
            'uuid' => (string) Str::uuid(),
            'commerce_user_id' => $commerce->id,
            'branch_id' => $validated['branch_id'] ?? null,
            'template_key' => null,
            'group_key' => $validated['group_key'],
            'is_required' => false,
            'title' => $validated['title'],
            'contract_type' => $validated['contract_type'],
            'version' => $validated['version'],
            'document_year' => (int) now()->format('Y'),
            'sort_order' => 999,
            'status' => 'draft',
            'representative_name' => $validated['representative_name'] ?? null,
            'representative_email' => $validated['representative_email'] ?? null,
            'representative_position' => $validated['representative_position'] ?? null,
            'original_path' => $path,
            'effective_from' => $validated['effective_from'] ?? null,
            'effective_to' => $validated['effective_to'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $this->event($contract, 'created', 'Contrato creado.');

        return redirect()
            ->route('comercio.dashboard', ['tab' => 'contratos'])
            ->with('status', 'Contrato creado correctamente.');
    }

    public function uploadDocument(Request $request, CommerceContract $contract): RedirectResponse
    {
        $commerce = $this->commerce();
        $this->authorizeContract($contract, $commerce);

        $validated = $request->validate([
            'document_type' => ['required', 'string', 'max:100'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:15360'],
        ]);

        $file = $request->file('document');
        $path = $file->store('commerce/contracts/documents', 'local');

        CommerceContractDocument::query()->create([
            'contract_id' => $contract->id,
            'commerce_user_id' => $commerce->id,
            'document_type' => $validated['document_type'],
            'name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'sha256' => hash_file('sha256', $file->getRealPath()),
            'status' => 'pending',
        ]);

        $this->event($contract, 'document_uploaded', 'Documento cargado: '.$file->getClientOriginalName());

        return back()->with('status', 'Documento cargado correctamente.');
    }

    public function submit(CommerceContract $contract): RedirectResponse
    {
        $commerce = $this->commerce();
        $this->authorizeContract($contract, $commerce);

        $contract->update([
            'status' => 'pending_review',
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);

        $this->event($contract, 'submitted', 'Contrato enviado a revisión.');

        return back()->with('status', 'Contrato enviado a revisión.');
    }

    public function sign(Request $request, CommerceContract $contract): RedirectResponse
    {
        $commerce = $this->commerce();
        $this->authorizeContract($contract, $commerce);

        abort_unless(in_array($contract->status, ['pending_signature', 'draft'], true), 422);

        $identityProfile = CommerceIdentityProfile::query()
            ->with('documents')
            ->where('commerce_user_id', $commerce->id)
            ->first();

        abort_unless(
            $identityProfile?->isReadyForSignature(),
            422,
            'La firma está bloqueada hasta que Admin apruebe la identidad, documentos y representación legal.'
        );

        $validated = $request->validate([
            'acceptance' => ['accepted'],
            'representative_name' => ['required', 'string', 'max:190'],
            'representative_position' => ['nullable', 'string', 'max:120'],
            'signature_method' => ['required', Rule::in(['drawn', 'camera', 'uploaded', 'certificate'])],
            'signature_data' => ['nullable', 'string'],
            'camera_data' => ['nullable', 'string'],
            'signature_file' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:5120'],
            'cer_file' => ['nullable', 'file', 'max:5120'],
            'key_file' => ['nullable', 'file', 'max:5120'],
            'key_password' => ['nullable', 'string', 'max:255'],
        ]);

        $method = $validated['signature_method'];
        $signaturePath = null;
        $cameraPath = null;
        $certificateData = [];
        $cryptographicSignature = null;

        if ($method === 'drawn') {
            $signaturePath = $this->storeDataUrl(
                (string) ($validated['signature_data'] ?? ''),
                'commerce/contracts/signatures',
                'firma'
            );
        }

        if ($method === 'camera') {
            $cameraPath = $this->storeDataUrl(
                (string) ($validated['camera_data'] ?? ''),
                'commerce/contracts/evidence',
                'camara'
            );
        }

        if ($method === 'uploaded') {
            abort_unless($request->hasFile('signature_file'), 422, 'Selecciona una imagen de firma.');
            $signaturePath = $request->file('signature_file')->store('commerce/contracts/signatures', 'local');
        }

        if ($method === 'certificate') {
            abort_unless(
                $request->hasFile('cer_file')
                && $request->hasFile('key_file')
                && filled($validated['key_password'] ?? null),
                422,
                'Carga el certificado .cer, la llave .key y su contraseña.'
            );

            [$certificateData, $cryptographicSignature] = $this->signWithCertificate(
                $request->file('cer_file')->getRealPath(),
                $request->file('key_file')->getRealPath(),
                (string) $validated['key_password'],
                $this->signaturePayload($contract, $validated['representative_name'])
            );

            abort_unless(
                strtoupper((string) ($certificateData['rfc'] ?? '')) === strtoupper((string) $identityProfile->representative_rfc),
                422,
                'El RFC del certificado no coincide con el representante legal aprobado.'
            );
        }

        $signedAt = now();
        $contentHash = hash('sha256', $this->signaturePayload(
            $contract,
            $validated['representative_name'],
            $signedAt->toIso8601String()
        ));

        $contract->update([
            'status' => 'signed',
            'representative_name' => $validated['representative_name'],
            'representative_position' => $validated['representative_position'] ?? $contract->representative_position,
            'signature_method' => $method,
            'signature_image_path' => $signaturePath,
            'camera_evidence_path' => $cameraPath,
            'certificate_rfc' => $certificateData['rfc'] ?? null,
            'certificate_serial' => $certificateData['serial'] ?? null,
            'certificate_subject' => $certificateData['subject'] ?? null,
            'certificate_valid_from' => $certificateData['valid_from'] ?? null,
            'certificate_valid_to' => $certificateData['valid_to'] ?? null,
            'cryptographic_signature' => $cryptographicSignature,
            'signature_metadata' => [
                'method' => $method,
                'accepted_at' => $signedAt->toIso8601String(),
                'ip' => $request->ip(),
                'user_agent' => (string) $request->userAgent(),
                'identity_profile_uuid' => $identityProfile->uuid,
                'identity_hash' => $identityProfile->identity_hash,
            ],
            'signed_at' => $signedAt,
            'signed_ip' => $request->ip(),
            'signed_user_agent' => (string) $request->userAgent(),
            'content_hash' => $contentHash,
        ]);

        $contract->refresh();
        $contract->update(['signed_path' => $this->generateSignedPdf($contract)]);

        $this->event($contract, 'signed', 'Contrato firmado.', [
            'ip' => $request->ip(),
            'method' => $method,
            'content_hash' => $contentHash,
            'certificate_rfc' => $certificateData['rfc'] ?? null,
            'certificate_serial' => $certificateData['serial'] ?? null,
        ]);

        return redirect()
            ->route('comercio.dashboard', ['tab' => 'contratos'])
            ->with('status', 'Contrato firmado y PDF generado correctamente.');
    }

    public function evidence(CommerceContract $contract, string $type)
    {
        $commerce = $this->commerce();
        $this->authorizeContract($contract, $commerce);

        $path = match ($type) {
            'signature' => $contract->signature_image_path,
            'camera' => $contract->camera_evidence_path,
            default => null,
        };

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless(
            is_string($path)
            && $path !== ''
            && $disk->exists($path),
            404
        );

        return $disk->response($path);
    }

    public function download(CommerceContract $contract, string $type = 'original')
    {
        $commerce = $this->commerce();
        $this->authorizeContract($contract, $commerce);

        $path = $type === 'signed'
            ? $contract->signed_path
            : $contract->original_path;

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless(
            is_string($path)
            && $path !== ''
            && $disk->exists($path),
            404
        );

        return $disk->download($path);
    }

    public function downloadZip()
    {
        $commerce = $this->commerce();

        $contracts = CommerceContract::query()
            ->with('documents')
            ->where('commerce_user_id', $commerce->id)
            ->get();

        $tempPath = storage_path('app/private/commerce/contracts/zip');
        if (! is_dir($tempPath)) {
            mkdir($tempPath, 0775, true);
        }

        $zipPath = $tempPath.'/contratos_'.$commerce->id.'_'.now()->format('YmdHis').'.zip';
        $zip = new ZipArchive();

        abort_unless($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true, 500);

        foreach ($contracts as $contract) {
            foreach (['original_path' => 'original', 'signed_path' => 'firmado'] as $field => $label) {
                $path = $contract->{$field};

                if ($path && Storage::disk('local')->exists($path)) {
                    $extension = pathinfo($path, PATHINFO_EXTENSION) ?: 'bin';
                    $zip->addFromString(
                        Str::slug($contract->title).'_'.$label.'.'.$extension,
                        Storage::disk('local')->get($path)
                    );
                }
            }

            foreach ($contract->documents as $document) {
                if (Storage::disk('local')->exists($document->path)) {
                    $zip->addFromString(
                        Str::slug($contract->title).'/'.$document->name,
                        Storage::disk('local')->get($document->path)
                    );
                }
            }
        }

        $zip->close();

        return response()
            ->download($zipPath, 'contratos-petpay-'.now()->format('Y').'.zip')
            ->deleteFileAfterSend(true);
    }

    public function destroy(CommerceContract $contract): RedirectResponse
    {
        $commerce = $this->commerce();
        $this->authorizeContract($contract, $commerce);

        abort_if($contract->status === 'signed', 422, 'Un contrato firmado no puede eliminarse.');

        $contract->delete();

        return back()->with('status', 'Contrato eliminado.');
    }


    private function generateSignedPdf(CommerceContract $contract): string
    {
        $pdf = Pdf::loadView('comercio.contracts.signed-pdf', [
            'contract' => $contract,
            'signatureImage' => $this->imageAsDataUrl($contract->signature_image_path),
            'cameraEvidence' => $this->imageAsDataUrl($contract->camera_evidence_path),
        ])->setPaper('letter');

        $path = 'commerce/contracts/signed/'.Str::slug($contract->title).'-'.$contract->uuid.'.pdf';
        Storage::disk('local')->put($path, $pdf->output());

        return $path;
    }

    private function imageAsDataUrl(?string $path): ?string
    {
        if (! is_string($path) || $path === '') {
            return null;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        if (! $disk->exists($path)) {
            return null;
        }

        $mime = $disk->mimeType($path);

        if (! is_string($mime) || $mime === '') {
            $mime = 'image/png';
        }

        return 'data:'.$mime.';base64,'.base64_encode(
            $disk->get($path)
        );
    }

    private function storeDataUrl(string $dataUrl, string $directory, string $prefix): string
    {
        abort_unless(
            preg_match('/^data:image\/(png|jpeg);base64,(.+)$/', $dataUrl, $matches) === 1,
            422,
            'La evidencia capturada no es válida.'
        );

        $binary = base64_decode($matches[2], true);
        abort_unless($binary !== false && strlen($binary) <= 8 * 1024 * 1024, 422, 'La imagen es inválida o demasiado grande.');

        $extension = $matches[1] === 'jpeg' ? 'jpg' : 'png';
        $path = $directory.'/'.$prefix.'-'.Str::uuid().'.'.$extension;
        Storage::disk('local')->put($path, $binary);

        return $path;
    }

    private function signWithCertificate(string $cerPath, string $keyPath, string $password, string $payload): array
    {
        $certificateDer = file_get_contents($cerPath);
        $privateKeyDer = file_get_contents($keyPath);

        if ($certificateDer === false || $privateKeyDer === false) {
            throw new RuntimeException('No fue posible leer los archivos de e.firma.');
        }

        $certificatePem = $this->derToPem($certificateDer, 'CERTIFICATE');
        $privateKeyPem = $this->derToPem($privateKeyDer, 'ENCRYPTED PRIVATE KEY');

        $certificate = openssl_x509_read($certificatePem);
        abort_unless($certificate !== false, 422, 'El archivo .cer no es válido.');

        $privateKey = openssl_pkey_get_private($privateKeyPem, $password);
        abort_unless($privateKey !== false, 422, 'La llave .key o su contraseña no son válidas.');

        $parsed = openssl_x509_parse($certificate);
        abort_unless(is_array($parsed), 422, 'No fue posible analizar el certificado.');

        $now = time();
        $validFrom = (int) ($parsed['validFrom_time_t'] ?? 0);
        $validTo = (int) ($parsed['validTo_time_t'] ?? 0);

        abort_unless($validFrom <= $now && $validTo >= $now, 422, 'El certificado no está vigente.');

        $signature = '';
        abort_unless(
            openssl_sign($payload, $signature, $privateKey, OPENSSL_ALGO_SHA256),
            422,
            'No fue posible firmar criptográficamente el contrato.'
        );

        $subjectData = $parsed['subject'] ?? [];

        $subject = $this->flattenCertificateSubject(
            is_array($subjectData) ? $subjectData : []
        );

        preg_match(
            '/[A-ZÑ&]{3,4}\d{6}[A-Z0-9]{3}/u',
            strtoupper($subject),
            $rfcMatch
        );

        return [[
            'rfc' => $rfcMatch[0] ?? null,
            'serial' => (string) ($parsed['serialNumberHex'] ?? $parsed['serialNumber'] ?? ''),
            'subject' => $subject,
            'valid_from' => date('Y-m-d H:i:s', $validFrom),
            'valid_to' => date('Y-m-d H:i:s', $validTo),
        ], base64_encode($signature)];
    }

    private function derToPem(string $der, string $label): string
    {
        return "-----BEGIN {$label}-----\n"
            .chunk_split(base64_encode($der), 64, "\n")
            ."-----END {$label}-----\n";
    }

    private function flattenCertificateSubject(array $subject): string
    {
        $parts = [];

        foreach ($subject as $key => $value) {
            $parts[] = $key.'='.(is_array($value) ? implode(', ', $value) : $value);
        }

        return implode('; ', $parts);
    }

    private function signaturePayload(CommerceContract $contract, string $representativeName, ?string $signedAt = null): string
    {
        return implode('|', [
            $contract->uuid,
            $contract->title,
            $contract->version,
            $contract->commerce_user_id,
            $representativeName,
            $signedAt ?? now()->toIso8601String(),
        ]);
    }

    private function commerce(): CommerceUser
    {
        $commerce = Auth::guard('comercio')->user();
        abort_unless($commerce instanceof CommerceUser, 401);

        return $commerce;
    }

    private function authorizeContract(CommerceContract $contract, CommerceUser $commerce): void
    {
        abort_unless((int) $contract->commerce_user_id === (int) $commerce->id, 404);
    }

    private function event(CommerceContract $contract, string $type, string $description, array $metadata = []): void
    {
        CommerceContractEvent::query()->create([
            'contract_id' => $contract->id,
            'commerce_user_id' => $contract->commerce_user_id,
            'event_type' => $type,
            'actor_type' => 'commerce',
            'actor_id' => $contract->commerce_user_id,
            'description' => $description,
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }
}
