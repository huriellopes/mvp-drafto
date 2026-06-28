<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Auth;

use App\Livewire\Auth\ResendVerification;
use App\Models\User;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

beforeEach(function () {
    Notification::fake();
    Cache::flush(); // O rate limiter usa cache, que não é resetado pelo RefreshDatabase.
});

it('resends the verification email for an unverified user', function () {
    $user = User::factory()->unverified()->create();

    Livewire::actingAs($user)
        ->test(ResendVerification::class)
        ->call('resend')
        ->assertSet('sent', true);

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

it('does nothing when the user already verified the email', function () {
    $user = User::factory()->create(); // verified by default

    Livewire::actingAs($user)
        ->test(ResendVerification::class)
        ->call('resend')
        ->assertSet('sent', false);

    Notification::assertNothingSent();
});

it('shows an error when resending too many times', function () {
    $user = User::factory()->unverified()->create();
    RateLimiter::hit('resend-email:' . $user->id, 120);

    Livewire::actingAs($user)
        ->test(ResendVerification::class)
        ->call('resend')
        ->assertSet('sent', false)
        ->assertHasErrors('resend');

    Notification::assertNothingSent();
});
