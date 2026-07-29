<?php

declare(strict_types=1);

namespace Tests\Feature\Monitoring;

use App\Models\User;
use App\Notifications\Auth\MagicLinkNotification;
use Closure;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Http;
use Mockery;
use RuntimeException;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

// Intercepta os envios ao Telegram (o handler usa Http::post à API).
beforeEach(function () {
    config(['services.telegram.token' => 'TEST_TOKEN', 'services.telegram.chat' => '999']);
    Http::fake(['api.telegram.org/*' => Http::response(['ok' => true])]);
});

function telegramSentContaining(string $needle): Closure
{
    return fn ($request) => str_contains($request->url(), 'api.telegram.org')
        && str_contains($request['text'] ?? '', $needle);
}

it('alerts the Telegram channel when a command finishes with an error', function () {
    event(new CommandFinished('drafto:foo', new ArrayInput([]), new BufferedOutput, 1));

    Http::assertSent(telegramSentContaining('drafto:foo'));
});

it('stays silent when a command finishes successfully', function () {
    event(new CommandFinished('drafto:foo', new ArrayInput([]), new BufferedOutput, 0));

    Http::assertNothingSent();
});

it('alerts when a scheduled task fails', function () {
    $task = app(Schedule::class)->command('inspire')->daily();

    event(new ScheduledTaskFailed($task, new RuntimeException('cron exploded')));

    Http::assertSent(telegramSentContaining('Tarefa agendada falhou'));
});

it('logs completed scheduled tasks to the debug channel', function () {
    $task = app(Schedule::class)->command('inspire')->daily();

    event(new ScheduledTaskFinished($task, 0.1));

    Http::assertSent(telegramSentContaining('Tarefa agendada concluída'));
});

it('skips logging for every-minute scheduled tasks to avoid flooding', function () {
    $task = app(Schedule::class)->command('inspire')->everyMinute();

    event(new ScheduledTaskFinished($task, 0.1));

    Http::assertNothingSent();
});

it('alerts when a queue job fails', function () {
    $job = Mockery::mock(Job::class);
    $job->shouldReceive('resolveName')->andReturn('App\\Jobs\\ExportDataJob');

    event(new JobFailed('database', $job, new RuntimeException('boom')));

    Http::assertSent(telegramSentContaining('App\\Jobs\\ExportDataJob'));
});

it('alerts only when a notification fails to send', function () {
    $user = User::factory()->create();

    event(new NotificationFailed($user, new MagicLinkNotification('tok'), 'mail', []));

    Http::assertSent(telegramSentContaining('MagicLinkNotification'));
});
