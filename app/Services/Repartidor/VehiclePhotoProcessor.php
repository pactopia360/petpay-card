<?php

namespace App\Services\Repartidor;

use App\Models\Repartidor\DriverVehicle;
use App\Models\Repartidor\DriverVehiclePhoto;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class VehiclePhotoProcessor
{
    private const POSITIONS = [
        'front' => 10,
        'front_left' => 20,
        'left' => 30,
        'rear' => 40,
        'right' => 50,
        'front_right' => 60,
        'plate' => 70,
        'dashboard' => 80,
    ];

    public function sync(
        DriverVehicle $vehicle,
        Request $request
    ): void {
        foreach (self::POSITIONS as $position => $sequence) {
            $file = $this->fileForPosition(
                $request,
                $position
            );

            if (! $file instanceof UploadedFile) {
                continue;
            }

            $this->store(
                $vehicle,
                $file,
                $position,
                $sequence
            );
        }
    }

    private function fileForPosition(
        Request $request,
        string $position
    ): ?UploadedFile {
        $cameraFile = $request->file(
            'vehicle_camera.'.$position
        );

        if ($cameraFile instanceof UploadedFile) {
            return $cameraFile;
        }

        $uploadedFile = $request->file(
            'vehicle_photos.'.$position
        );

        return $uploadedFile instanceof UploadedFile
            ? $uploadedFile
            : null;
    }

    private function store(
        DriverVehicle $vehicle,
        UploadedFile $file,
        string $position,
        int $sequence
    ): void {
        $mime = (string) $file->getMimeType();

        $source = $this->createSourceImage(
            $file,
            $mime
        );

        $source = $this->correctOrientation(
            $source,
            $file,
            $mime
        );

        $main = $this->resizeImage(
            $source,
            1800,
            1800
        );

        $thumbnail = $this->resizeImage(
            $source,
            520,
            520
        );

        $directory = implode('/', [
            'repartidor',
            'vehicles',
            (string) $vehicle->driver_user_id,
            (string) $vehicle->uuid,
            'photos',
        ]);

        $baseName = $position.'-'.Str::uuid();

        $mainPath = $directory.'/'.$baseName.'.png';
        $thumbnailPath =
            $directory.'/'.$baseName.'-thumb.png';

        $mainContents = $this->pngContents($main);
        $thumbnailContents = $this->pngContents(
            $thumbnail
        );

        Storage::disk('local')->put(
            $mainPath,
            $mainContents
        );

        Storage::disk('local')->put(
            $thumbnailPath,
            $thumbnailContents
        );

        $existing = DriverVehiclePhoto::query()
            ->where('driver_vehicle_id', $vehicle->id)
            ->where('position', $position)
            ->first();

        $oldPath = $existing?->path;
        $oldThumbnail = $existing?->thumbnail_path;

        $photo = $existing ?? new DriverVehiclePhoto();

        $photo->fill([
            'uuid' => $photo->uuid ?: (string) Str::uuid(),
            'driver_vehicle_id' => $vehicle->id,
            'driver_user_id' => $vehicle->driver_user_id,
            'position' => $position,
            'path' => $mainPath,
            'thumbnail_path' => $thumbnailPath,
            'original_name' => $file->getClientOriginalName(),
            'original_mime_type' => $mime,
            'mime_type' => 'image/png',
            'sha256' => hash('sha256', $mainContents),
            'width' => imagesx($main),
            'height' => imagesy($main),
            'size_bytes' => strlen($mainContents),
            'sequence' => $sequence,
            'is_plate_visible' => $position === 'plate',
            'is_primary' => $position === 'front',
            'status' => 'pending',
            'review_notes' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
        ]);

        $photo->save();

        if ($oldPath && $oldPath !== $mainPath) {
            Storage::disk('local')->delete($oldPath);
        }

        if (
            $oldThumbnail &&
            $oldThumbnail !== $thumbnailPath
        ) {
            Storage::disk('local')->delete(
                $oldThumbnail
            );
        }

        imagedestroy($source);
        imagedestroy($main);
        imagedestroy($thumbnail);
    }

    private function createSourceImage(
        UploadedFile $file,
        string $mime
    ): \GdImage {
        $path = $file->getRealPath();

        $image = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
            default => false,
        };

        if (! $image instanceof \GdImage) {
            throw new RuntimeException(
                'No se pudo procesar una fotografía del vehículo.'
            );
        }

        return $image;
    }

    private function correctOrientation(
        \GdImage $image,
        UploadedFile $file,
        string $mime
    ): \GdImage {
        if (
            $mime !== 'image/jpeg' ||
            ! function_exists('exif_read_data')
        ) {
            return $image;
        }

        $exif = @exif_read_data(
            $file->getRealPath()
        );

        $orientation = (int) (
            $exif['Orientation'] ?? 1
        );

        $rotated = match ($orientation) {
            3 => imagerotate($image, 180, 0),
            6 => imagerotate($image, -90, 0),
            8 => imagerotate($image, 90, 0),
            default => $image,
        };

        if (
            $rotated instanceof \GdImage &&
            $rotated !== $image
        ) {
            imagedestroy($image);

            return $rotated;
        }

        return $image;
    }

    private function resizeImage(
        \GdImage $source,
        int $maximumWidth,
        int $maximumHeight
    ): \GdImage {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);

        $ratio = min(
            $maximumWidth / $sourceWidth,
            $maximumHeight / $sourceHeight,
            1
        );

        $width = max(
            1,
            (int) round($sourceWidth * $ratio)
        );

        $height = max(
            1,
            (int) round($sourceHeight * $ratio)
        );

        $destination = imagecreatetruecolor(
            $width,
            $height
        );

        imagealphablending($destination, false);
        imagesavealpha($destination, true);

        $transparent = imagecolorallocatealpha(
            $destination,
            255,
            255,
            255,
            127
        );

        imagefill(
            $destination,
            0,
            0,
            $transparent
        );

        imagecopyresampled(
            $destination,
            $source,
            0,
            0,
            0,
            0,
            $width,
            $height,
            $sourceWidth,
            $sourceHeight
        );

        return $destination;
    }

    private function pngContents(
        \GdImage $image
    ): string {
        ob_start();

        imagepng($image, null, 6);

        $contents = ob_get_clean();

        if (! is_string($contents)) {
            throw new RuntimeException(
                'No se pudo generar la imagen PNG.'
            );
        }

        return $contents;
    }
}
