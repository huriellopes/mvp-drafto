<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Comment;
use App\Models\Follower;
use App\Models\Post;
use App\Models\PostView;
use App\Models\Profile;
use App\Models\Report;
use App\Policies\CommentPolicy;
use App\Policies\FollowerPolicy;
use App\Policies\PostPolicy;
use App\Policies\PostViewPolicy;
use App\Policies\ProfilePolicy;
use App\Policies\ReportPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Follower::class, FollowerPolicy::class);
        Gate::policy(PostView::class, PostViewPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Profile::class, ProfilePolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
    }
}
