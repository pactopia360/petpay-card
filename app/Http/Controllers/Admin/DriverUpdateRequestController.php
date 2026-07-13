<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Repartidor\DriverUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DriverUpdateRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();

        $requests = DriverUpdateRequest::query()
            ->with(['driver', 'profile'])
            ->when(
                in_array($status, [
                    'pending',
                    'under_review',
                    'approved',
                    'rejected',
                ], true),
                fn ($query) => $query->where('status', $status)
            )
            ->orderByRaw("
                CASE status
                    WHEN 'pending' THEN 1
                    WHEN 'under_review' THEN 2
                    WHEN 'rejected' THEN 3
                    WHEN 'approved' THEN 4
                    ELSE 5
                END
            ")
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.driver-update-requests.index', [
            'requests' => $requests,
            'statusFilter' => $status,
        ]);
    }

    public function approve(
        Request $request,
        DriverUpdateRequest $updateRequest
    ): RedirectResponse {
        abort_unless(
            in_array($updateRequest->status, ['pending', 'under_review'], true),
            422,
            'Esta solicitud ya fue resuelta.'
        );

        $driver = $updateRequest->driver;
        $profile = $updateRequest->profile;

        abort_unless($driver !== null, 404, 'No existe el repartidor.');

        DB::connection('mysql_repartidor')->transaction(
            function () use ($request, $updateRequest, $driver, $profile): void {
                $value = trim($updateRequest->requested_value);

                switch ($updateRequest->field_name) {
                    case 'first_name':
                        $driver->first_name = $value;
                        $driver->name = trim(
                            $value.' '.$driver->last_name
                        );
                        $driver->save();
                        break;

                    case 'paternal_last_name':
                        abort_unless($profile !== null, 422);

                        $profile->paternal_last_name = $value;
                        $profile->save();

                        $driver->last_name = trim(
                            $value.' '.
                            ($profile->maternal_last_name ?? '')
                        );
                        $driver->name = trim(
                            $driver->first_name.' '.$driver->last_name
                        );
                        $driver->save();
                        break;

                    case 'maternal_last_name':
                        abort_unless($profile !== null, 422);

                        $profile->maternal_last_name = $value ?: null;
                        $profile->save();

                        $driver->last_name = trim(
                            ($profile->paternal_last_name ?? '').' '.$value
                        );
                        $driver->name = trim(
                            $driver->first_name.' '.$driver->last_name
                        );
                        $driver->save();
                        break;

                    case 'email':
                        $driver->email = strtolower($value);
                        $driver->save();

                        if ($profile !== null) {
                            $profile->contact_email = strtolower($value);
                            $profile->email_verified = false;
                            $profile->contact_email_verified_at = null;
                            $profile->save();
                        }
                        break;

                    case 'mobile_phone':
                        $driver->phone = $value;
                        $driver->save();

                        if ($profile !== null) {
                            $profile->mobile_phone = $value;
                            $profile->phone_verified = false;
                            $profile->phone_verified_at = null;
                            $profile->save();
                        }
                        break;

                    case 'vehicle_type':
                    case 'vehicle_make':
                    case 'vehicle_model':
                    case 'vehicle_plate':
                    case 'license_number':
                    case 'operation_zone':
                    case 'state':
                    case 'city':
                        $driver->forceFill([
                            $updateRequest->field_name => $value,
                        ])->save();
                        break;

                    default:
                        abort(422, 'El dato solicitado no está permitido.');
                }

                $updateRequest->forceFill([
                    'status' => 'approved',
                    'admin_notes' => trim(
                        (string) $request->input('admin_notes')
                    ) ?: null,
                    'reviewed_by' => $request->user('admin')?->id,
                    'reviewed_at' => now(),
                    'applied_at' => now(),
                ])->save();
            }
        );

        return back()->with(
            'status',
            'Solicitud aprobada y cambio aplicado correctamente.'
        );
    }

    public function reject(
        Request $request,
        DriverUpdateRequest $updateRequest
    ): RedirectResponse {
        $validated = $request->validate([
            'admin_notes' => [
                'required',
                'string',
                'min:5',
                'max:2000',
            ],
        ]);

        abort_unless(
            in_array($updateRequest->status, ['pending', 'under_review'], true),
            422,
            'Esta solicitud ya fue resuelta.'
        );

        $updateRequest->forceFill([
            'status' => 'rejected',
            'admin_notes' => trim($validated['admin_notes']),
            'reviewed_by' => $request->user('admin')?->id,
            'reviewed_at' => now(),
            'applied_at' => null,
        ])->save();

        return back()->with('status', 'Solicitud rechazada.');
    }

    public function evidence(
        DriverUpdateRequest $updateRequest
    ): BinaryFileResponse {
        abort_if(blank($updateRequest->evidence_path), 404);

        $disk = Storage::disk('local');

        abort_unless(
            $disk->exists($updateRequest->evidence_path),
            404
        );

        return new BinaryFileResponse(
            $disk->path($updateRequest->evidence_path),
            200,
            [
                'Content-Type' =>
                    $updateRequest->evidence_mime_type
                    ?: 'application/octet-stream',
                'Content-Disposition' =>
                    'inline; filename="'.
                    str_replace(
                        ['"', "\r", "\n"],
                        '',
                        $updateRequest->evidence_original_name
                        ?: 'evidencia'
                    ).'"',
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]
        );
    }
}
