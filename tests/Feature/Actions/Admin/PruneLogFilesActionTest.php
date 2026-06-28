<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Admin;

use App\Actions\Admin\PruneLogFilesAction;

beforeEach(function () {
    $this->action = app(PruneLogFilesAction::class);
    $this->dir = sys_get_temp_dir() . '/prune-logs-' . uniqid();
    mkdir($this->dir);
});

afterEach(function () {
    foreach (glob($this->dir . '/*') ?: [] as $file) {
        @unlink($file);
    }
    @rmdir($this->dir);
});

it('removes log files older than the retention window', function () {
    $old = $this->dir . '/old.log';
    $recent = $this->dir . '/recent.log';

    file_put_contents($old, 'old');
    file_put_contents($recent, 'recent');

    touch($old, now()->subDays(30)->getTimestamp());
    touch($recent, now()->subHours(1)->getTimestamp());

    $deleted = $this->action->exec(keepDays: 7, directory: $this->dir);

    expect($deleted)->toBe(1)
        ->and(file_exists($old))->toBeFalse()
        ->and(file_exists($recent))->toBeTrue();
});

it('only targets .log files', function () {
    $log = $this->dir . '/app.log';
    $txt = $this->dir . '/notes.txt';

    file_put_contents($log, 'x');
    file_put_contents($txt, 'x');

    touch($log, now()->subDays(60)->getTimestamp());
    touch($txt, now()->subDays(60)->getTimestamp());

    $deleted = $this->action->exec(keepDays: 7, directory: $this->dir);

    expect($deleted)->toBe(1)
        ->and(file_exists($txt))->toBeTrue();
});

it('returns zero when nothing is old enough to prune', function () {
    file_put_contents($this->dir . '/fresh.log', 'x');

    $deleted = $this->action->exec(keepDays: 7, directory: $this->dir);

    expect($deleted)->toBe(0);
});
