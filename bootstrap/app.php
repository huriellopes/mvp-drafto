<?php

declare(strict_types=1);

use App\Exceptions\BrandedErrorRenderer;
use App\Exceptions\TelegramErrorReporter;
use App\Http\Middleware\AllowIframeMiddleware;
use App\Http\Middleware\CheckBanned;
use App\Http\Middleware\CheckEmailVerificationInterval;
use App\Http\Middleware\CheckModuleAccess;
use App\Http\Middleware\CheckModuleStatus;
use App\Http\Middleware\EnsurePasswordIsChanged;
use App\Http\Middleware\EnsureUsernameHasAtPrefix;
use App\Http\Middleware\LogContextMiddleware;
use App\Http\Middleware\TrackPostView;
use App\Http\Middleware\TrackProfileView;
use App\Http\Middleware\TrackSiteView;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'stripe/*',
        ]);

        // Cookie de consentimento (LGPD) é definido/lido pelo JS do banner, logo não é criptografado.
        $middleware->encryptCookies(except: [
            'drafto_consent',
        ]);

        $middleware->alias([
            'check.verification.interval' => CheckEmailVerificationInterval::class,
            'check.banned' => CheckBanned::class,
            'username.prefix' => EnsureUsernameHasAtPrefix::class,
            'track.post' => TrackPostView::class,
            'track.profile' => TrackProfileView::class,
            'module' => CheckModuleStatus::class,
            'can' => Authorize::class,
            'module.access' => CheckModuleAccess::class,
            'allow.iframe' => AllowIframeMiddleware::class,
            'must.change.password' => EnsurePasswordIsChanged::class,
        ]);

        $middleware->web(append: [
            LogContextMiddleware::class,
            CheckBanned::class,
            TrackSiteView::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // O Laravel ignora erros HTTP (4xx) por padrão; reativamos para alertar TUDO.
        $exceptions->stopIgnoring(HttpException::class);

        // Lógica de reporte/render isolada em classes de responsabilidade única.
        $exceptions->report(fn (Throwable $e) => app(TelegramErrorReporter::class)->report($e));
        $exceptions->render(fn (Throwable $e, Request $request) => app(BrandedErrorRenderer::class)->render($e, $request));
    })->create();
