<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Auth;

use App\Actions\Auth\SendMagicLinkAction;
use App\Enums\UserStatusEnum;
use App\Models\User;
use App\Notifications\Auth\MagicLinkNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    Notification::fake();
    $this->action = app(SendMagicLinkAction::class);
});

it('creates a token and notifies an active user', function () {
    $user = User::factory()->active()->create(['email' => 'user@example.com']);

    $this->action->exec('user@example.com', remember: true);

    expect($user->magicLoginTokens()->count())->toBe(1)
        ->and($user->magicLoginTokens()->first()->remember)->toBeTrue();

    Notification::assertSentTo($user, MagicLinkNotification::class);
});

it('does nothing for an unknown email', function () {
    $this->action->exec('ghost@example.com');

    Notification::assertNothingSent();
});

it('does nothing for an inactive user', function () {
    $user = User::factory()->create([
        'email' => 'inactive@example.com',
        'status' => UserStatusEnum::INACTIVE,
    ]);

    $this->action->exec('inactive@example.com');

    expect($user->magicLoginTokens()->count())->toBe(0);
    Notification::assertNothingSent();
});

it('invalidates previous tokens before issuing a new one', function () {
    $user = User::factory()->active()->create(['email' => 'rotate@example.com']);

    $this->action->exec('rotate@example.com');
    $this->action->exec('rotate@example.com');

    expect($user->magicLoginTokens()->count())->toBe(1);
});
