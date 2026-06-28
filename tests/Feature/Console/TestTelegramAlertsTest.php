<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Log;
use Mockery;
use Psr\Log\LoggerInterface;

it('sends test telegram alerts to the support and alerts channels', function () {
    // Substitui os canais do Telegram por mocks para evitar HTTP real.
    $support = Mockery::mock(LoggerInterface::class);
    $support->shouldReceive('info')->once();

    $alerts = Mockery::mock(LoggerInterface::class);
    $alerts->shouldReceive('error')->once();

    Log::shouldReceive('channel')->with('telegram_support')->andReturn($support);
    Log::shouldReceive('channel')->with('telegram_alerts')->andReturn($alerts);

    $this->artisan('drafto:test-telegram')
        ->expectsOutputToContain('Todos os alertas de teste foram enviados!')
        ->assertExitCode(0);
});
