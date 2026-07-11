<?php

namespace App\Http\Controllers\Comercio;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceBranch;
use App\Models\Comercio\CommerceBranding;
use App\Models\Comercio\CommerceUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CommerceBrandingController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $commerce = Auth::guard('comercio')->user();

        abort_unless($commerce instanceof CommerceUser, 401);

        $branding = CommerceBranding::query()->firstOrNew([
            'commerce_user_id' => $commerce->id,
        ]);

        $validated = $request->validate([
            'header_image' => [
                'nullable',
                'image',
                Rule::dimensions()->minWidth(900)->minHeight(160)->maxWidth(5000)->maxHeight(2500),
                'mimes:png,jpg,jpeg,webp',
                'max:8192',
            ],
            'icon_image' => [
                'nullable',
                'image',
                Rule::dimensions()->minWidth(256)->minHeight(256)->maxWidth(3000)->maxHeight(3000),
                'mimes:png,jpg,jpeg,webp',
                'max:4096',
            ],
            'listing_image' => [
                'nullable',
                'image',
                Rule::dimensions()->minWidth(600)->minHeight(600)->maxWidth(4000)->maxHeight(4000),
                'mimes:png,jpg,jpeg,webp',
                'max:6144',
            ],

            'header_branch_id' => ['nullable', 'integer'],
            'icon_branch_id' => ['nullable', 'integer'],
            'listing_branch_id' => ['nullable', 'integer'],

            'remove_header_image' => ['nullable', 'boolean'],
            'remove_icon_image' => ['nullable', 'boolean'],
            'remove_listing_image' => ['nullable', 'boolean'],
        ], [
            'header_image.image' => 'La imagen de cabecera debe ser una imagen válida.',
            'header_image.dimensions' => 'La imagen de cabecera debe medir al menos 900 x 160 px.',
            'header_image.max' => 'La imagen de cabecera no debe exceder 8 MB.',

            'icon_image.image' => 'La imagen de ícono debe ser una imagen válida.',
            'icon_image.dimensions' => 'La imagen de ícono debe medir al menos 256 x 256 px.',
            'icon_image.max' => 'La imagen de ícono no debe exceder 4 MB.',

            'listing_image.image' => 'La imagen de listado debe ser una imagen válida.',
            'listing_image.dimensions' => 'La imagen de listado debe medir al menos 600 x 600 px.',
            'listing_image.max' => 'La imagen de listado no debe exceder 6 MB.',
        ]);

        $payload = [
            'commerce_user_id' => $commerce->id,
            'store_name' => $branding->store_name ?: ($commerce->business_name ?? $commerce->name),
            'show_logo' => true,
            'show_banner' => true,
        ];

        foreach ([
            'header' => ['input' => 'header_image', 'folder' => 'header'],
            'icon' => ['input' => 'icon_image', 'folder' => 'icon'],
            'listing' => ['input' => 'listing_image', 'folder' => 'listing'],
        ] as $type => $config) {
            $branchField = "{$type}_branch_id";
            $branchId = $validated[$branchField] ?? null;

            $payload[$branchField] = $this->validatedBranchId(
                commerceId: (int) $commerce->id,
                branchId: $branchId === null ? null : (int) $branchId,
                field: $branchField
            );

            $this->removeImageWhenRequested(
                request: $request,
                branding: $branding,
                type: $type,
                payload: $payload
            );

            $this->storeImage(
                request: $request,
                branding: $branding,
                type: $type,
                input: $config['input'],
                folder: $config['folder'],
                commerceId: (int) $commerce->id,
                payload: $payload
            );
        }

        $branding->fill($payload)->save();

        return redirect()
            ->route('comercio.dashboard', ['tab' => 'branding'])
            ->with('status', 'Branding guardado y enviado a revisión.');
    }

    public function reset(): RedirectResponse
    {
        $commerce = Auth::guard('comercio')->user();

        abort_unless($commerce instanceof CommerceUser, 401);

        $branding = CommerceBranding::query()
            ->where('commerce_user_id', $commerce->id)
            ->first();

        if ($branding) {
            $payload = [];

            foreach (['header', 'icon', 'listing'] as $type) {
                $pathField = "{$type}_image_path";

                if ($branding->{$pathField}) {
                    Storage::disk('public')->delete($branding->{$pathField});
                }

                $this->clearReviewFields($type, $payload);
                $payload[$pathField] = null;
                $payload["{$type}_branch_id"] = null;
            }

            $branding->forceFill($payload)->save();
        }

        return redirect()
            ->route('comercio.dashboard', ['tab' => 'branding'])
            ->with('status', 'Imágenes y asignaciones de Branding eliminadas correctamente.');
    }

    private function validatedBranchId(
        int $commerceId,
        ?int $branchId,
        string $field
    ): ?int {
        if ($branchId === null || $branchId === 0) {
            return null;
        }

        $exists = CommerceBranch::query()
            ->whereKey($branchId)
            ->where('commerce_user_id', $commerceId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                $field => 'La sucursal seleccionada no pertenece a este comercio.',
            ]);
        }

        return $branchId;
    }

    private function removeImageWhenRequested(
        Request $request,
        CommerceBranding $branding,
        string $type,
        array &$payload
    ): void {
        if (! $request->boolean("remove_{$type}_image")) {
            return;
        }

        $pathField = "{$type}_image_path";

        if ($branding->{$pathField}) {
            Storage::disk('public')->delete($branding->{$pathField});
        }

        $payload[$pathField] = null;
        $payload["{$type}_branch_id"] = null;

        $this->clearReviewFields($type, $payload);
    }

    private function storeImage(
        Request $request,
        CommerceBranding $branding,
        string $type,
        string $input,
        string $folder,
        int $commerceId,
        array &$payload
    ): void {
        if (! $request->hasFile($input)) {
            return;
        }

        $pathField = "{$type}_image_path";

        if ($branding->{$pathField}) {
            Storage::disk('public')->delete($branding->{$pathField});
        }

        $payload[$pathField] = $request->file($input)->store(
            'commerce-branding/'.$commerceId.'/'.$folder,
            'public'
        );

        $payload["{$type}_image_status"] = 'pending';
        $payload["{$type}_image_submitted_at"] = now();
        $payload["{$type}_image_reviewed_at"] = null;
        $payload["{$type}_image_rejection_reason"] = null;
        $payload["{$type}_image_reviewed_by"] = null;
    }

    private function clearReviewFields(string $type, array &$payload): void
    {
        $payload["{$type}_image_status"] = null;
        $payload["{$type}_image_submitted_at"] = null;
        $payload["{$type}_image_reviewed_at"] = null;
        $payload["{$type}_image_rejection_reason"] = null;
        $payload["{$type}_image_reviewed_by"] = null;
    }
}
