<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PostView;
use Exception;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

#[Signature('app:archive-post-views {--days=30 : The number of days to keep raw data}')]
#[Description('Archive and purge old granular post view records')]
final class ArchivePostViewsCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $days = (int) $this->option('days');
        $cutoffDate = now()->subDays($days);

        $this->info("Archiving post views older than {$days} days (before {$cutoffDate->toDateString()})...");

        try {
            DB::transaction(function () use ($cutoffDate) {

                $deletedCount = PostView::query()
                    ->where('viewed_at', '<', $cutoffDate)
                    ->delete();

                $this->info("Successfully purged {$deletedCount} old post view records.");

                Log::info("Post views archived. Purged {$deletedCount} records older than {$cutoffDate->toDateString()}.");
            });
        } catch (Exception $e) {
            $this->error("Failed to archive post views: {$e->getMessage()}");
            Log::error("Failed to archive post views: {$e->getMessage()}");
        }
    }
}
