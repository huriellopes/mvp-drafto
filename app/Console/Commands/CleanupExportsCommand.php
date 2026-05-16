<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

#[Signature('app:cleanup-exports')]
#[Description('Remove temporary export files from the storage folder')]
final class CleanupExportsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->info('Starting cleanup of temporary export files...');

        $files = Storage::disk('local')
            ->files('temp');

        $count = 0;

        foreach ($files as $file) {
            if (str_starts_with(basename($file), '.')) {
                continue;
            }

            Storage::disk('local')
                ->delete($file);
            $count++;
        }

        $this->info("Successfully deleted {$count} temporary files.");
    }
}
