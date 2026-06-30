<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Actions\Profile\GenerateProfileQrCodeAction;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

it('downloads the post qr code as a png attachment', function () {
    $post = Post::factory()->published()->create();

    $response = $this->get(route('public.posts.qrcode', $post->slug));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/png');
    $response->assertDownload("drafto-qrcode-{$post->slug}.png");

    // O conteúdo é um PNG válido (assinatura mágica + dimensões legíveis).
    $content = $response->getContent();
    expect(str_starts_with($content, "\x89PNG\r\n\x1a\n"))->toBeTrue();
    expect(getimagesizefromstring($content)[2])->toBe(IMAGETYPE_PNG);
});

it('returns 404 for an unknown post slug', function () {
    $this->get(route('public.posts.qrcode', 'artigo-inexistente'))->assertNotFound();
});

it('does not persist any file on the server when downloading the post qr code', function () {
    Storage::fake('local');

    $post = Post::factory()->published()->create();

    $response = $this->get(route('public.posts.qrcode', $post->slug));

    $response->assertOk();

    // Nada deve ser gravado em disco: a imagem é gerada e transmitida em memória.
    expect(Storage::disk('local')->allFiles())->toBeEmpty();

    // E não é entregue a partir de um arquivo físico (não é um BinaryFileResponse).
    expect($response->baseResponse)->not->toBeInstanceOf(BinaryFileResponse::class);
});

it('generates a valid in-memory png from a url without touching disk via the action', function () {
    Storage::fake('local');

    $png = app(GenerateProfileQrCodeAction::class)
        ->pngFromUrl('https://drafto.test/posts/exemplo', 'Exemplo de Artigo');

    expect(str_starts_with($png, "\x89PNG\r\n\x1a\n"))->toBeTrue();
    expect(getimagesizefromstring($png)[2])->toBe(IMAGETYPE_PNG);
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});

it('generates a valid svg from a url via the action', function () {
    $svg = app(GenerateProfileQrCodeAction::class)
        ->svgFromUrl('https://drafto.test/posts/exemplo');

    expect($svg)->toContain('<svg');
});
