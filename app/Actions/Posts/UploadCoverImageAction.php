<?php

declare(strict_types=1);

namespace App\Actions\Posts;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

final class UploadCoverImageAction
{
    /**
     * Executes the cover image upload, crop, and resize.
     * Ensures consistent 3:1 aspect ratio for profile covers.
     */
    public function exec(UploadedFile $file, array $cropData, int $targetWidth = 1200, int $targetHeight = 400): string
    {
        $fileName = hexdec(uniqid()) . '.webp';
        $path = 'covers/' . $fileName;

        // Sênior: A versão instalada utiliza decodePath e encodeUsingFileExtension
        $manager = ImageManager::usingDriver(new Driver);
        $image = $manager->decodePath($file->getRealPath());

        // Realizando o crop baseado nas coordenadas do CropperJS
        $image->crop(
            width: (int) $cropData['width'],
            height: (int) $cropData['height'],
            x: (int) $cropData['x'],
            y: (int) $cropData['y'],
        );

        // Sênior: Redimensionar mantendo a proporção do recorte para evitar distorção
        // Definimos uma largura padrão de 1200px para garantir qualidade em telas retina
        $image->scale(width: $targetWidth);

        // Salvando no storage public como WebP
        Storage::disk('public')->put($path, (string) $image->encodeUsingFileExtension('webp', 90));

        return $path;
    }
}
