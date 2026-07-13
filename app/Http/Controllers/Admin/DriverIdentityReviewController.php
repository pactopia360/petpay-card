<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Repartidor\DriverIdentityDocument;
use App\Models\Repartidor\DriverIdentityProfile;
use App\Services\Repartidor\DriverDocumentAiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class DriverIdentityReviewController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = trim($request->string('search')->toString());

        $profiles = DriverIdentityProfile::query()
            ->with([
                'driver',
                'documents' => fn ($query) => $query
                    ->whereIn('status', ['pending', 'approved', 'rejected'])
                    ->latest('id'),
            ])
            ->when(
                in_array($status, [
                    'submitted',
                    'under_review',
                    'corrections_required',
                    'approved',
                    'rejected',
                ], true),
                fn ($query) => $query->where('status', $status)
            )
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('driver', function ($driverQuery) use ($search): void {
                    $driverQuery
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                });
            })
            ->orderByRaw("
                CASE status
                    WHEN 'submitted' THEN 1
                    WHEN 'under_review' THEN 2
                    WHEN 'corrections_required' THEN 3
                    WHEN 'rejected' THEN 4
                    WHEN 'approved' THEN 5
                    ELSE 6
                END
            ")
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.driver-identities.index', [
            'profiles' => $profiles,
            'statusFilter' => $status,
            'search' => $search,
        ]);
    }

    public function show(DriverIdentityProfile $profile): View
    {
        $profile->load([
            'driver.primaryAddress',
            'driver.emergencyContacts',
            'driver.personalReferences',
            'documents' => fn ($query) => $query
                ->whereIn('status', ['pending', 'approved', 'rejected'])
                ->latest('id'),
            'events',
        ]);

        $currentDocuments = $profile->documents
            ->groupBy('document_type')
            ->map(fn ($documents) => $documents->sortByDesc('id')->first());

        $missingApprovedDocuments =
            $profile->missingApprovedDocumentTypes();

        return view('admin.driver-identities.show', [
            'profile' => $profile,
            'driver' => $profile->driver,
            'currentDocuments' => $currentDocuments,
            'missingApprovedDocuments' => $missingApprovedDocuments,
            'canApproveComplete' => $missingApprovedDocuments === [],
        ]);
    }

    public function startReview(
        Request $request,
        DriverIdentityProfile $profile
    ): RedirectResponse {
        abort_unless(
            in_array($profile->status, ['submitted', 'under_review'], true),
            422,
            'Este expediente no puede iniciar revisión en su estado actual.'
        );

        if ($profile->status === 'submitted') {
            $profile->forceFill([
                'status' => 'under_review',
                'review_started_at' => now(),
                'review_notes' => null,
            ])->save();
        }

        $this->event(
            $profile,
            $request,
            'admin_review_started',
            'Admin inició la revisión del expediente.'
        );

        return back()->with('status', 'Revisión administrativa iniciada.');
    }

    public function analyze(
        Request $request,
        DriverIdentityProfile $profile,
        DriverIdentityDocument $document,
        DriverDocumentAiService $service
    ): JsonResponse {
        $this->authorizeDocument($profile, $document);

        abort_if(
            $document->status === 'replaced',
            422,
            'No se puede analizar una versión reemplazada.'
        );

        if ($document->analysis_status === 'processing') {
            return response()->json([
                'ok' => false,
                'message' => 'El documento ya se está analizando.',
            ], 409);
        }

        try {
            $analysis = $service->analyze($document, $profile);
            $fresh = $document->fresh() ?? $document;

            $this->event(
                $profile,
                $request,
                'document_ai_analyzed',
                'Admin ejecutó la validación IA del documento.',
                [
                    'document_id' => $document->id,
                    'document_type' => $document->document_type,
                    'analysis_status' => $fresh->analysis_status,
                ]
            );

            return response()->json([
                'ok' => true,
                'message' => 'Documento analizado correctamente.',
                'document' => $this->documentPayload($fresh),
                'analysis' => $analysis,
            ]);
        } catch (Throwable $exception) {
            $fresh = $document->fresh() ?? $document;

            return response()->json([
                'ok' => false,
                'message' => $exception->getMessage(),
                'document' => $this->documentPayload($fresh),
            ], 422);
        }
    }

    public function reviewDocument(
        Request $request,
        DriverIdentityProfile $profile,
        DriverIdentityDocument $document
    ): RedirectResponse {
        $this->authorizeDocument($profile, $document);

        $validated = $request->validate([
            'decision' => [
                'required',
                Rule::in(['approved', 'rejected', 'pending']),
            ],
            'review_notes' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        if (
            in_array($validated['decision'], ['rejected', 'pending'], true)
            && blank($validated['review_notes'] ?? null)
        ) {
            return back()->withErrors([
                'review_notes' =>
                    'Escribe una observación para rechazar o solicitar corrección.',
            ]);
        }

        $document->forceFill([
            'status' => $validated['decision'],
            'review_notes' => trim((string) ($validated['review_notes'] ?? '')) ?: null,
            'reviewed_at' => now(),
            'reviewed_by' => $request->user('admin')?->id,
        ])->save();

        $this->event(
            $profile,
            $request,
            'document_reviewed',
            'Admin revisó un documento del expediente.',
            [
                'document_id' => $document->id,
                'document_type' => $document->document_type,
                'decision' => $validated['decision'],
            ]
        );

        return back()->with('status', 'Documento actualizado correctamente.');
    }

    public function corrections(
        Request $request,
        DriverIdentityProfile $profile
    ): RedirectResponse {
        $validated = $request->validate([
            'review_notes' => [
                'required',
                'string',
                'min:10',
                'max:3000',
            ],
        ]);

        $profile->forceFill([
            'status' => 'corrections_required',
            'review_notes' => trim($validated['review_notes']),
            'approved_at' => null,
            'approved_by' => null,
            'rejected_at' => null,
            'rejected_by' => null,
            'identity_locked_at' => null,
            'identity_hash' => null,
        ])->save();

        $this->event(
            $profile,
            $request,
            'corrections_required',
            'Admin solicitó correcciones al repartidor.',
            ['notes' => $validated['review_notes']]
        );

        return back()->with('status', 'Correcciones solicitadas al repartidor.');
    }

    public function approve(
        Request $request,
        DriverIdentityProfile $profile
    ): RedirectResponse {
        $profile->load('documents');

        $missing = $profile->missingApprovedDocumentTypes();

        if ($missing !== []) {
            return back()->withErrors([
                'profile' =>
                    'No se puede aprobar. Faltan documentos aprobados: '.
                    implode(', ', $missing).'.',
            ]);
        }

        $identityHash = hash(
            'sha256',
            collect($profile->documents)
                ->where('status', 'approved')
                ->sortBy('document_type')
                ->map(fn ($document) =>
                    $document->document_type.':'.$document->sha256
                )
                ->implode('|')
        );

        DB::connection('mysql_repartidor')->transaction(
            function () use ($request, $profile, $identityHash): void {
                $profile->forceFill([
                    'status' => 'approved',
                    'review_notes' => null,
                    'approved_at' => now(),
                    'approved_by' => $request->user('admin')?->id,
                    'rejected_at' => null,
                    'rejected_by' => null,
                    'identity_locked_at' => now(),
                    'identity_hash' => $identityHash,
                ])->save();

                $this->event(
                    $profile,
                    $request,
                    'identity_approved',
                    'Admin aprobó el expediente de identidad.'
                );
            }
        );

        return redirect()
            ->route('admin.driver-identities.show', $profile)
            ->with('status', 'Expediente aprobado correctamente.');
    }

    public function reject(
        Request $request,
        DriverIdentityProfile $profile
    ): RedirectResponse {
        $validated = $request->validate([
            'review_notes' => [
                'required',
                'string',
                'min:10',
                'max:3000',
            ],
        ]);

        $profile->forceFill([
            'status' => 'rejected',
            'review_notes' => trim($validated['review_notes']),
            'approved_at' => null,
            'approved_by' => null,
            'rejected_at' => now(),
            'rejected_by' => $request->user('admin')?->id,
            'identity_locked_at' => null,
            'identity_hash' => null,
        ])->save();

        $this->event(
            $profile,
            $request,
            'identity_rejected',
            'Admin rechazó el expediente de identidad.',
            ['notes' => $validated['review_notes']]
        );

        return back()->with('status', 'Expediente rechazado.');
    }

    public function document(
        DriverIdentityProfile $profile,
        DriverIdentityDocument $document
    ): BinaryFileResponse {
        $this->authorizeDocument($profile, $document);

        $disk = Storage::disk('local');

        abort_unless($disk->exists($document->path), 404);

        $absolutePath = $disk->path($document->path);

        abort_unless(
            is_file($absolutePath) && is_readable($absolutePath),
            404
        );

        $safeName = str_replace(
            ['"', "\r", "\n"],
            '',
            $document->original_name
        );

        return new BinaryFileResponse(
            $absolutePath,
            200,
            [
                'Content-Type' =>
                    $document->mime_type ?: 'application/octet-stream',
                'Content-Disposition' =>
                    'inline; filename="'.$safeName.'"',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' =>
                    'private, no-store, no-cache, must-revalidate',
            ],
            true,
            null,
            true,
            false
        );
    }

    private function authorizeDocument(
        DriverIdentityProfile $profile,
        DriverIdentityDocument $document
    ): void {
        abort_unless(
            (int) $document->identity_profile_id === (int) $profile->id
            && (int) $document->driver_user_id === (int) $profile->driver_user_id,
            404
        );
    }

    private function documentPayload(
        DriverIdentityDocument $document
    ): array {
        return [
            'id' => $document->id,
            'document_type' => $document->document_type,
            'analysis_status' => $document->analysis_status,
            'analysis_confidence' => $document->analysis_confidence,
            'quality_score' => $document->quality_score,
            'detected_document_type' => $document->detected_document_type,
            'face_detected' => $document->face_detected,
            'face_count' => $document->face_count,
            'extracted_data' => $document->extracted_data ?? [],
            'validation_results' => $document->validation_results ?? [],
            'warnings' => $document->analysis_warnings ?? [],
            'analysis_error' => $document->analysis_error,
            'analyzed_at' => $document->analyzed_at?->toIso8601String(),
            'requires_manual_review' => $document->requiresManualReview(),
        ];
    }

    private function event(
        DriverIdentityProfile $profile,
        Request $request,
        string $type,
        string $description,
        array $metadata = []
    ): void {
        $profile->events()->create([
            'driver_user_id' => $profile->driver_user_id,
            'event_type' => $type,
            'actor_type' => 'admin',
            'actor_id' => $request->user('admin')?->id,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => substr(
                (string) $request->userAgent(),
                0,
                1000
            ),
            'occurred_at' => now(),
        ]);
    }
}


