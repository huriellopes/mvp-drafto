<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Mail\ReengagementMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

it('sends reengagement emails to inactive eligible users', function () {
    Mail::fake();

    $user = User::factory()->active()->create([
        'email_verified_at' => now()->subYear(),
        'wants_reengagement_emails' => true,
        'last_login_at' => now()->subDays(40),
        'created_at' => now()->subDays(40),
        'reengagement_stage' => null,
    ]);

    $this->artisan('users:reengage')
        ->assertExitCode(0);

    // ReengagementMail implementa ShouldQueue, então é enfileirado.
    Mail::assertQueued(ReengagementMail::class, fn ($mail) => $mail->hasTo($user->email));
});

it('does not email active recently-seen users', function () {
    Mail::fake();

    User::factory()->active()->create([
        'email_verified_at' => now(),
        'wants_reengagement_emails' => true,
        'last_login_at' => now(),
        'created_at' => now(),
    ]);

    $this->artisan('users:reengage')
        ->assertExitCode(0);

    Mail::assertNothingQueued();
});
