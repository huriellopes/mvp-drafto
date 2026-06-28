<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Schema;

it('returns a failure exit code when the purge query throws', function () {
    // Dropping the table forces a query exception inside the try block so the
    // catch (lines 35-39) is exercised and the command returns FAILURE.
    Schema::drop('site_views');

    $this->artisan('app:purge-site-views', ['--days' => 10])
        ->expectsOutputToContain('Falha ao expurgar site_views')
        ->assertExitCode(1);
});
