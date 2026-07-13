<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\Controller;
use App\Models\Repartidor\DriverUser;
use App\Models\Repartidor\DriverVehicle;
use App\Models\Repartidor\DriverVehicle3dFrame;
use App\Models\Repartidor\DriverVehicle3dJob;
use App\Services\Repartidor\Vehicle3dFrameProcessor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DriverVehicle3dController extends Controller
{
    public function store(
        DriverVehicle $vehicle
    ): RedirectResponse {
        $driver = $this->driver();
        $this->authorizeVehicle($driver, $vehicle);

        $activeJob = DriverVehicle3dJob::query()
            ->where('driver_vehicle_id', $vehicle->id)
            ->whereIn('status', [
                'awaiting_capture',
                'capture_ready',
                'requires_recapture',
            ])
            ->latest('id')
            ->first();

        if (! $activeJob) {
            $activeJob = DriverVehicle3dJob::create([
                'uuid' => (string) Str::uuid(),
                'driver_vehicle_id' => $vehicle->id,
                'driver_user_id' => $driver->id,
                'source_type' => 'photos',
                'status' => 'awaiting_capture',
                'progress' => 0,
                'required_frames' => 30,
                'captured_frames' => 0,
                'metadata' => [
                    'capture_plan' => '24_middle_6_high',
                    'version' => 1,
                ],
            ]);
        }

        return redirect()
            ->route('repartidor.dashboard', [
                'tab' => 'vehiculo',
                'capture3d' => $vehicle->id,
            ])
            ->with(
                'status',
                'Captura 3D lista. Continúa desde la última toma guardada.'
            );
    }

    public function upload(
        Request $request,
        DriverVehicle $vehicle,
        DriverVehicle3dJob $job,
        Vehicle3dFrameProcessor $processor
    ): RedirectResponse {
        $driver = $this->driver();

        $this->authorizeJob($driver, $vehicle, $job);

        abort_if(
            in_array($job->status, [
                'queued',
                'processing',
                'optimizing',
                'ready',
            ], true),
            409,
            'La captura ya fue enviada a procesamiento.'
        );

        $validated = $request->validate([
            'sequence' => [
                'required',
                'integer',
                'between:1,30',
            ],
            'frame' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:20480',
                'dimensions:min_width=640,min_height=480',
            ],
        ]);

        $processor->store(
            $job,
            $request->file('frame'),
            (int) $validated['sequence']
        );

        return redirect()
            ->route('repartidor.dashboard', [
                'tab' => 'vehiculo',
                'capture3d' => $vehicle->id,
            ])
            ->with('status', sprintf(
                'Toma %02d guardada correctamente.',
                $validated['sequence']
            ));
    }

    public function frame(
        DriverVehicle $vehicle,
        DriverVehicle3dJob $job,
        DriverVehicle3dFrame $frame
    ): BinaryFileResponse {
        $driver = $this->driver();

        $this->authorizeJob($driver, $vehicle, $job);

        abort_unless(
            $frame->driver_vehicle_3d_job_id === $job->id
            && $frame->driver_vehicle_id === $vehicle->id,
            404
        );

        $path = request()->boolean('thumbnail')
            ? $frame->thumbnail_path
            : $frame->path;

        abort_unless(
            $path && Storage::disk('local')->exists($path),
            404
        );

        return response()->file(
            Storage::disk('local')->path($path),
            [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'private, max-age=300',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    public function destroyFrame(
        DriverVehicle $vehicle,
        DriverVehicle3dJob $job,
        DriverVehicle3dFrame $frame,
        Vehicle3dFrameProcessor $processor
    ): RedirectResponse {
        $driver = $this->driver();

        $this->authorizeJob($driver, $vehicle, $job);

        abort_unless(
            $frame->driver_vehicle_3d_job_id === $job->id,
            404
        );

        abort_if(
            in_array($job->status, [
                'queued',
                'processing',
                'optimizing',
                'ready',
            ], true),
            409
        );

        $processor->delete($job, $frame);

        return redirect()
            ->route('repartidor.dashboard', [
                'tab' => 'vehiculo',
                'capture3d' => $vehicle->id,
            ])
            ->with('status', 'Toma eliminada. Puedes capturarla nuevamente.');
    }

    public function submit(
        DriverVehicle $vehicle,
        DriverVehicle3dJob $job,
        Vehicle3dFrameProcessor $processor
    ): RedirectResponse {
        $driver = $this->driver();

        $this->authorizeJob($driver, $vehicle, $job);
        $processor->refreshJobProgress($job);
        $job->refresh();

        if ($job->captured_frames < $job->required_frames) {
            return back()->withErrors([
                'capture3d' => sprintf(
                    'Faltan %d tomas para completar el modelo 3D.',
                    $job->required_frames - $job->captured_frames
                ),
            ]);
        }

        $job->forceFill([
            'status' => 'queued',
            'progress' => 100,
            'capture_completed_at' => now(),
            'error_message' => null,
        ])->save();

        return redirect()
            ->route('repartidor.dashboard', ['tab' => 'vehiculo'])
            ->with(
                'status',
                'Las fotografías fueron enviadas para construir el modelo 3D.'
            );
    }

    private function driver(): DriverUser
    {
        $driver = Auth::guard('repartidor')->user();

        abort_unless($driver instanceof DriverUser, 401);

        return $driver;
    }

    private function authorizeVehicle(
        DriverUser $driver,
        DriverVehicle $vehicle
    ): void {
        abort_unless(
            (int) $vehicle->driver_user_id === (int) $driver->id,
            404
        );
    }

    private function authorizeJob(
        DriverUser $driver,
        DriverVehicle $vehicle,
        DriverVehicle3dJob $job
    ): void {
        $this->authorizeVehicle($driver, $vehicle);

        abort_unless(
            (int) $job->driver_vehicle_id === (int) $vehicle->id
            && (int) $job->driver_user_id === (int) $driver->id,
            404
        );
    }
}
