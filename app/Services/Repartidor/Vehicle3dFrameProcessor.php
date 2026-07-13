<?php

namespace App\Services\Repartidor;

use App\Models\Repartidor\DriverVehicle3dFrame;
use App\Models\Repartidor\DriverVehicle3dJob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class Vehicle3dFrameProcessor
{
    public function store(
        DriverVehicle3dJob $job,
        UploadedFile $file,
        int $sequence
    ): DriverVehicle3dFrame {
        [$angle, $elevation] = $this->capturePosition($sequence);

        $binary = file_get_contents($file->getRealPath());

        if ($binary === false) {
            throw new RuntimeException('No fue posible leer la fotografía.');
        }

        $source = imagecreatefromstring($binary);

        if ($source === false) {
            throw new RuntimeException('La fotografía no tiene un formato válido.');
        }

        $source = $this->correctOrientation($source, $file);

        [$sourceWidth, $sourceHeight] = [
            imagesx($source),
            imagesy($source),
        ];

        if ($sourceWidth < 640 || $sourceHeight < 480) {
            imagedestroy($source);

            throw new RuntimeException(
                'La fotografía debe medir al menos 640 × 480 píxeles.'
            );
        }

        $image = $this->resize($source, 1920);
        $thumbnail = $this->resize($source, 480);

        imagedestroy($source);

        $directory = implode('/', [
            'repartidores',
            $job->driver_user_id,
            'vehiculos',
            $job->driver_vehicle_id,
            'modelo-3d',
            $job->uuid,
        ]);

        $baseName = sprintf('toma-%02d', $sequence);
        $path = "{$directory}/{$baseName}.png";
        $thumbnailPath = "{$directory}/{$baseName}-miniatura.png";

        $png = $this->encodePng($image);
        $thumbnailPng = $this->encodePng($thumbnail);

        imagedestroy($image);
        imagedestroy($thumbnail);

        Storage::disk('local')->put($path, $png);
        Storage::disk('local')->put($thumbnailPath, $thumbnailPng);

        $oldFrame = DriverVehicle3dFrame::withTrashed()
            ->where('driver_vehicle_3d_job_id', $job->id)
            ->where('sequence', $sequence)
            ->first();

        if ($oldFrame) {
            $oldPaths = array_filter([
                $oldFrame->path,
                $oldFrame->thumbnail_path,
            ]);

            $oldFrame->forceDelete();

            foreach ($oldPaths as $oldPath) {
                if (! in_array($oldPath, [$path, $thumbnailPath], true)) {
                    Storage::disk('local')->delete($oldPath);
                }
            }
        }

        $frame = DriverVehicle3dFrame::create([
            'uuid' => (string) Str::uuid(),
            'driver_vehicle_3d_job_id' => $job->id,
            'driver_vehicle_id' => $job->driver_vehicle_id,
            'driver_user_id' => $job->driver_user_id,
            'sequence' => $sequence,
            'angle_degrees' => $angle,
            'elevation' => $elevation,
            'path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => 'image/png',
            'sha256' => hash('sha256', $png),
            'width' => imagesx(imagecreatefromstring($png)),
            'height' => imagesy(imagecreatefromstring($png)),
            'size_bytes' => strlen($png),
            'accepted' => true,
            'rejection_reason' => null,
        ]);

        $this->refreshJobProgress($job);

        return $frame;
    }

    public function delete(
        DriverVehicle3dJob $job,
        DriverVehicle3dFrame $frame
    ): void {
        Storage::disk('local')->delete(array_filter([
            $frame->path,
            $frame->thumbnail_path,
        ]));

        $frame->delete();

        $this->refreshJobProgress($job);
    }

    public function refreshJobProgress(DriverVehicle3dJob $job): void
    {
        $captured = DriverVehicle3dFrame::query()
            ->where('driver_vehicle_3d_job_id', $job->id)
            ->where('accepted', true)
            ->distinct('sequence')
            ->count('sequence');

        $required = max(1, (int) $job->required_frames);

        $job->forceFill([
            'captured_frames' => $captured,
            'progress' => min(
                100,
                (int) round(($captured / $required) * 100)
            ),
            'status' => $captured >= $required
                ? 'capture_ready'
                : 'awaiting_capture',
        ])->save();
    }

    private function capturePosition(int $sequence): array
    {
        if ($sequence <= 24) {
            return [
                ($sequence - 1) * 15,
                'middle',
            ];
        }

        return [
            ($sequence - 25) * 60,
            'high',
        ];
    }

    private function resize($source, int $maximumSize)
    {
        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, $maximumSize / max($width, $height));

        $newWidth = max(1, (int) round($width * $scale));
        $newHeight = max(1, (int) round($height * $scale));

        $target = imagecreatetruecolor($newWidth, $newHeight);

        imagealphablending($target, false);
        imagesavealpha($target, true);

        imagecopyresampled(
            $target,
            $source,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $width,
            $height
        );

        return $target;
    }

    private function encodePng($image): string
    {
        ob_start();
        imagepng($image, null, 7);
        $png = ob_get_clean();

        if (! is_string($png)) {
            throw new RuntimeException(
                'No fue posible convertir la fotografía a PNG.'
            );
        }

        return $png;
    }

    private function correctOrientation($image, UploadedFile $file)
    {
        if (
            ! function_exists('exif_read_data')
            || ! in_array(
                strtolower($file->getClientOriginalExtension()),
                ['jpg', 'jpeg'],
                true
            )
        ) {
            return $image;
        }

        $exif = @exif_read_data($file->getRealPath());
        $orientation = (int) ($exif['Orientation'] ?? 1);

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        if ($rotated !== $image) {
            imagedestroy($image);
        }

        return $rotated;
    }
}
