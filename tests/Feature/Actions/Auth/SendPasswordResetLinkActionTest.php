<?php

declare(strict_types=1);

use App\Actions\Auth\SendPasswordResetLinkAction;
use App\Models\User;
use App\Notifications\Auth\ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    Notification::fake();
    $this->action = app(SendPasswordResetLinkAction::class);
});

it('sends a password reset link to an existing user', function () {
    $user = User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    $this->action->exec($user->email);

    Notification::assertSentTo($user, ResetPasswordNotification::class);
});

it('throws a validation exception when no user matches the email', function () {
    $this->action->exec('missing@example.com');
})->throws(ValidationException::class);

it('does not send any notification when the email is unknown', function () {
    try {
        $this->action->exec('missing@example.com');
    } catch (ValidationException) {
        // expected
    }

    Notification::assertNothingSent();
});
