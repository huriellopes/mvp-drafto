<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Engagement;

use App\Actions\Engagement\SendReengagementEmailAction;
use App\Mail\ReengagementMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    $this->action = app(SendReengagementEmailAction::class);
});

it('returns false when the user is not eligible', function () {
    $user = User::factory()->create([
        'wants_reengagement_emails' => false,
        'last_login_at' => now()->subDays(90),
    ]);

    expect($this->action->exec($user))->toBeFalse();
    Mail::assertNothingQueued();
});

it('returns false when the same stage has already been sent', function () {
    $user = User::factory()->create([
        'wants_reengagement_emails' => true,
        'email_verified_at' => now(),
        'last_login_at' => now()->subDays(70),
        'created_at' => now()->subDays(70),
        'reengagement_stage' => 60,
    ]);

    // resolved stage will be 60 and user already at stage 60 -> blocked
    expect($this->action->exec($user))->toBeFalse();
    Mail::assertNothingQueued();
});

it('sends the email and records the stage when eligible and not yet sent', function () {
    $user = User::factory()->create([
        'wants_reengagement_emails' => true,
        'email_verified_at' => now(),
        'last_login_at' => now()->subDays(70),
        'created_at' => now()->subDays(70),
        'reengagement_stage' => null,
    ]);

    expect($this->action->exec($user))->toBeTrue();

    Mail::assertQueued(ReengagementMail::class);
    expect($user->fresh()->reengagement_stage)->toBe(60);
});
