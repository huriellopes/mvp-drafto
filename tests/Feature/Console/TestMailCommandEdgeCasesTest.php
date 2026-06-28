<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Exception;
use Illuminate\Support\Facades\Mail;
use Mockery;

afterEach(function () {
    Mockery::close();
});

it('sends a raw test email through the message closure on success', function () {
    Mail::fake();

    $this->artisan('drafto:test-mail', ['email' => 'ops@example.com', '--mailer' => 'support'])
        ->expectsOutputToContain('teste enviado com sucesso')
        ->assertExitCode(0);
});

it('fails gracefully when the mailer throws during send', function () {
    // Make the resolved mailer throw so the catch block (lines 50-53) runs.
    $throwingMailer = Mockery::mock();
    $throwingMailer->shouldReceive('raw')->andThrow(new Exception('connection refused'));

    Mail::shouldReceive('mailer')->with('contact')->andReturn($throwingMailer);

    $this->artisan('drafto:test-mail', ['email' => 'ops@example.com', '--mailer' => 'contact'])
        ->expectsOutputToContain('Falha no envio')
        ->assertExitCode(1);
});
