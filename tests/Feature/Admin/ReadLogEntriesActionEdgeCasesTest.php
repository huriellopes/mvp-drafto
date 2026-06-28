<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Actions\Admin\ReadLogEntriesAction;
use Illuminate\Support\Facades\DB;

afterEach(function () {
    foreach (glob(storage_path('logs/__edge_*.log')) ?: [] as $f) {
        @unlink($f);
    }
});

it('returns "Desconhecido" for a failed job with an invalid (non-array) payload', function () {
    DB::table('failed_jobs')->insert([
        'uuid' => 'edge-uuid-bad-payload',
        'connection' => 'redis',
        'queue' => 'default',
        'payload' => '"just-a-string"', // valid JSON, but not an array
        'exception' => "Exception: boom\n#0 ...",
        'failed_at' => now(),
    ]);

    $job = app(ReadLogEntriesAction::class)->failedJobs()
        ->firstWhere('uuid', 'edge-uuid-bad-payload');

    expect($job['job'])->toBe('Desconhecido');
});

it('resolves the job name from the serialized commandName when displayName is absent', function () {
    DB::table('failed_jobs')->insert([
        'uuid' => 'edge-uuid-command',
        'connection' => 'redis',
        'queue' => 'default',
        'payload' => json_encode(['data' => ['commandName' => 'App\\Jobs\\ProcessThing']]),
        'exception' => "Exception: boom\n#0 ...",
        'failed_at' => now(),
    ]);

    $job = app(ReadLogEntriesAction::class)->failedJobs()
        ->firstWhere('uuid', 'edge-uuid-command');

    expect($job['job'])->toBe('App\\Jobs\\ProcessThing');
});

it('returns "Desconhecido" when neither displayName nor commandName are present', function () {
    DB::table('failed_jobs')->insert([
        'uuid' => 'edge-uuid-empty',
        'connection' => 'redis',
        'queue' => 'default',
        'payload' => json_encode(['data' => ['something' => 'else']]),
        'exception' => "Exception: boom\n#0 ...",
        'failed_at' => now(),
    ]);

    $job = app(ReadLogEntriesAction::class)->failedJobs()
        ->firstWhere('uuid', 'edge-uuid-empty');

    expect($job['job'])->toBe('Desconhecido');
});

it('trims the leading partial entry when the log file is larger than the byte cap', function () {
    // Build a log file larger than MAX_BYTES (~2MB). The first "entry" begins with
    // padding (no date bracket) so the tail() trimming logic (lines 156-160) drops it.
    $padding = str_repeat("partial garbage line without a date prefix\n", 60000);
    $realEntry = "[2026-06-26 12:00:00] production.ERROR: erro depois do corte real\n";

    $name = '__edge_large.log';
    file_put_contents(storage_path('logs/' . $name), $padding . $realEntry);

    expect(filesize(storage_path('logs/' . $name)))->toBeGreaterThan(2_000_000);

    $entries = app(ReadLogEntriesAction::class)->exec($name, ['error']);

    expect($entries->pluck('summary')->implode(' '))->toContain('erro depois do corte real');
});
