<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Auth;

use App\Livewire\Auth\VerifyEmailNotice;
use App\Models\User;
use Illuminate\Support\Facades\URL;

it('renders the verify email notice page', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('verification.notice'))
        ->assertOk()
        ->assertSee('Thanks for signing up');
});

it('can verify email with valid link', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1($user->getEmailForVerification())],
    );

    $response = $this->actingAs($user)->get($verificationUrl);

    expect($user->refresh()->hasVerifiedEmail())->toBeTrue();
    $response->assertRedirect(route('dashboard.index'));
});

it('cannot verify email with invalid hash', function () {
    $user = User::factory()->unverified()->create();

    $verificationUrl = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        ['id' => $user->id, 'hash' => sha1('invalid-hash')],
    );

    $this->get($verificationUrl);

    expect($user->refresh()->hasVerifiedEmail())->toBeFalse();
});
