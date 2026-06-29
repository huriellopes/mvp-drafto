<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Actions\Auth\AuthenticateUserAction;
use App\DTOs\AuthenticateData;
use App\Enums\UserStatusEnum;
use App\Livewire\Auth\Login;
use App\Models\MagicLoginToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Livewire;

function queuedRecaller(): mixed
{
    return collect(app('cookie')->getQueuedCookies())
        ->first(fn ($c) => str_starts_with($c->getName(), 'remember_'));
}

it('password login WITH remember persists remember_token and queues the recaller cookie', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'status' => UserStatusEnum::ACTIVE,
    ]);

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'password123')
        ->set('form.remember', true)
        ->call('login');

    expect(Auth::check())->toBeTrue()
        ->and($user->fresh()->remember_token)->not->toBeNull()
        ->and(queuedRecaller())->not->toBeNull();
});

it('password login WITHOUT remember does not queue a recaller cookie', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'status' => UserStatusEnum::ACTIVE,
    ]);

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'password123')
        ->set('form.remember', false)
        ->call('login');

    expect(Auth::check())->toBeTrue()
        ->and(queuedRecaller())->toBeNull();
});

it('magic link login WITH remember persists remember_token and sets a recaller cookie', function () {
    $user = User::factory()->create(['status' => UserStatusEnum::ACTIVE]);

    $plain = Str::random(40);
    MagicLoginToken::create([
        'user_id' => $user->id,
        'token' => MagicLoginToken::hashToken($plain),
        'expires_at' => now()->addMinutes(15),
        'remember' => true,
    ]);

    $response = $this->get(route('auth.magic.verify', $plain));

    $response->assertRedirect(route('dashboard.index'));

    $recaller = collect($response->headers->getCookies())
        ->first(fn ($c) => str_starts_with($c->getName(), 'remember_'));

    expect(Auth::check())->toBeTrue()
        ->and($user->fresh()->remember_token)->not->toBeNull()
        ->and($recaller)->not->toBeNull();
});

it('magic link login WITHOUT remember does not set a recaller cookie', function () {
    $user = User::factory()->create(['status' => UserStatusEnum::ACTIVE]);

    $plain = Str::random(40);
    MagicLoginToken::create([
        'user_id' => $user->id,
        'token' => MagicLoginToken::hashToken($plain),
        'expires_at' => now()->addMinutes(15),
        'remember' => false,
    ]);

    $response = $this->get(route('auth.magic.verify', $plain));

    $recaller = collect($response->headers->getCookies())
        ->first(fn ($c) => str_starts_with($c->getName(), 'remember_'));

    expect($recaller)->toBeNull();
});

it('does NOT log out other devices (no password rehash) when remember is set', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $originalHash = $user->password;

    resolve(AuthenticateUserAction::class)->exec(
        AuthenticateData::from([
            'email' => $user->email,
            'password' => 'password123',
            'remember' => true,
        ]),
    );

    // Sem rehash da senha => logoutOtherDevices NÃO foi chamado => outros
    // dispositivos (e seus cookies "lembrar-me") permanecem válidos.
    expect($user->fresh()->password)->toBe($originalHash);
});

it('logs out other devices (rehashes password) when remember is NOT set', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password123'),
        'status' => UserStatusEnum::ACTIVE,
    ]);
    $originalHash = $user->password;

    resolve(AuthenticateUserAction::class)->exec(
        AuthenticateData::from([
            'email' => $user->email,
            'password' => 'password123',
            'remember' => false,
        ]),
    );

    // Rehash da senha => logoutOtherDevices foi chamado (sessão única).
    expect($user->fresh()->password)->not->toBe($originalHash);
});
