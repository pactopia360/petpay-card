<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceIdentityDocument;
use App\Models\Comercio\CommerceIdentityEvent;
use App\Models\Comercio\CommerceIdentityProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CommerceIdentityReviewController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status', 'submitted');

        $profiles = CommerceIdentityProfile::query()
            ->with(['commerce', 'documents'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->orderByRaw("FIELD(status, 'submitted', 'under_review', 'corrections_required', 'approved', 'rejected', 'draft')")
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.approvals.identities.index', [
            'profiles' => $profiles,
            'status' => $status,
            'counts' => CommerceIdentityProfile::query()
                ->selectRaw('status, COUNT(*) total')
                ->groupBy('status')
                ->pluck('total', 'status'),
        ]);
    }

    public function startReview(CommerceIdentityProfile $profile): RedirectResponse
    {
        abort_unless(in_array($profile->status, ['submitted', 'under_review'], true), 422);

        $profile->update([
            'status' => 'under_review',
            'review_started_at' => $profile->review_started_at ?? now(),
        ]);

        $this->event($profile, 'review_started', 'Revisión iniciada.');

        return back()->with('status', 'Expediente marcado en revisión.');
    }

    public function reviewDocument(
        Request $request,
        CommerceIdentityProfile $profile,
        CommerceIdentityDocument $document
    ): RedirectResponse {
        abort_unless((int) $document->identity_profile_id === (int) $profile->id, 404);

        $validated = $request->validate([
            'decision' => ['required', 'in:approved,rejected'],
            'review_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $document->update([
            'status' => $validated['decision'],
            'review_notes' => $validated['review_notes'] ?? null,
            'reviewed_at' => now(),
            'reviewed_by' => Auth::guard('admin')->id(),
        ]);

        $profile->update([
            'status' => $validated['decision'] === 'rejected'
                ? 'corrections_required'
                : 'under_review',
            'review_notes' => $validated['decision'] === 'rejected'
                ? ($validated['review_notes'] ?? 'Documento rechazado.')
                : $profile->review_notes,
            'approved_at' => null,
            'approved_by' => null,
            'identity_locked_at' => null,
            'identity_hash' => null,
        ]);

        $this->event($profile, 'document_reviewed', 'Documento revisado.', [
            'document_id' => $document->id,
            'document_type' => $document->document_type,
            'decision' => $validated['decision'],
        ]);

        return back()->with('status', 'Documento revisado.');
    }

    public function approve(Request $request, CommerceIdentityProfile $profile): RedirectResponse
    {
        $profile->load('documents');

        $missing = collect($profile->requiredDocumentTypes())
            ->reject(fn (string $type) => $profile->documents
                ->where('document_type', $type)
                ->where('status', 'approved')
                ->isNotEmpty())
            ->values()
            ->all();

        abort_if($missing !== [], 422, 'No puedes aprobar. Faltan documentos aprobados: '.implode(', ', $missing).'.');
        abort_unless(
            filled($profile->representative_name)
            && filled($profile->representative_rfc)
            && filled($profile->business_rfc)
            && $profile->truth_declaration
            && $profile->data_processing_consent
            && $profile->powers_declared_current,
            422,
            'El expediente no tiene completos los datos o consentimientos.'
        );

        $identityHash = hash('sha256', json_encode([
            'profile' => $profile->only([
                'uuid',
                'commerce_user_id',
                'person_type',
                'business_rfc',
                'business_legal_name',
                'representative_name',
                'representative_rfc',
                'representative_curp',
                'representative_email',
                'representative_phone',
                'representative_position',
                'notarial_deed_number',
                'notary_name',
                'notary_number',
                'legal_powers_scope',
            ]),
            'documents' => $profile->documents
                ->where('status', 'approved')
                ->sortBy('document_type')
                ->map(fn ($document) => [
                    'type' => $document->document_type,
                    'sha256' => $document->sha256,
                ])
                ->values()
                ->all(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $profile->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => Auth::guard('admin')->id(),
            'rejected_at' => null,
            'rejected_by' => null,
            'review_notes' => null,
            'identity_locked_at' => now(),
            'identity_hash' => $identityHash,
        ]);

        $this->event($profile, 'approved', 'Expediente aprobado y bloqueado.', [
            'identity_hash' => $identityHash,
        ]);

        return back()->with('status', 'Expediente aprobado. El representante ya puede firmar.');
    }

    public function corrections(Request $request, CommerceIdentityProfile $profile): RedirectResponse
    {
        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'max:3000'],
        ]);

        $profile->update([
            'status' => 'corrections_required',
            'review_notes' => $validated['review_notes'],
            'approved_at' => null,
            'approved_by' => null,
            'identity_locked_at' => null,
            'identity_hash' => null,
        ]);

        $this->event($profile, 'corrections_required', 'Se solicitaron correcciones.', [
            'notes' => $validated['review_notes'],
        ]);

        return back()->with('status', 'Correcciones solicitadas.');
    }

    public function reject(Request $request, CommerceIdentityProfile $profile): RedirectResponse
    {
        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'max:3000'],
        ]);

        $profile->update([
            'status' => 'rejected',
            'review_notes' => $validated['review_notes'],
            'rejected_at' => now(),
            'rejected_by' => Auth::guard('admin')->id(),
            'approved_at' => null,
            'approved_by' => null,
            'identity_locked_at' => null,
            'identity_hash' => null,
        ]);

        $this->event($profile, 'rejected', 'Expediente rechazado.', [
            'notes' => $validated['review_notes'],
        ]);

        return back()->with('status', 'Expediente rechazado.');
    }

    public function document(CommerceIdentityDocument $document)
    {
        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless($disk->exists($document->path), 404);

        return $disk->response(
            $document->path,
            $document->original_name
        );
    }

    private function event(
        CommerceIdentityProfile $profile,
        string $type,
        string $description,
        array $metadata = []
    ): void {
        CommerceIdentityEvent::query()->create([
            'identity_profile_id' => $profile->id,
            'commerce_user_id' => $profile->commerce_user_id,
            'event_type' => $type,
            'actor_type' => 'admin',
            'actor_id' => Auth::guard('admin')->id(),
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => (string) request()->userAgent(),
            'occurred_at' => now(),
        ]);
    }
}

