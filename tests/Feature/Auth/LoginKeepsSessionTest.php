<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Enums\UserStatusEnum;
use App\Livewire\Auth\Login;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

// Regression (Laravel 13.30+): SessionGuard::login() now stores the password
// hash in the session, and logoutOtherDevices() rehashes the password right
// after it. If the session hash is not re-synced, AuthenticateSession logs
// the user out on the very next request (login bounced back to /login).
it('keeps the session password hash in sync after logging out other devices', function () {
    $user = User::factory()->withProfile()->create([
        'password' => Hash::make('password123'),
        'status' => UserStatusEnum::ACTIVE,
    ]);

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'password123')
        ->call('login');

    $guard = Auth::guard('web');

    expect(session('password_hash_web'))
        ->toBe($guard->hashPasswordForCookie($user->fresh()->getAuthPassword()));
});

it('stays authenticated on the request right after a password login', function () {
    $user = User::factory()->withProfile()->create([
        'password' => Hash::make('password123'),
        'status' => UserStatusEnum::ACTIVE,
    ]);

    Livewire::test(Login::class)
        ->set('form.email', $user->email)
        ->set('form.password', 'password123')
        ->call('login');

    $this->get(route('dashboard.index'))->assertOk();
    $this->assertAuthenticatedAs($user->fresh());
});
