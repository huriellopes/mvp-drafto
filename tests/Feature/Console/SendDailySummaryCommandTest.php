<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Log;
use Mockery;
use Psr\Log\LoggerInterface;

it('builds the daily summary and logs it to the telegram channel', function () {
    // Substitui o canal do Telegram por um mock para não disparar HTTP real.
    $channel = Mockery::mock(LoggerInterface::class);
    $channel->shouldReceive('info')->once();

    Log::shouldReceive('channel')
        ->with('telegram_support')
        ->once()
        ->andReturn($channel);

    $this->artisan('drafto:daily-summary')
        ->expectsOutputToContain('Resumo diário enviado ao Telegram.')
        ->assertExitCode(0);
});
