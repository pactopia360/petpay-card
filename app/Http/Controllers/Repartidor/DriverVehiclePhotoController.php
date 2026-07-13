<?php

namespace App\Http\Controllers\Repartidor;

use App\Http\Controllers\Controller;
use App\Models\Repartidor\DriverUser;
use App\Models\Repartidor\DriverVehicle;
use App\Models\Repartidor\DriverVehiclePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DriverVehiclePhotoController extends Controller
{
    public function show(
        Request $request,
        DriverVehicle $vehicle,
        DriverVehiclePhoto $photo
    ): BinaryFileResponse {
        $driver = $request->user('repartidor');

        abort_unless($driver instanceof DriverUser, 401);

        abort_unless(
            (int) $vehicle->driver_user_id === (int) $driver->id,
            404
        );

        abort_unless(
            (int) $photo->driver_vehicle_id === (int) $vehicle->id,
            404
        );

        $size = $request->string('size')->toString();

        $path = $size === 'thumb' && $photo->thumbnail_path
            ? $photo->thumbnail_path
            : $photo->path;

        abort_unless(
            $path && Storage::disk('local')->exists($path),
            404
        );

        return response()->file(
            Storage::disk('local')->path($path),
            [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'private, max-age=3600',
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}
