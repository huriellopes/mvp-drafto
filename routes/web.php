<?php

declare(strict_types=1);

use App\Http\Controllers\EditorAttachmentController;
use App\Http\Controllers\EmailPreferencesController;
use App\Http\Controllers\Newsletter\UnsubscribeController;
use App\Http\Controllers\Newsletter\VerifySubscriberController;
use App\Http\Controllers\Public\AnalyticsController;
use App\Http\Controllers\Public\PostQrCodeController;
use App\Http\Controllers\Public\ProfileBadgeController;
use App\Http\Controllers\Public\ProfileQrCodeController;
use App\Http\Controllers\ShortLinkController;
use App\Http\Controllers\TemporaryFileDownloadController;
use App\Http\Middleware\EnsureUsernameHasAtPrefix;
use App\Livewire\Dashboard\Modules\LinkShortenerDashboard;
use App\Livewire\Dashboard\Support\SupportPage;
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

// Verificação de e-mail: rotas 'verification.verify'/'verification.notice'/
// 'verification.send' já vivem em routes/parts/auth.php (dentro do grupo
// 'auth'). Havia uma duplicata aqui, sem o middleware 'auth' na rota de
// verify — inofensiva hoje só porque o require acima registra a versão
// protegida primeiro e ela "ganha" no match, mas era frágil a qualquer
// reordenação futura. Removida.

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

        Route::get('/download-temporary-file', TemporaryFileDownloadController::class)
            ->name('temporary-file.download');

        // Módulo Encurtador de Links (Escritor)
        Route::livewire('/encurtador', LinkShortenerDashboard::class)
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
Route::post('/editor/attachments', EditorAttachmentController::class)
    ->middleware(['auth'])
    ->name('editor.attachments.store');

Route::post('/analytics/duration', [AnalyticsController::class, 'updateDuration'])
    ->name('analytics.duration');

Route::get('/health-check', HealthCheckJsonResultsController::class);

Route::livewire('/@{username}', 'public.profile.show-profile')
    ->middleware(['throttle:public-content', 'track.profile', EnsureUsernameHasAtPrefix::class])
    ->name('profile.show')
    ->where('username', '[a-z0-9._]+');

Route::livewire('/@{username}/colecao/{collection}', 'public.profile.show-profile-collection')
    ->middleware(['throttle:public-content'])
    ->name('profile.collection')
    ->where('username', '[a-z0-9._]+')
    ->where('collection', '[a-z0-9._-]+');

Route::livewire('/posts/{slug}', 'public.posts.show-post')
    ->middleware(['throttle:public-content', 'track.post'])
    ->name('posts.show');

Route::get('/badge/@{username}', [ProfileBadgeController::class, 'show'])
    ->name('public.profile.badge')
    ->middleware(['throttle:public-content', 'allow.iframe']);

Route::get('/@{username}/qrcode', [ProfileQrCodeController::class, 'download'])
    ->name('public.profile.qrcode')
    ->middleware(['throttle:public-content'])
    ->where('username', '[a-z0-9._]+');

Route::get('/posts/{slug}/qrcode', [PostQrCodeController::class, 'download'])
    ->name('public.posts.qrcode')
    ->middleware(['throttle:public-content']);

Route::get('/newsletter/verify', VerifySubscriberController::class)
    ->name('newsletter.verify');

Route::get('/newsletter/unsubscribe/{email}', UnsubscribeController::class)
    ->name('newsletter.unsubscribe')
    ->middleware('signed');

Route::get('/email/preferencias/{user}/{type}/cancelar', [EmailPreferencesController::class, 'unsubscribe'])
    ->name('email.preferences.unsubscribe')
    ->middleware('signed');

Route::get('/s/{code}', ShortLinkController::class)
    ->middleware(['throttle:public-content'])
    ->name('shortlink.redirect');
