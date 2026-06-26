<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Actions\Admin\PruneLogFilesAction;

it('deletes log files older than the retention window and keeps recent ones', function () {
    $dir = storage_path('logs/__prune_test_' . uniqid());
    mkdir($dir, 0777, true);

    $old = $dir . '/laravel-old.log';
    $recent = $dir . '/laravel-recent.log';
    file_put_contents($old, 'old');
    file_put_contents($recent, 'recent');

    // Envelhece o arquivo "old" em 10 dias.
    touch($old, time() - (10 * 86400));

    $deleted = app(PruneLogFilesAction::class)->exec(keepDays: 3, directory: $dir);

    expect($deleted)->toBe(1)
        ->and(file_exists($old))->toBeFalse()   // removido (>3 dias)
        ->and(file_exists($recent))->toBeTrue(); // mantido

    @unlink($recent);
    @rmdir($dir);
});

it('runs the prune command successfully', function () {
    // --days alto: não remove nada real, apenas valida a execução do comando.
    $this->artisan('app:prune-logs', ['--days' => 3650])->assertExitCode(0);
});
