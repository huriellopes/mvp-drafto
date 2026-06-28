<?php

declare(strict_types=1);

namespace Tests\Feature\Logging;

use App\Logging\Telegram\TelegramLoggerHandler;
use Illuminate\Support\Facades\Http;
use Monolog\Level;
use Monolog\Logger;

it('selects the critical emoji and level name for a critical record', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);

    $handler = new TelegramLoggerHandler('TOKEN', '123', null, Level::Critical);
    $logger = new Logger('telegram');
    $logger->pushHandler($handler);

    $logger->critical('meltdown');

    Http::assertSent(fn ($request) => str_contains($request['text'], 'DRAFTO ALERTA [CRITICAL]')
        && str_contains($request['text'], '🔥'));
});

it('selects the emergency emoji for an emergency record', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);

    $handler = new TelegramLoggerHandler('TOKEN', '123', null, Level::Emergency);
    $logger = new Logger('telegram');
    $logger->pushHandler($handler);

    $logger->emergency('total failure');

    Http::assertSent(fn ($request) => str_contains($request['text'], '🆘'));
});

it('selects the alert emoji for an alert record', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);

    $handler = new TelegramLoggerHandler('TOKEN', '123', null, Level::Alert);
    $logger = new Logger('telegram');
    $logger->pushHandler($handler);

    $logger->alert('escalate now');

    Http::assertSent(fn ($request) => str_contains($request['text'], '🔔')
        && str_contains($request['text'], 'DRAFTO ALERTA [ALERT]'));
});
