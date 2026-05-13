<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Auth;

use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\ResetPassword;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;

it('renders forgot password page', function () {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertSeeLivewire(ForgotPassword::class);
});

it('can request a reset password link', function () {
    $user = User::factory()->create();

    Livewire::test(ForgotPassword::class)
        ->set('form.email', $user->email)
        ->call('sendResetLink')
        ->assertHasNoErrors()
        ->assertSet('sent', true);
});

it('renders reset password page with token', function () {
    $token = 'fake-token';

    $this->get(route('password.reset', ['token' => $token, 'email' => 'test@example.com']))
        ->assertOk()
        ->assertSeeLivewire(ResetPassword::class);
});

it('can reset password with valid token', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('old-password'),
    ]);

    $token = Password::createToken($user);

    Livewire::test(ResetPassword::class, ['token' => $token])
        ->set('form.email', 'test@example.com')
        ->set('form.password', 'new-password-123')
        ->set('form.password_confirmation', 'new-password-123')
        ->call('resetPassword')
        ->assertHasNoErrors()
        ->assertRedirect(route('login'));

    expect(Hash::check('new-password-123', $user->refresh()->password))->toBeTrue();
});
