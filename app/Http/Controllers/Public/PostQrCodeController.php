<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Actions\Profile\GenerateProfileQrCodeAction;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class PostQrCodeController extends Controller
{
    public function download(string $slug): Response
    {
        $post = Post::query()
            ->where('slug', $slug)
            ->firstOrFail();

        $png = resolve(GenerateProfileQrCodeAction::class)
            ->pngFromUrl(
                url: $post->getShareUrl(),
                label: Str::limit($post->title, 28),
            );

        $fileName = "drafto-qrcode-{$post->slug}.png";

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}
