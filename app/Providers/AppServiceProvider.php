<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\ModuleEnum;
use App\Enums\RoleEnum;
use App\Models\Comment;
use App\Models\Follower;
use App\Models\Module;
use App\Models\Post;
use App\Models\PostView;
use App\Models\Profile;
use App\Models\Report;
use App\Models\User;
use App\Policies\CommentPolicy;
use App\Policies\FollowerPolicy;
use App\Policies\PostPolicy;
use App\Policies\PostViewPolicy;
use App\Policies\ProfilePolicy;
use App\Policies\ReportPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

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

        Blade::if('module', function (mixed $slug) {

            if (auth()->check() && auth()->user()->hasRole(RoleEnum::SUPER_ADMIN)) {
                return true;
            }

            $module = $slug instanceof ModuleEnum
                ? $slug
                : ModuleEnum::tryFrom((string) $slug);

            return $module && Module::isEnabled($module);
        });

        Gate::define('admin', function (User $user) {
            return $user->hasRole(RoleEnum::SUPER_ADMIN);
        });

        Cashier::useCustomerModel(User::class);
    }
}
