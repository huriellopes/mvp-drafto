<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use Illuminate\Support\Facades\Storage;

it('deletes temporary export files but keeps dotfiles', function () {
    Storage::fake('local');

    Storage::disk('local')->put('temp/export-1.csv', 'a');
    Storage::disk('local')->put('temp/export-2.csv', 'b');
    Storage::disk('local')->put('temp/.gitignore', 'keep');

    $this->artisan('app:cleanup-exports')
        ->assertExitCode(0);

    Storage::disk('local')->assertMissing('temp/export-1.csv');
    Storage::disk('local')->assertMissing('temp/export-2.csv');
    Storage::disk('local')->assertExists('temp/.gitignore');
});

it('runs successfully when there are no temp files', function () {
    Storage::fake('local');

    $this->artisan('app:cleanup-exports')
        ->assertExitCode(0);
});
