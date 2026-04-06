<?php

declare(strict_types=1);

use App\Http\Controllers\TrixAttachmentController;
use App\Http\Controllers\Newsletter\UnsubscribeController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'public.site.home')->name('home');

Route::get('/artigos', \App\Livewire\Public\Site\ExplorePosts::class)->name('posts.explore');
Route::get('/escritores', \App\Livewire\Public\Site\ExploreWriters::class)->name('writers.explore');

/**
 * Rotas de Autenticação (Guest)
 */
Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'auth.login')->name('login');
    Route::livewire('/register', 'auth.register')->name('register');
    Route::livewire('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::livewire('/reset-password/{token}', 'auth.reset-password')->name('password.reset');
});

/**
 * Rotas Protegidas (Auth)
 */
Route::middleware(['auth'])->group(function () {

    // Verificação de E-mail
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('dashboard.index')->with('success', 'E-mail confirmado!');
    })->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

    Route::livewire('/email/verify', 'auth.verify-email-notice')->name('verification.notice');

    // Dashboard Group
    Route::middleware(['check.verification.interval'])
        ->name('dashboard.')
        ->group(function () {

            Route::livewire('/dashboard', 'dashboard.index')->name('index');

            // Admin: Newsletter
            Route::prefix('newsletter')->name('newsletter.')->group(function () {
                Route::livewire('/', 'dashboard.admin.newsletter.newsletter-index')->name('index');
            });

            // Admin: Reports
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::livewire('/', 'dashboard.admin.reports.report-index')->name('index');
            });

            // Admin: Users
            Route::prefix('users')->name('users.')->group(function () {
                Route::livewire('/', 'dashboard.admin.users.user-index')->name('index');
            });

            Route::livewire('/views', 'dashboard.admin.post-views.post-view-index')->name('posts.views');

            // Conteúdo (Posts, Rascunhos, Salvos)
            Route::prefix('posts')->name('posts.')->group(function () {
                Route::livewire('/meus-conteudos', 'dashboard.posts.index-posts')->name('index');
                Route::livewire('/rascunhos', 'dashboard.posts.draft-index')->name('draft');
                Route::livewire('/create', 'dashboard.posts.manage-post')->name('create');
                Route::livewire('/{post}/edit', 'dashboard.posts.manage-post')->name('edit');
                Route::livewire('/salvos', 'dashboard.saved.saved-index')->name('saved');
            });

            // Social: Comentários e Seguidores
            Route::livewire('/comentários', 'dashboard.comments.comment-index')->name('comments');
            Route::livewire('/comunidade', 'dashboard.follows.follow-index')->name('follows');

            // Perfil e Configurações
            Route::livewire('/perfil/editar', 'dashboard.profile.edit-profile')->name('profile');
            Route::livewire('/conta', 'dashboard.settings.account-settings')->name('account');
        });
});

/**
 * Uploads Trix
 */
Route::post('/trix/attachments', TrixAttachmentController::class)
    ->middleware('auth')
    ->name('trix.attachments.store');

/**
 * Rotas Públicas (Escritores e Posts)
 */
Route::livewire('/@{username}', 'public.profile.show-profile')
    ->name('profile.show')
    ->middleware(\App\Http\Middleware\EnsureUsernameHasAtPrefix::class)
    ->where('username', '[a-z0-9._]+');

Route::prefix('posts')->name('posts.')->group(function () {
    Route::livewire('/{slug}', 'public.posts.show-post')
        ->middleware(\App\Http\Middleware\TrackPostView::class)
        ->name('show');
});

/**
 * Newsletter Guest
 */
Route::get('/newsletter/unsubscribe/{email}', UnsubscribeController::class)
    ->name('newsletter.unsubscribe')
    ->middleware('signed');

// Root Redirect
//Route::redirect('/', '/dashboard');
