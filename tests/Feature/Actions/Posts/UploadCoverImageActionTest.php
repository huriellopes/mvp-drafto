<?php

declare(strict_types=1);

use App\Actions\Posts\UploadCoverImageAction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

beforeEach(function () {
    $this->action = new UploadCoverImageAction;
    Storage::fake('public');
});

it('crops, resizes and stores the cover image returning its path', function () {
    $file = UploadedFile::fake()->image('cover.jpg', 1500, 600);

    $cropData = ['width' => 1200, 'height' => 400, 'x' => 0, 'y' => 0];

    $path = $this->action->exec($file, $cropData);

    expect($path)->toStartWith('covers/')
        ->and($path)->toEndWith('.webp');

    Storage::disk('public')->assertExists($path);
});

it('scales the stored image to the requested target width', function () {
    $file = UploadedFile::fake()->image('cover.jpg', 2000, 800);

    $cropData = ['width' => 1800, 'height' => 600, 'x' => 0, 'y' => 0];

    $path = $this->action->exec($file, $cropData, targetWidth: 600, targetHeight: 200);

    Storage::disk('public')->assertExists($path);

    $stored = Storage::disk('public')->get($path);

    $image = ImageManager::usingDriver(new Driver)
        ->decode($stored);

    expect($image->width())->toBe(600);
});
