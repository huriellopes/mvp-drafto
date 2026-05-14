<?php

declare(strict_types=1);

use App\Http\Middleware\CheckBanned;
use App\Http\Middleware\CheckEmailVerificationInterval;
use App\Http\Middleware\CheckModuleAccess;
use App\Http\Middleware\CheckModuleStatus;
use App\Http\Middleware\EnsureUsernameHasAtPrefix;
use App\Http\Middleware\LogContextMiddleware;
use App\Http\Middleware\TrackPostView;
use App\Http\Middleware\TrackProfileView;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'check.verification.interval' => CheckEmailVerificationInterval::class,
            'check.banned' => CheckBanned::class,
            'username.prefix' => EnsureUsernameHasAtPrefix::class,
            'track.post' => TrackPostView::class,
            'track.profile' => TrackProfileView::class,
            'module' => CheckModuleStatus::class,
            'can' => Authorize::class,
            'module.access' => CheckModuleAccess::class,
        ]);

        $middleware->web(append: [
            LogContextMiddleware::class,
            CheckEmailVerificationInterval::class,
            CheckBanned::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
