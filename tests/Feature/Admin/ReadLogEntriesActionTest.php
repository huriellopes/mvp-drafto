<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Actions\Admin\ReadLogEntriesAction;
use Illuminate\Support\Facades\DB;

function writeTestLog(string $name, string $content): string
{
    $path = storage_path('logs/' . $name);
    file_put_contents($path, $content);

    return $name;
}

afterEach(function () {
    foreach (glob(storage_path('logs/__test_*.log')) ?: [] as $f) {
        @unlink($f);
    }
});

it('parses error-level entries from a log file', function () {
    $file = writeTestLog('__test_app.log', <<<'LOG'
        [2026-06-26 10:00:00] production.ERROR: 💥 Erro 500: explosão {"trace_id":"abc"}
        #0 /app/Foo.php(10): boom()
        #1 {main}
        [2026-06-26 10:01:00] production.DEBUG: tarefa concluída
        [2026-06-26 10:02:00] production.INFO: novo usuário
        LOG);

    $entries = app(ReadLogEntriesAction::class)->exec($file, ['error']);

    expect($entries)->toHaveCount(1)
        ->and($entries->first()->level)->toBe('error')
        ->and($entries->first()->summary)->toContain('Erro 500: explosão')
        ->and($entries->first()->details)->toContain('#0 /app/Foo.php');
});

it('filters debug and info entries for the debug tab', function () {
    $file = writeTestLog('__test_debug.log', <<<'LOG'
        [2026-06-26 10:00:00] production.ERROR: erro grave
        [2026-06-26 10:01:00] production.DEBUG: detalhe de debug
        [2026-06-26 10:02:00] production.INFO: informação
        LOG);

    $entries = app(ReadLogEntriesAction::class)->exec($file, ['debug', 'info', 'notice', 'warning']);

    expect($entries)->toHaveCount(2)
        ->and($entries->pluck('level')->all())->each->toBeIn(['debug', 'info', 'notice', 'warning']);
});

it('lists failed jobs from the failed_jobs table', function () {
    DB::table('failed_jobs')->insert([
        'uuid' => 'job-uuid-1',
        'connection' => 'redis',
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'App\\Jobs\\SendNewsletter']),
        'exception' => "RuntimeException: smtp caiu\n#0 /app/...",
        'failed_at' => now(),
    ]);

    $jobs = app(ReadLogEntriesAction::class)->failedJobs();

    expect($jobs)->toHaveCount(1)
        ->and($jobs->first()['job'])->toBe('App\\Jobs\\SendNewsletter')
        ->and($jobs->first()['error'])->toContain('smtp caiu')
        ->and($jobs->first()['uuid'])->toBe('job-uuid-1');
});
