<?php

declare(strict_types=1);

namespace Tests\Feature\Logging;

use App\Logging\Telegram\CreateTelegramLogger;
use App\Logging\Telegram\TelegramLoggerHandler;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Mockery;
use Monolog\Level;
use Monolog\Logger;
use RuntimeException;

afterEach(function () {
    Mockery::close();
});

beforeEach(function () {
    config([
        'services.telegram.token' => 'TEST-TOKEN',
        'services.telegram.chat' => '123456',
        'services.telegram.thread' => null,
    ]);
});

it('builds a monolog logger with a telegram handler from config', function () {
    $logger = (new CreateTelegramLogger)(['level' => 'error']);

    expect($logger)->toBeInstanceOf(Logger::class)
        ->and($logger->getName())->toBe('telegram')
        ->and($logger->getHandlers()[0])->toBeInstanceOf(TelegramLoggerHandler::class);
});

it('uses the explicit numeric thread from the config array', function () {
    $logger = (new CreateTelegramLogger)(['thread' => 42, 'level' => 'warning']);

    expect($logger->getHandlers()[0])->toBeInstanceOf(TelegramLoggerHandler::class);
});

it('ignores a non-numeric thread value', function () {
    $logger = (new CreateTelegramLogger)(['thread' => 'not-a-number', 'level' => 'critical']);

    expect($logger->getHandlers()[0])->toBeInstanceOf(TelegramLoggerHandler::class);
});

it('falls back to the configured thread when none is passed in the array', function () {
    config(['services.telegram.thread' => 99]);

    $logger = (new CreateTelegramLogger)(['level' => 'error']);

    expect($logger->getHandlers()[0])->toBeInstanceOf(TelegramLoggerHandler::class);
});

it('sends a formatted HTML message to the telegram api on write', function () {
    Http::fake([
        'api.telegram.org/*' => Http::response(['ok' => true], 200),
    ]);

    $handler = new TelegramLoggerHandler('TOKEN', '123', null, Level::Error);
    $logger = new Logger('telegram');
    $logger->pushHandler($handler);

    $logger->error('Something exploded', ['detail' => 'value']);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), '/botTOKEN/sendMessage')
            && $request['parse_mode'] === 'HTML'
            && str_contains($request['text'], 'DRAFTO ALERTA [ERROR]')
            && str_contains($request['text'], 'Something exploded')
            && str_contains($request['text'], 'Contexto');
    });
});

it('includes the message_thread_id when a thread is configured', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);

    $handler = new TelegramLoggerHandler('TOKEN', '123', '777', Level::Error);
    $logger = new Logger('telegram');
    $logger->pushHandler($handler);

    $logger->error('threaded message');

    Http::assertSent(fn ($request) => $request['message_thread_id'] === '777');
});

it('formats exception details when an exception is present in the context', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);

    $handler = new TelegramLoggerHandler('TOKEN', '123', null, Level::Error);
    $logger = new Logger('telegram');
    $logger->pushHandler($handler);

    $logger->error('boom', ['exception' => new RuntimeException('kaput')]);

    Http::assertSent(fn ($request) => str_contains($request['text'], 'RuntimeException')
        && str_contains($request['text'], 'Erro:'));
});

it('logs to the daily channel when the telegram api responds with a failure', function () {
    Http::fake(['api.telegram.org/*' => Http::response('bad request', 400)]);

    $captured = null;
    Log::shouldReceive('channel')->with('daily')->andReturnSelf();
    Log::shouldReceive('error')->once()->andReturnUsing(function ($message) use (&$captured) {
        $captured = $message;
    });

    $handler = new TelegramLoggerHandler('TOKEN', '123', null, Level::Error);
    $logger = new Logger('telegram');
    $logger->pushHandler($handler);

    $logger->error('will fail at telegram');

    expect($captured)->toContain('ERRO API TELEGRAM');
});

it('logs to the daily channel when the http call throws', function () {
    Http::fake(function () {
        throw new ConnectionException('connection refused');
    });

    $captured = null;
    Log::shouldReceive('channel')->with('daily')->andReturnSelf();
    Log::shouldReceive('error')->once()->andReturnUsing(function ($message) use (&$captured) {
        $captured = $message;
    });

    $handler = new TelegramLoggerHandler('TOKEN', '123', null, Level::Error);
    $logger = new Logger('telegram');
    $logger->pushHandler($handler);

    $logger->error('will throw');

    expect($captured)->toContain('FALHA AO ENVIAR PRO TELEGRAM');
});

it('selects the correct emoji and level name for a warning record', function () {
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);

    $handler = new TelegramLoggerHandler('TOKEN', '123', null, Level::Warning);
    $logger = new Logger('telegram');
    $logger->pushHandler($handler);

    $logger->warning('heads up');

    Http::assertSent(fn ($request) => str_contains($request['text'], 'DRAFTO ALERTA [WARNING]')
        && str_contains($request['text'], '⚠️'));
});
