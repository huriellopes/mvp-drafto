<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\Services\LoggerInterface;
use App\Enums\RoleEnum;
use App\Events\Posts\PostSaved;
use App\Listeners\PlatformMonitor;
use App\Listeners\Posts\HandlePostMediaAndSeo;
use App\Models\Collection;
use App\Models\Comment;
use App\Models\Follower;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PostCollection;
use App\Models\PostView;
use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use App\Observers\CommentObserver;
use App\Observers\PostObserver;
use App\Observers\UserObserver;
use App\Policies\CollectionPolicy;
use App\Policies\CommentPolicy;
use App\Policies\FollowerPolicy;
use App\Policies\PostCategoryPolicy;
use App\Policies\PostCollectionPolicy;
use App\Policies\PostPolicy;
use App\Policies\PostViewPolicy;
use App\Policies\ProfilePolicy;
use App\Policies\ReportPolicy;
use App\Services\SystemLogger;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoggerInterface::class, SystemLogger::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(PostSaved::class, HandlePostMediaAndSeo::class);

        // Observabilidade → Telegram (falhas de comandos/crons/jobs/notifications + execução de crons)
        Event::listen(CommandFinished::class, [PlatformMonitor::class, 'commandFinished']);
        Event::listen(ScheduledTaskFinished::class, [PlatformMonitor::class, 'scheduledTaskFinished']);
        Event::listen(ScheduledTaskFailed::class, [PlatformMonitor::class, 'scheduledTaskFailed']);
        Event::listen(JobFailed::class, [PlatformMonitor::class, 'jobFailed']);
        Event::listen(NotificationFailed::class, [PlatformMonitor::class, 'notificationFailed']);

        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Follower::class, FollowerPolicy::class);
        Gate::policy(PostView::class, PostViewPolicy::class);
        Gate::policy(Comment::class, CommentPolicy::class);
        Gate::policy(Profile::class, ProfilePolicy::class);
        Gate::policy(Report::class, ReportPolicy::class);
        Gate::policy(Collection::class, CollectionPolicy::class);
        Gate::policy(PostCategory::class, PostCategoryPolicy::class);
        Gate::policy(PostCollection::class, PostCollectionPolicy::class);

        User::observe(UserObserver::class);
        Post::observe(PostObserver::class);
        Comment::observe(CommentObserver::class);

        Blade::if('module', function (mixed $slug) {
            if (!function_exists('is_module_enabled')) {
                return false;
            }

            return is_module_enabled($slug);
        });

        Gate::define('admin', function (User $user) {
            return $user->hasRole(RoleEnum::SUPER_ADMIN);
        });

        Relation::morphMap([
            'user' => User::class,
            'post' => Post::class,
            'comment' => Comment::class,
        ]);
    }
}
