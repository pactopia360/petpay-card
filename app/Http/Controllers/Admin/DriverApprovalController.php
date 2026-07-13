<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Repartidor\DriverUser;
use App\Models\Repartidor\DriverIdentityProfile;
use App\Models\Repartidor\DriverUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DriverApprovalController extends Controller
{
    public function index(): View
    {
        $drivers = DriverUser::query()
            ->where('approval_status', 'pending')
            ->latest()
            ->paginate(15);

        $identityCount = DriverIdentityProfile::query()
            ->whereIn('status', [
                'submitted',
                'under_review',
                'corrections_required',
            ])
            ->count();

        $updateRequestCount = DriverUpdateRequest::query()
            ->whereIn('status', ['pending', 'under_review'])
            ->count();

        return view('admin.approvals.drivers.index', [
            'drivers' => $drivers,
            'identityCount' => $identityCount,
            'updateRequestCount' => $updateRequestCount,
        ]);
    }

    public function approve(DriverUser $driverUser): RedirectResponse
    {
        $driverUser->forceFill([
            'status' => 'active',
            'approval_status' => 'approved',
        ])->save();

        return redirect()
            ->route('admin.drivers.pending')
            ->with('status', 'Repartidor aprobado correctamente.');
    }

    public function reject(DriverUser $driverUser): RedirectResponse
    {
        $driverUser->forceFill([
            'status' => 'rejected',
            'approval_status' => 'rejected',
            'is_available' => false,
        ])->save();

        return redirect()
            ->route('admin.drivers.pending')
            ->with('status', 'Repartidor rechazado correctamente.');
    }
}
