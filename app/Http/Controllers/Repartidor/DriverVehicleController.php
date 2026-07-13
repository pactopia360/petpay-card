<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\Controller;
use App\Models\Repartidor\DriverUser;
use App\Models\Repartidor\DriverVehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DriverVehicleController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $driver = $this->driver($request);
        $validated = $this->validateVehicle($request);

        DB::connection('mysql_repartidor')->transaction(
            function () use ($request, $driver, $validated): void {
                if (! empty($validated['is_primary'])) {
                    DriverVehicle::query()
                        ->where('driver_user_id', $driver->id)
                        ->update(['is_primary' => false]);
                }

                $vehicle = new DriverVehicle();
                $vehicle->uuid = (string) Str::uuid();
                $vehicle->driver_user_id = $driver->id;
                $vehicle->vehicle_code = $this->nextCode();
                $vehicle->status = 'draft';

                $this->fillVehicle(
                    $vehicle,
                    $request,
                    $validated
                );

                $vehicle->save();

                app(
                    \App\Services\Repartidor\VehiclePhotoProcessor::class
                )->sync($vehicle, $request);
            }
        );

        return redirect()
            ->route('repartidor.dashboard', ['tab' => 'vehiculo'])
            ->with('status', 'Vehículo guardado correctamente.');
    }

    public function update(
        Request $request,
        DriverVehicle $vehicle
    ): RedirectResponse {
        $driver = $this->driver($request);
        $this->authorizeVehicle($vehicle, $driver);

        abort_if(
            $vehicle->isLocked(),
            422,
            'El vehículo está en revisión o aprobado. Solicita una actualización.'
        );

        $validated = $this->validateVehicle($request);

        DB::connection('mysql_repartidor')->transaction(
            function () use ($request, $driver, $vehicle, $validated): void {
                if (! empty($validated['is_primary'])) {
                    DriverVehicle::query()
                        ->where('driver_user_id', $driver->id)
                        ->whereKeyNot($vehicle->id)
                        ->update(['is_primary' => false]);
                }

                $this->fillVehicle(
                    $vehicle,
                    $request,
                    $validated
                );

                $vehicle->save();

                app(
                    \App\Services\Repartidor\VehiclePhotoProcessor::class
                )->sync($vehicle, $request);
            }
        );

        return redirect()
            ->route('repartidor.dashboard', ['tab' => 'vehiculo'])
            ->with('status', 'Vehículo actualizado correctamente.');
    }

    public function destroy(
        Request $request,
        DriverVehicle $vehicle
    ): RedirectResponse {
        $driver = $this->driver($request);
        $this->authorizeVehicle($vehicle, $driver);

        abort_if(
            $vehicle->isLocked(),
            422,
            'No se puede eliminar un vehículo enviado o aprobado.'
        );

        abort_if(
            $vehicle->is_primary,
            422,
            'Asigna otro vehículo principal antes de eliminar este.'
        );

        $vehicle->delete();

        return redirect()
            ->route('repartidor.dashboard', ['tab' => 'vehiculo'])
            ->with('status', 'Vehículo eliminado.');
    }

    private function validateVehicle(Request $request): array
    {
        return $request->validate([
            'vehicle_type' => [
                'required',
                Rule::in([
                    'motorcycle',
                    'car',
                    'bicycle',
                    'walking',
                    'van',
                    'other',
                ]),
            ],
            'alias' => ['nullable', 'string', 'max:120'],
            'make' => ['nullable', 'string', 'max:120'],
            'model' => ['nullable', 'string', 'max:120'],
            'year' => [
                'nullable',
                'integer',
                'between:1950,'.((int) date('Y') + 1),
            ],
            'color' => ['nullable', 'string', 'max:80'],
            'color_scale' => ['nullable', 'string', 'max:80'],
            'plates' => ['nullable', 'string', 'max:40'],
            'is_primary' => ['nullable', 'boolean'],
            'insurer' => ['nullable', 'string', 'max:150'],
            'policy_number' => ['nullable', 'string', 'max:120'],
            'coverage_type' => ['nullable', 'string', 'max:80'],
            'insurance_status' => [
                'required',
                Rule::in([
                    'not_required',
                    'pending',
                    'under_review',
                    'approved',
                    'rejected',
                    'expired',
                ]),
            ],
            'insurance_starts_at' => ['nullable', 'date'],
            'insurance_expires_at' => [
                'nullable',
                'date',
                'after_or_equal:insurance_starts_at',
            ],
            'insurance_cost' => ['nullable', 'numeric', 'min:0'],
            'assistance_phone' => ['nullable', 'string', 'max:30'],
            'expiration_alert_days' => [
                'required',
                'integer',
                'between:1,180',
            ],
            'internal_notes' => ['nullable', 'string', 'max:3000'],
            'vehicle_photos' => ['nullable', 'array'],
            'vehicle_photos.*' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:15360',
                'dimensions:min_width=640,min_height=480',
            ],
            'vehicle_camera' => ['nullable', 'array'],
            'vehicle_camera.*' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:15360',
                'dimensions:min_width=640,min_height=480',
            ],
            'policy_file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:15360',
            ],
            'receipt_file' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png,webp',
                'max:15360',
            ],
        ]);
    }

    private function fillVehicle(
        DriverVehicle $vehicle,
        Request $request,
        array $validated
    ): void {
        $vehicle->fill([
            'vehicle_type' => $validated['vehicle_type'],
            'alias' => trim((string) ($validated['alias'] ?? '')) ?: null,
            'make' => trim((string) ($validated['make'] ?? '')) ?: null,
            'model' => trim((string) ($validated['model'] ?? '')) ?: null,
            'year' => $validated['year'] ?? null,
            'color' => trim((string) ($validated['color'] ?? '')) ?: null,
            'color_scale' => trim((string) ($validated['color_scale'] ?? '')) ?: null,
            'plates' => strtoupper(
                trim((string) ($validated['plates'] ?? ''))
            ) ?: null,
            'is_primary' => (bool) ($validated['is_primary'] ?? false),
            'insurer' => trim((string) ($validated['insurer'] ?? '')) ?: null,
            'policy_number' => trim(
                (string) ($validated['policy_number'] ?? '')
            ) ?: null,
            'coverage_type' => $validated['coverage_type'] ?? null,
            'insurance_status' => $validated['insurance_status'],
            'insurance_starts_at' => $validated['insurance_starts_at'] ?? null,
            'insurance_expires_at' => $validated['insurance_expires_at'] ?? null,
            'insurance_cost' => $validated['insurance_cost'] ?? null,
            'assistance_phone' => trim(
                (string) ($validated['assistance_phone'] ?? '')
            ) ?: null,
            'expiration_alert_days' =>
                $validated['expiration_alert_days'],
            'internal_notes' => trim(
                (string) ($validated['internal_notes'] ?? '')
            ) ?: null,
        ]);

        $this->storeFile(
            $vehicle,
            $request,
            'policy_file',
            'policy'
        );

        $this->storeFile(
            $vehicle,
            $request,
            'receipt_file',
            'receipt'
        );
    }

    private function storeFile(
        DriverVehicle $vehicle,
        Request $request,
        string $input,
        string $prefix
    ): void {
        $file = $request->file($input);

        if ($file === null) {
            return;
        }

        $oldPath = $vehicle->getAttribute($prefix.'_path');

        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        $path = $file->store(
            'repartidor/vehicles/'.$vehicle->driver_user_id,
            'local'
        );

        $vehicle->setAttribute($prefix.'_path', $path);
        $vehicle->setAttribute(
            $prefix.'_original_name',
            $file->getClientOriginalName()
        );
        $vehicle->setAttribute(
            $prefix.'_mime_type',
            $file->getMimeType()
        );
        $vehicle->setAttribute(
            $prefix.'_sha256',
            hash_file('sha256', $file->getRealPath())
        );
    }

    private function nextCode(): string
    {
        $next = ((int) DriverVehicle::query()->max('id')) + 1;

        return 'VEH-'.str_pad(
            (string) $next,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    private function driver(Request $request): DriverUser
    {
        $driver = $request->user('repartidor');

        abort_unless($driver instanceof DriverUser, 401);

        return $driver;
    }

    private function authorizeVehicle(
        DriverVehicle $vehicle,
        DriverUser $driver
    ): void {
        abort_unless(
            (int) $vehicle->driver_user_id === (int) $driver->id,
            404
        );
    }
}


