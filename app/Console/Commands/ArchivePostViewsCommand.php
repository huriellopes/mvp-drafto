<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PostView;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class ArchivePostViewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:archive-post-views {--days=30 : The number of days to keep raw data}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive and purge old granular post view records';

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
                // Here we could aggregate data into a 'post_views_daily' table if needed for analytics.
                // For this MVP, we simply purge old granular data to keep the table lean.
                // The total counts are already persisted in the 'posts' table via 'views_count'.

                $deletedCount = PostView::where('viewed_at', '<', $cutoffDate)->delete();

                $this->info("Successfully purged {$deletedCount} old post view records.");

                Log::info("Post views archived. Purged {$deletedCount} records older than {$cutoffDate->toDateString()}.");
            });
        } catch (Exception $e) {
            $this->error("Failed to archive post views: {$e->getMessage()}");
            Log::error("Failed to archive post views: {$e->getMessage()}");
        }
    }
}
