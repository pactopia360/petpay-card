<?php

namespace App\Http\Controllers\Comercio;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceIdentityDocument;
use App\Models\Comercio\CommerceIdentityEvent;
use App\Models\Comercio\CommerceIdentityProfile;
use App\Models\Comercio\CommerceUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CommerceIdentityController extends Controller
{
    public function saveProfile(Request $request): RedirectResponse
    {
        $commerce = $this->commerce();
        $profile = $this->profile($commerce);

        abort_if($profile->status === 'approved', 422, 'El expediente aprobado está bloqueado.');

        $personType = (string) $request->input('person_type');

        $rules = [
            'person_type' => ['required', Rule::in(['individual', 'company'])],
            'business_rfc' => ['required', 'string', 'max:20'],
            'business_legal_name' => ['required', 'string', 'max:190'],
            'representative_name' => ['required', 'string', 'max:190'],
            'representative_rfc' => ['required', 'string', 'max:20'],
            'representative_curp' => ['required', 'string', 'max:25'],
            'representative_email' => ['required', 'email', 'max:190'],
            'representative_phone' => ['required', 'string', 'max:30'],
            'representative_position' => [$personType === 'company' ? 'required' : 'nullable', 'string', 'max:120'],
            'address_line' => ['required', 'string', 'max:500'],
            'postal_code' => ['required', 'string', 'max:10'],
            'state' => ['required', 'string', 'max:100'],
            'municipality' => ['required', 'string', 'max:120'],
            'notarial_deed_number' => [$personType === 'company' ? 'required' : 'nullable', 'string', 'max:120'],
            'incorporation_date' => [$personType === 'company' ? 'required' : 'nullable', 'date'],
            'notary_name' => [$personType === 'company' ? 'required' : 'nullable', 'string', 'max:190'],
            'notary_number' => [$personType === 'company' ? 'required' : 'nullable', 'string', 'max:50'],
            'legal_powers_scope' => [$personType === 'company' ? 'required' : 'nullable', 'string', 'max:500'],
            'powers_declared_current' => [$personType === 'company' ? 'accepted' : 'nullable'],
            'data_processing_consent' => ['accepted'],
            'truth_declaration' => ['accepted'],
        ];

        $validated = $request->validate($rules, [
            'representative_curp.required' => 'La CURP del titular o representante es obligatoria.',
            'notarial_deed_number.required' => 'La escritura o instrumento notarial es obligatorio para persona moral.',
            'incorporation_date.required' => 'La fecha de constitución es obligatoria para persona moral.',
            'notary_name.required' => 'El nombre del notario es obligatorio para persona moral.',
            'notary_number.required' => 'El número de notaría es obligatorio para persona moral.',
            'legal_powers_scope.required' => 'Describe las facultades del representante legal.',
            'powers_declared_current.accepted' => 'Confirma que las facultades del representante continúan vigentes.',
        ]);

        $isCompany = $validated['person_type'] === 'company';

        $profile->fill([
            ...$validated,
            'business_rfc' => strtoupper(trim($validated['business_rfc'])),
            'business_legal_name' => trim($validated['business_legal_name']),
            'representative_name' => trim($validated['representative_name']),
            'representative_rfc' => strtoupper(trim($validated['representative_rfc'])),
            'representative_curp' => strtoupper(trim($validated['representative_curp'])),
            'representative_email' => strtolower(trim($validated['representative_email'])),
            'representative_phone' => trim($validated['representative_phone']),
            'representative_position' => $isCompany
                ? trim((string) $validated['representative_position'])
                : 'Titular',
            'notarial_deed_number' => $isCompany ? $validated['notarial_deed_number'] : null,
            'incorporation_date' => $isCompany ? $validated['incorporation_date'] : null,
            'notary_name' => $isCompany ? $validated['notary_name'] : null,
            'notary_number' => $isCompany ? $validated['notary_number'] : null,
            'legal_powers_scope' => $isCompany ? $validated['legal_powers_scope'] : 'Actúa por cuenta propia.',
            'powers_declared_current' => $isCompany
                ? $request->boolean('powers_declared_current')
                : true,
            'data_processing_consent' => true,
            'truth_declaration' => true,
            'status' => in_array($profile->status, ['corrections_required', 'rejected'], true)
                ? 'draft'
                : $profile->status,
            'review_notes' => null,
            'approved_at' => null,
            'approved_by' => null,
            'identity_locked_at' => null,
            'identity_hash' => null,
        ])->save();

        $this->event($profile, $request, 'profile_saved', 'Datos de identidad actualizados.', [
            'person_type' => $validated['person_type'],
        ]);

        return back()->with(
            'status',
            $isCompany
                ? 'Datos de persona moral y representante legal guardados.'
                : 'Datos de persona física guardados.'
        );
    }

    public function uploadDocument(Request $request): RedirectResponse
    {
        $commerce = $this->commerce();
        $profile = $this->profile($commerce);

        abort_if($profile->status === 'approved', 422, 'El expediente aprobado está bloqueado.');

        $validated = $request->validate([
            'document_type' => ['required', Rule::in([
                'ine_front',
                'ine_back',
                'proof_address',
                'tax_certificate',
                'representative_tax_certificate',
                'selfie',
                'liveness',
                'articles_incorporation',
                'power_of_attorney',
            ])],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp,mp4,mov', 'max:20480'],
        ]);

        if ($profile->person_type !== 'company' && in_array($validated['document_type'], ['articles_incorporation', 'power_of_attorney', 'representative_tax_certificate'], true)) {
            abort(422, 'Este documento solo corresponde a personas morales.');
        }

        DB::connection('mysql_comercio')->transaction(function () use ($request, $commerce, $profile, $validated): void {
            CommerceIdentityDocument::query()
                ->where('identity_profile_id', $profile->id)
                ->where('document_type', $validated['document_type'])
                ->whereIn('status', ['pending', 'approved', 'rejected'])
                ->update(['status' => 'replaced']);

            $file = $request->file('document');
            $path = $file->store(
                'commerce/identity/'.$commerce->id.'/'.$validated['document_type'],
                'local'
            );

            CommerceIdentityDocument::query()->create([
                'identity_profile_id' => $profile->id,
                'commerce_user_id' => $commerce->id,
                'document_type' => $validated['document_type'],
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
                'sha256' => hash_file('sha256', $file->getRealPath()),
                'is_required' => in_array($validated['document_type'], $profile->requiredDocumentTypes(), true),
                'status' => 'pending',
            ]);

            $profile->forceFill([
                'status' => 'draft',
                'review_notes' => null,
                'approved_at' => null,
                'approved_by' => null,
                'identity_locked_at' => null,
                'identity_hash' => null,
            ])->save();

            $this->event($profile, $request, 'document_uploaded', 'Documento cargado.', [
                'document_type' => $validated['document_type'],
                'sha256' => hash_file('sha256', $file->getRealPath()),
            ]);
        });

        return back()->with('status', 'Documento cargado. El expediente requiere revisión.');
    }

    public function submit(Request $request): RedirectResponse
    {
        $commerce = $this->commerce();
        $profile = $this->profile($commerce)->load('documents');

        abort_if($profile->status === 'approved', 422, 'El expediente ya está aprobado.');

        $missingUploads = collect($profile->requiredDocumentTypes())
            ->reject(fn (string $type) => $profile->documents
                ->where('document_type', $type)
                ->whereIn('status', ['pending', 'approved'])
                ->isNotEmpty())
            ->values()
            ->all();

        abort_if($missingUploads !== [], 422, 'Faltan documentos obligatorios: '.implode(', ', $missingUploads).'.');
        abort_unless(
            $profile->truth_declaration && $profile->data_processing_consent,
            422,
            'Debes aceptar las declaraciones de autenticidad y tratamiento de datos.'
        );

        if ($profile->person_type === 'company') {
            abort_unless(
                $profile->powers_declared_current
                && filled($profile->notarial_deed_number)
                && filled($profile->incorporation_date)
                && filled($profile->notary_name)
                && filled($profile->notary_number)
                && filled($profile->legal_powers_scope),
                422,
                'Completa y confirma los datos notariales y las facultades del representante legal.'
            );
        }

        $profile->forceFill([
            'status' => 'submitted',
            'submitted_at' => now(),
            'review_notes' => null,
        ])->save();

        $this->event($profile, $request, 'submitted', 'Expediente enviado a revisión.');

        return back()->with('status', 'Expediente enviado a revisión. La firma quedará bloqueada hasta su aprobación.');
    }

    public function document(CommerceIdentityDocument $document)
    {
        $commerce = $this->commerce();

        abort_unless(
            (int) $document->commerce_user_id === (int) $commerce->id,
            404
        );

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        abort_unless($disk->exists($document->path), 404);

        return $disk->response(
            $document->path,
            $document->original_name
        );
    }

    private function profile(CommerceUser $commerce): CommerceIdentityProfile
    {
        return CommerceIdentityProfile::query()->firstOrCreate(
            ['commerce_user_id' => $commerce->id],
            [
                'uuid' => (string) Str::uuid(),
                'person_type' => 'individual',
                'business_legal_name' => $commerce->business_name,
                'representative_name' => $commerce->name,
                'representative_email' => $commerce->email,
                'representative_phone' => $commerce->phone,
                'address_line' => $commerce->business_address,
                'status' => 'draft',
                'liveness_challenge' => 'Muestra tu rostro y una hoja con la fecha actual y la palabra PETPAY.',
            ]
        );
    }

    private function commerce(): CommerceUser
    {
        $commerce = Auth::guard('comercio')->user();
        abort_unless($commerce instanceof CommerceUser, 401);

        return $commerce;
    }

    private function event(
        CommerceIdentityProfile $profile,
        Request $request,
        string $type,
        string $description,
        array $metadata = []
    ): void {
        CommerceIdentityEvent::query()->create([
            'identity_profile_id' => $profile->id,
            'commerce_user_id' => $profile->commerce_user_id,
            'event_type' => $type,
            'actor_type' => 'commerce',
            'actor_id' => $profile->commerce_user_id,
            'description' => $description,
            'metadata' => $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => (string) $request->userAgent(),
            'occurred_at' => now(),
        ]);
    }
}

