<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proveedor\ProviderUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProviderApprovalController extends Controller
{
    public function index(): View
    {
        $providers = ProviderUser::query()
            ->where('approval_status', 'pending')
            ->latest()
            ->paginate(15);

        return view('admin.approvals.providers.index', [
            'providers' => $providers,
        ]);
    }

    public function approve(ProviderUser $providerUser): RedirectResponse
    {
        $providerUser->forceFill([
            'status' => 'active',
            'approval_status' => 'approved',
        ])->save();

        return redirect()
            ->route('admin.providers.pending')
            ->with('status', 'Proveedor aprobado correctamente.');
    }

    public function reject(ProviderUser $providerUser): RedirectResponse
    {
        $providerUser->forceFill([
            'status' => 'rejected',
            'approval_status' => 'rejected',
            'is_open' => false,
        ])->save();

        return redirect()
            ->route('admin.providers.pending')
            ->with('status', 'Proveedor rechazado correctamente.');
    }
}