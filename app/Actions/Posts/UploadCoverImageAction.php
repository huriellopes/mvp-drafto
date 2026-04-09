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
     * Ensures consistent 16:9 aspect ratio and high quality.
     */
    public function exec(UploadedFile $file, array $cropData): string
    {
        $fileName = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
        $path = 'covers/' . $fileName;

        $manager = ImageManager::usingDriver(new Driver());
        $image = $manager->decodePath($file->getRealPath());

        // Realizando o crop baseado nas coordenadas do CropperJS
        $image->crop(
            width: (int) $cropData['width'],
            height: (int) $cropData['height'],
            x: (int) $cropData['x'],
            y: (int) $cropData['y'],
        );

        // Opcional: Redimensionar para um tamanho padrão 16:9 (ex: 1200px de largura)
        // Isso garante que todas as capas tenham o mesmo peso e proporção no card.
        $image->resize(1200, 675);

        // Salvando no storage public com qualidade 90 (opcional na v4)
        Storage::disk('public')->put($path, (string) $image->encode());

        return $path;
    }
}
