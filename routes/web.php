<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TrixAttachmentController;
use App\Http\Controllers\Newsletter\UnsubscribeController;

/*
|--------------------------------------------------------------------------
| Rotas Públicas (Visitantes)
|--------------------------------------------------------------------------
*/
Route::livewire('/', 'public.site.home')
    ->name('home');
Route::get('/artigos', \App\Livewire\Public\Site\ExplorePosts::class)
    ->name('posts.explore');
Route::get('/escritores', \App\Livewire\Public\Site\ExploreWriters::class)
    ->name('writers.explore');

Route::middleware('guest')
    ->group(function () {
        Route::livewire('/login', 'auth.login')
            ->name('login');
        Route::livewire('/register', 'auth.register')
            ->name('register');

        Route::livewire('/forgot-password', 'auth.forgot-password')->name('password.request');
        Route::livewire('/reset-password/{token}', 'auth.reset-password')->name('password.reset');
});

/*
|--------------------------------------------------------------------------
| Rotas Protegidas (Dashboard Comum)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check.verification.interval'])->prefix('dashboard')->name('dashboard.')->group(function () {

    Route::livewire('/', 'dashboard.index')->name('index');

    // Perfil e Conta (Base para todos)
    Route::livewire('/perfil/editar', 'dashboard.profile.edit-profile')->middleware('module:profile')->name('profile');
    Route::livewire('/conta', 'dashboard.settings.account-settings')->middleware('module:account')->name('account');

    /*
    |--- Área do Escritor (Writer/Admin) ---
    */
    Route::prefix('posts')->name('posts.')->group(function () {
        Route::livewire('/meus-conteudos', 'dashboard.posts.index-posts')->middleware('module:my_posts')->name('index');
        Route::livewire('/rascunhos', 'dashboard.posts.draft-index')->middleware('module:draft')->name('draft');
        Route::livewire('/create', 'dashboard.posts.manage-post')->name('create');
        Route::livewire('/{post}/edit', 'dashboard.posts.manage-post')->name('edit');
    });

    /*
    |--- Área do Leitor (Reader) ---
    */
    Route::livewire('/salvos', 'dashboard.saved.saved-index')->middleware('module:saved_post')->name('posts.saved');
    Route::livewire('/comentarios', 'dashboard.comments.comment-index')->middleware('module:comments')->name('comments');
    Route::livewire('/comunidade', 'dashboard.follows.follow-index')->middleware('module:follows')->name('follows');

    /*
    |--- Gestão Master (Admin Only) ---
    */
    Route::middleware(['can:admin'])->group(function () {
        Route::prefix('admin')
            ->name('admin.')
            ->group(function () {
                Route::livewire('/newsletter', 'dashboard.admin.newsletter.newsletter-index')->name('newsletter.index');
                Route::livewire('/reports', 'dashboard.admin.reports.report-index')->name('reports.index');
                Route::livewire('/users', 'dashboard.admin.users.user-index')->name('users.index');
                Route::livewire('/modules', 'dashboard.admin.modules.module-index')->name('modules.index');

                Route::livewire('/views', 'dashboard.admin.post-views.post-view-index')->name('posts.views');
        });
    });
});

/*
|--------------------------------------------------------------------------
| Recursos de Sistema & Públicos Específicos
|--------------------------------------------------------------------------
*/
Route::post('/trix/attachments', TrixAttachmentController::class)->middleware('auth')->name('trix.attachments.store');

Route::livewire('/@{username}', 'public.profile.show-profile')->name('profile.show')
    ->middleware(\App\Http\Middleware\EnsureUsernameHasAtPrefix::class)->where('username', '[a-z0-9._]+');

Route::livewire('/posts/{slug}', 'public.posts.show-post')->middleware(\App\Http\Middleware\TrackPostView::class)->name('posts.show');

Route::get('/newsletter/unsubscribe/{email}', UnsubscribeController::class)->name('newsletter.unsubscribe')->middleware('signed');
