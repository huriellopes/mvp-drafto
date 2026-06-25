<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Mail;

it('sends a test email via the chosen mailer and succeeds', function () {
    Mail::fake();

    $this->artisan('drafto:test-mail', ['email' => 'ops@example.com', '--mailer' => 'contact'])
        ->assertExitCode(0);
});

it('fails for an invalid email address', function () {
    Mail::fake();

    $this->artisan('drafto:test-mail', ['email' => 'not-an-email'])
        ->assertExitCode(1);
});

it('fails for an unknown mailer', function () {
    Mail::fake();

    $this->artisan('drafto:test-mail', ['email' => 'ops@example.com', '--mailer' => 'mailgun'])
        ->assertExitCode(1);
});
