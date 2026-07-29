<?php

declare(strict_types=1);

namespace Tests\Feature\Exceptions;

use App\Exceptions\TelegramErrorReporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

beforeEach(function () {
    Cache::flush();
    config(['services.telegram.token' => 'TEST-TOKEN']);
});

it('does not alert for scanner-noise 404 paths', function () {
    app()->instance('request', Request::create('/wp-login.php', 'GET'));

    Log::spy();
    Log::shouldReceive('channel')->with('daily')->andReturnSelf();
    Log::shouldReceive('error')->andReturnNull();

    $reporter = new TelegramErrorReporter;

    expect($reporter->report(new NotFoundHttpException('not found')))->toBeFalse();

    // Scanner noise must NOT route to a telegram channel.
    Log::shouldNotHaveReceived('channel', ['telegram_debug']);
});

it('routes a 500 error to the critical telegram channel', function () {
    app()->instance('request', Request::create('/dashboard', 'GET'));

    $channels = [];
    Log::shouldReceive('channel')->andReturnUsing(function ($name) use (&$channels) {
        $channels[] = $name;

        return Log::getFacadeRoot();
    });
    Log::shouldReceive('error')->andReturnNull();

    $reporter = new TelegramErrorReporter;

    expect($reporter->report(new RuntimeException('explosion')))->toBeFalse()
        ->and($channels)->toContain('telegram_alerts');
});

it('routes a non-noise 404 to the debug telegram channel', function () {
    app()->instance('request', Request::create('/some/real/page', 'GET'));

    $channels = [];
    Log::shouldReceive('channel')->andReturnUsing(function ($name) use (&$channels) {
        $channels[] = $name;

        return Log::getFacadeRoot();
    });
    Log::shouldReceive('error')->andReturnNull();

    $reporter = new TelegramErrorReporter;

    $reporter->report(new NotFoundHttpException('missing'));

    expect($channels)->toContain('telegram_debug');
});
