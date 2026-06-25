<?php

declare(strict_types=1);

namespace Tests\Feature\Public;

use App\Actions\Profile\GenerateProfileQrCodeAction;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

it('downloads the profile qr code as a png attachment', function () {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    $response = $this->get(route('public.profile.qrcode', $profile->username));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'image/png');
    $response->assertDownload("drafto-qrcode-{$profile->username}.png");

    // O conteúdo é um PNG válido (assinatura mágica + dimensões legíveis).
    $content = $response->getContent();
    expect(str_starts_with($content, "\x89PNG\r\n\x1a\n"))->toBeTrue();
    expect(getimagesizefromstring($content)[2])->toBe(IMAGETYPE_PNG);
});

it('returns 404 for an unknown username', function () {
    $this->get(route('public.profile.qrcode', 'naoexiste'))->assertNotFound();
});

it('does not persist any file on the server when downloading', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    $response = $this->get(route('public.profile.qrcode', $profile->username));

    $response->assertOk();

    // Nada deve ser gravado em disco: a imagem é gerada e transmitida em memória.
    expect(Storage::disk('local')->allFiles())->toBeEmpty();

    // E não é entregue a partir de um arquivo físico (não é um BinaryFileResponse).
    expect($response->baseResponse)->not->toBeInstanceOf(BinaryFileResponse::class);
});

it('generates a valid in-memory png without touching disk via the action', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    Profile::factory()->create(['user_id' => $user->id]);

    $png = app(GenerateProfileQrCodeAction::class)->exec($user->fresh()->load('profile'));

    expect(str_starts_with($png, "\x89PNG\r\n\x1a\n"))->toBeTrue();
    expect(getimagesizefromstring($png)[2])->toBe(IMAGETYPE_PNG);
    expect(Storage::disk('local')->allFiles())->toBeEmpty();
});
