<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

it('logs an error and does not throw when archiving fails', function () {
    Log::spy();

    // Dropping the table forces the delete query to fail inside the transaction,
    // exercising the catch block (lines 40-42).
    Schema::drop('post_views');

    $this->artisan('app:archive-post-views', ['--days' => 10])
        ->expectsOutputToContain('Failed to archive post views')
        ->assertExitCode(0);

    Log::shouldHaveReceived('error')
        ->withArgs(fn ($message) => str_contains($message, 'Failed to archive post views'));
});
