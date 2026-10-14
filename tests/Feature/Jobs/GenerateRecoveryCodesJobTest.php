<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Events\Exports\ExportFailed;
use App\Events\Exports\ExportReady;
use App\Jobs\GenerateRecoveryCodesJob;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

beforeEach(function (): void {
    Storage::fake('local');
    Event::fake([ExportReady::class, ExportFailed::class]);
});

it('writes a txt file with the recovery codes', function (): void {
    $user = User::factory()->withProfile()->create([
        'email' => 'codes@example.com',
        'two_factor_recovery_codes' => ['CODE-1111', 'CODE-2222'],
    ]);

    app()->call([new GenerateRecoveryCodesJob($user, 'txt', '20260628'), 'handle']);

    $username = Str::slug($user->profile->username);
    $path = "temp/exports/{$user->id}/drafto-{$username}-20260628.txt";

    Storage::disk('local')->assertExists($path);

    $content = Storage::disk('local')->get($path);

    expect($content)->toContain('CODE-1111')
        ->and($content)->toContain('CODE-2222')
        ->and($content)->toContain('codes@example.com')
        ->and($content)->toContain('CÓDIGOS DE RECUPERAÇÃO');
});

it('falls back to the user name when there is no profile', function (): void {
    $user = User::factory()->create([
        'name' => 'John Smith',
        'two_factor_recovery_codes' => ['AAA-BBB'],
    ]);

    app()->call([new GenerateRecoveryCodesJob($user, 'txt', '20260628'), 'handle']);

    $path = "temp/exports/{$user->id}/drafto-john-smith-20260628.txt";

    Storage::disk('local')->assertExists($path);
});

it('does nothing when the user has no recovery codes', function (): void {
    $user = User::factory()->withProfile()->create([
        'two_factor_recovery_codes' => null,
    ]);

    app()->call([new GenerateRecoveryCodesJob($user, 'txt', '20260628'), 'handle']);

    expect(Storage::disk('local')->allFiles('temp'))->toBeEmpty();
});

it('writes a png file when the format is png', function (): void {
    $user = User::factory()->withProfile()->create([
        'two_factor_recovery_codes' => ['CODE-1111', 'CODE-2222'],
    ]);

    app()->call([new GenerateRecoveryCodesJob($user, 'png', '20260628'), 'handle']);

    $username = Str::slug($user->profile->username);
    $path = "temp/exports/{$user->id}/drafto-{$username}-20260628.png";

    Storage::disk('local')->assertExists($path);
    expect(Storage::disk('local')->get($path))->not->toBeEmpty();
});

it('pushes ExportReady to the owner once the file is stored', function (): void {
    $user = User::factory()->create([
        'name' => 'John Smith',
        'two_factor_recovery_codes' => ['AAA-BBB'],
    ]);

    app()->call([new GenerateRecoveryCodesJob($user, 'txt', '20260628'), 'handle']);

    Event::assertDispatched(
        ExportReady::class,
        fn (ExportReady $event): bool => $event->userId === $user->id
            && $event->path === "temp/exports/{$user->id}/drafto-john-smith-20260628.txt",
    );
});

it('pushes ExportFailed to the owner when the job fails', function (): void {
    $user = User::factory()->create(['name' => 'John Smith']);

    (new GenerateRecoveryCodesJob($user, 'txt', '20260628'))->failed(new RuntimeException('boom'));

    Event::assertDispatched(
        ExportFailed::class,
        fn (ExportFailed $event): bool => $event->path === "temp/exports/{$user->id}/drafto-john-smith-20260628.txt",
    );
});
