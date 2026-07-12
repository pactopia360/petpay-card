<?php

namespace App\Http\Controllers\Comercio;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceMonetizationCampaign;
use App\Models\Comercio\CommerceUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CommerceMonetizationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $commerce = $this->commerce();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'branch_id' => ['nullable', 'integer'],
            'type' => ['required', Rule::in(['coupon', 'discount', 'sponsored', 'cashback', 'referral', 'membership'])],
            'scope' => ['required', Rule::in(['all', 'branch', 'category', 'product'])],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'discount_type' => ['nullable', Rule::in(['percentage', 'fixed'])],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'coupon_code' => ['nullable', 'string', 'max:80'],
            'minimum_purchase' => ['nullable', 'numeric', 'min:0'],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'cashback_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'description' => ['nullable', 'string', 'max:5000'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer'],
        ]);

        CommerceMonetizationCampaign::query()->create([
            'commerce_user_id' => $commerce->id,
            'branch_id' => $validated['branch_id'] ?? null,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']).'-'.Str::lower(Str::random(6)),
            'type' => $validated['type'],
            'status' => 'draft',
            'scope' => $validated['scope'],
            'budget' => $validated['budget'] ?? 0,
            'discount_type' => $validated['discount_type'] ?? null,
            'discount_value' => $validated['discount_value'] ?? null,
            'coupon_code' => filled($validated['coupon_code'] ?? null)
                ? Str::upper(trim((string) $validated['coupon_code']))
                : null,
            'minimum_purchase' => $validated['minimum_purchase'] ?? 0,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'cashback_percentage' => $validated['cashback_percentage'] ?? null,
            'product_ids' => $validated['product_ids'] ?? [],
            'category_ids' => $validated['category_ids'] ?? [],
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('comercio.dashboard', ['tab' => 'monetizar'])
            ->with('status', 'Campaña creada correctamente.');
    }

    public function submit(CommerceMonetizationCampaign $campaign): RedirectResponse
    {
        $commerce = $this->commerce();
        abort_unless((int) $campaign->commerce_user_id === (int) $commerce->id, 404);

        $campaign->update([
            'status' => 'pending',
            'submitted_at' => now(),
            'rejection_reason' => null,
        ]);

        return back()->with('status', 'Campaña enviada a revisión.');
    }

    public function toggle(CommerceMonetizationCampaign $campaign): RedirectResponse
    {
        $commerce = $this->commerce();
        abort_unless((int) $campaign->commerce_user_id === (int) $commerce->id, 404);
        abort_unless(in_array($campaign->status, ['active', 'paused'], true), 422);

        $campaign->update([
            'status' => $campaign->status === 'active' ? 'paused' : 'active',
        ]);

        return back()->with('status', 'Estatus de campaña actualizado.');
    }

    public function destroy(CommerceMonetizationCampaign $campaign): RedirectResponse
    {
        $commerce = $this->commerce();
        abort_unless((int) $campaign->commerce_user_id === (int) $commerce->id, 404);

        $campaign->delete();

        return back()->with('status', 'Campaña eliminada.');
    }

    private function commerce(): CommerceUser
    {
        $commerce = Auth::guard('comercio')->user();
        abort_unless($commerce instanceof CommerceUser, 401);

        return $commerce;
    }
}