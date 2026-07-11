<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comercio\CommerceBranding;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CommerceBrandingReviewController extends Controller
{
    private const TYPES = ['header', 'icon', 'listing'];

    public function index(): View
    {
        $brandings = CommerceBranding::query()
            ->with('commerce')
            ->where(function ($query): void {
                foreach (self::TYPES as $index => $type) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $query->{$method}("{$type}_image_status", 'pending');
                }
            })
            ->latest('updated_at')
            ->paginate(12);

        $pendingCount = 0;

        foreach (self::TYPES as $type) {
            $pendingCount += CommerceBranding::query()
                ->where("{$type}_image_status", 'pending')
                ->count();
        }

        return view('admin.approvals.branding.index', [
            'brandings' => $brandings,
            'pendingCount' => $pendingCount,
        ]);
    }

    public function approve(
        CommerceBranding $branding,
        string $type
    ): RedirectResponse {
        $this->assertType($type);
        abort_unless($branding->getAttribute("{$type}_image_path"), 404);

        $branding->forceFill([
            "{$type}_image_status" => 'approved',
            "{$type}_image_reviewed_at" => now(),
            "{$type}_image_rejection_reason" => null,
            "{$type}_image_reviewed_by" => Auth::guard('admin')->id(),
        ])->save();

        return back()->with('status', 'Imagen aprobada correctamente.');
    }

    public function reject(
        Request $request,
        CommerceBranding $branding,
        string $type
    ): RedirectResponse {
        $this->assertType($type);
        abort_unless($branding->getAttribute("{$type}_image_path"), 404);

        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:8', 'max:1000'],
        ], [
            'reason.required' => 'Debes indicar el motivo del rechazo.',
            'reason.min' => 'El motivo debe contener al menos 8 caracteres.',
        ]);

        $branding->forceFill([
            "{$type}_image_status" => 'rejected',
            "{$type}_image_reviewed_at" => now(),
            "{$type}_image_rejection_reason" => $validated['reason'],
            "{$type}_image_reviewed_by" => Auth::guard('admin')->id(),
        ])->save();

        return back()->with('status', 'Imagen rechazada correctamente.');
    }

    private function assertType(string $type): void
    {
        abort_unless(in_array($type, self::TYPES, true), 404);
    }
}
