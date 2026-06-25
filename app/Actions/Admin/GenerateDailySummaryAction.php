<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\DTOs\DailySummaryData;
use App\Models\Comment;
use App\Models\Follower;
use App\Models\NewsletterSubscriber;
use App\Models\Post;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class GenerateDailySummaryAction
{
    /**
     * Agrega as métricas da plataforma das últimas 24h (ou desde $since).
     */
    public function exec(?Carbon $since = null): DailySummaryData
    {
        $since ??= Carbon::now()->subDay();

        return new DailySummaryData(
            newUsers: User::query()->where('created_at', '>=', $since)->count(),
            newPosts: Post::query()->published()->where('published_at', '>=', $since)->count(),
            newComments: Comment::query()->where('created_at', '>=', $since)->count(),
            newFollowers: Follower::query()->where('created_at', '>=', $since)->count(),
            newSubscribers: NewsletterSubscriber::query()->where('created_at', '>=', $since)->count(),
            newReports: Report::query()->where('created_at', '>=', $since)->count(),
            failedJobs: DB::table('failed_jobs')->where('failed_at', '>=', $since)->count(),
        );
    }
}
