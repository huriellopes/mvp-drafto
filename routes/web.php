<?php

declare(strict_types=1);

use App\Actions\Auth\VerifyEmailAction;
use App\Http\Controllers\Newsletter\UnsubscribeController;
use App\Http\Controllers\Newsletter\VerifySubscriberController;
use App\Http\Controllers\Public\AnalyticsController;
use App\Http\Controllers\Public\ProfileBadgeController;
use App\Http\Controllers\TrixAttachmentController;
use App\Http\Middleware\EnsureUsernameHasAtPrefix;
use App\Livewire\Dashboard\Support\SupportPage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\Health\Http\Controllers\HealthCheckJsonResultsController;

/*
|--------------------------------------------------------------------------
| Rotas Públicas (Visitantes)
|--------------------------------------------------------------------------
*/
Route::group([], base_path('routes/parts/public.php'));

/*
|--------------------------------------------------------------------------
| Autenticação & Verificação
|--------------------------------------------------------------------------
*/
require __DIR__ . '/parts/auth.php';

// Verificação de E-mail (Exigido pelo MustVerifyEmail)
Route::get('/email/verify/{id}/{hash}', function (Request $request) {
    $result = app(VerifyEmailAction::class)->exec($request);

    if ($result) {
        session()->flash('success', 'E-mail verificado com sucesso! Você já pode aproveitar todos os recursos.');
    } else {
        session()->flash('error', 'O link de verificação é inválido ou expirou.');
    }

    return redirect()->route('dashboard.index');
})->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

/*
|--------------------------------------------------------------------------
| Rotas Protegidas (Dashboard Comum)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'must.change.password'])
    ->prefix('dashboard')
    ->name('dashboard.')
    ->group(function () {

        Route::livewire('/', 'dashboard.index')->name('index');

        // Senha Obrigatória
        Route::livewire('/alterar-senha', 'dashboard.auth.force-change-password')
            ->name('force-password-change');

        // Perfil e Conta (Base para todos)
        Route::livewire('/perfil/editar', 'dashboard.profile.edit-profile')
            ->middleware(['module:profile', 'module.access:profile'])
            ->name('profile');
        Route::livewire('/perfil/cracha', 'dashboard.profile.profile-badge-generator')
            ->name('profile.badge');
        Route::livewire('/conta', 'dashboard.settings.account-settings')
            ->middleware('module:account')
            ->name('account');

        Route::livewire('/suporte', SupportPage::class)
            ->middleware(['module:support'])
            ->name('support');

        Route::get('/download-temporary-file', \App\Http\Controllers\TemporaryFileDownloadController::class)
            ->name('temporary-file.download');

        // Módulo Encurtador de Links (Escritor)
        Route::livewire('/encurtador', \App\Livewire\Dashboard\Modules\LinkShortenerDashboard::class)
            ->middleware(['module:link_shortener', 'module.access:link_shortener'])
            ->name('short-links.index');

        // Módulos Administrativos
        require base_path('routes/parts/admin-routes.php');

        // Área do Escritor e Leitor
        Route::name('')->group(function () {
            require base_path('routes/parts/writer.php');
            require base_path('routes/parts/reader.php');
        });
    });

/*
|--------------------------------------------------------------------------
| Recursos de Sistema & Públicos Específicos
|--------------------------------------------------------------------------
*/
Route::post('/trix/attachments', TrixAttachmentController::class)
    ->middleware(['auth'])
    ->name('trix.attachments.store');

Route::post('/analytics/duration', [AnalyticsController::class, 'updateDuration'])
    ->name('analytics.duration');

Route::get('/health-check', HealthCheckJsonResultsController::class);

Route::livewire('/@{username}', 'public.profile.show-profile')
    ->middleware(['track.profile', EnsureUsernameHasAtPrefix::class])
    ->name('profile.show')
    ->where('username', '[a-z0-9._]+');

Route::livewire('/posts/{slug}', 'public.posts.show-post')
    ->middleware(['track.post'])
    ->name('posts.show');

Route::get('/badge/@{username}', [ProfileBadgeController::class, 'show'])
    ->name('public.profile.badge')
    ->middleware('allow.iframe');

Route::get('/newsletter/verify', VerifySubscriberController::class)
    ->name('newsletter.verify');

Route::get('/newsletter/unsubscribe/{email}', UnsubscribeController::class)
    ->name('newsletter.unsubscribe')
    ->middleware('signed');

Route::get('/s/{code}', \App\Http\Controllers\ShortLinkController::class)
    ->name('shortlink.redirect');
