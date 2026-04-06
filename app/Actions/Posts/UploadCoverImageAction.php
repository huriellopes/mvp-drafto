<?php

namespace App\Actions\Posts;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;

final class UploadCoverImageAction
{
    public function exec(UploadedFile $file, array $cropData): string
    {
        $fileName = hexdec(uniqid()) . '.' . $file->getClientOriginalExtension();
        $path = 'covers/' . $fileName;

        $image = Image::read($file->getRealPath());

        $image->crop(
            width: (int) $cropData['width'],
            height: (int) $cropData['height'],
            offset_x: (int) $cropData['x'],
            offset_y: (int) $cropData['y']
        );

        Storage::disk('public')->put($path, (string) $image->encode());

        return $path;
    }
}
