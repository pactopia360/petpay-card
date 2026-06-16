<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CommerceApprovalController extends Controller
{
    public function index(): View
    {
        $commerces = CommerceUser::query()
            ->where('approval_status', 'pending')
            ->latest()
            ->paginate(10);

        return view('admin.approvals.commerces.index', [
            'commerces' => $commerces,
            'pendingCount' => CommerceUser::query()->where('approval_status', 'pending')->count(),
            'approvedCount' => CommerceUser::query()->where('approval_status', 'approved')->count(),
            'rejectedCount' => CommerceUser::query()->where('approval_status', 'rejected')->count(),
            'totalCount' => CommerceUser::query()->count(),
        ]);
    }

    public function approve(CommerceUser $commerceUser): RedirectResponse
    {
        $commerceUser->forceFill([
            'status' => 'active',
            'approval_status' => 'approved',
            'approved_at' => now(),
            'rejection_reason' => null,
        ])->save();

        return redirect()
            ->route('admin.commerces.pending')
            ->with('status', 'Comercio aprobado correctamente.');
    }

    public function reject(CommerceUser $commerceUser): RedirectResponse
    {
        $commerceUser->forceFill([
            'status' => 'rejected',
            'approval_status' => 'rejected',
            'is_open' => false,
            'rejection_reason' => 'Solicitud rechazada por Admin.',
        ])->save();

        return redirect()
            ->route('admin.commerces.pending')
            ->with('status', 'Comercio rechazado correctamente.');
    }
}