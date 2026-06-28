<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Settings;

use App\Actions\Settings\UpdateUserSettingsAction;
use App\DTOs\UpdateUserSettingsData;
use App\Models\User;
use App\Notifications\Auth\VerifyEmailNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    $this->action = app(UpdateUserSettingsAction::class);
});

it('updates basic profile fields and preferences', function () {
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'same@example.com',
        'wants_product_updates' => true,
    ]);

    $this->action->exec($user, new UpdateUserSettingsData(
        name: 'New Name',
        email: 'same@example.com',
        wants_reengagement_emails: false,
        wants_product_updates: false,
    ));

    $user->refresh();

    expect($user->name)->toBe('New Name')
        ->and($user->wants_reengagement_emails)->toBeFalse()
        ->and($user->wants_product_updates)->toBeFalse();

    Notification::assertNothingSent();
});

it('resets verification and notifies when the email changes', function () {
    $user = User::factory()->create([
        'email' => 'old@example.com',
        'email_verified_at' => now(),
    ]);

    $this->action->exec($user, new UpdateUserSettingsData(
        name: $user->name,
        email: 'new@example.com',
    ));

    $user->refresh();

    expect($user->email)->toBe('new@example.com')
        ->and($user->email_verified_at)->toBeNull();

    Notification::assertSentTo($user, VerifyEmailNotification::class);
});

it('hashes and updates the password when provided', function () {
    $user = User::factory()->create(['email' => 'pw@example.com']);

    $this->action->exec($user, new UpdateUserSettingsData(
        name: $user->name,
        email: 'pw@example.com',
        password: 'new-secret-password',
    ));

    expect(Hash::check('new-secret-password', $user->fresh()->password))->toBeTrue();
});
