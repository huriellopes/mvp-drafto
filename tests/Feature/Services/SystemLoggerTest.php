<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Enums\LogCategoryEnum;
use App\Services\SystemLogger;
use Illuminate\Support\Facades\Log;
use Mockery;

it('routes security logs to the security channel with structured context', function () {
    $captured = null;

    Log::shouldReceive('channel')->once()->with('security')->andReturnSelf();
    Log::shouldReceive('error')->once()->andReturnUsing(function ($message, $context) use (&$captured) {
        $captured = ['message' => $message, 'context' => $context];
    });

    (new SystemLogger)->error('breach', LogCategoryEnum::SECURITY, ['extra' => 'value']);

    expect($captured['message'])->toContain('[Segurança] breach')
        ->and($captured['context']['category'])->toBe('security')
        ->and($captured['context']['extra'])->toBe('value')
        ->and($captured['context'])->toHaveKeys(['trace_id', 'user_id', 'ip', 'url']);
});

it('routes payment logs to the payments channel', function () {
    Log::shouldReceive('channel')->once()->with('payments')->andReturnSelf();
    Log::shouldReceive('warning')->once()->withArgs(
        fn ($message, $context) => str_contains($message, '[Pagamentos] charge failed'),
    );

    (new SystemLogger)->warning('charge failed', LogCategoryEnum::PAYMENT);

    expect(true)->toBeTrue();
});

it('routes queue logs to the jobs channel', function () {
    Log::shouldReceive('channel')->once()->with('jobs')->andReturnSelf();
    Log::shouldReceive('info')->once()->withArgs(
        fn ($message, $context) => str_contains($message, '[Filas/Jobs] job ran'),
    );

    (new SystemLogger)->info('job ran', LogCategoryEnum::QUEUE);

    expect(true)->toBeTrue();
});

it('routes other categories to the default channel', function () {
    config(['logging.default' => 'stack']);

    Log::shouldReceive('channel')->once()->with('stack')->andReturnSelf();
    Log::shouldReceive('info')->once()->withArgs(
        fn ($message, $context) => str_contains($message, '[Sistema] system message'),
    );

    (new SystemLogger)->info('system message', LogCategoryEnum::SYSTEM);

    expect(true)->toBeTrue();
});

it('defaults the category to system when none is supplied', function () {
    config(['logging.default' => 'stack']);
    $captured = null;

    Log::shouldReceive('channel')->once()->andReturnSelf();
    Log::shouldReceive('info')->once()->andReturnUsing(function ($message, $context) use (&$captured) {
        $captured = $context;
    });

    (new SystemLogger)->info('no category');

    expect($captured['category'])->toBe('system');
});

afterEach(function () {
    Mockery::close();
});
