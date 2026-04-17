<?php

declare(strict_types=1);

use App\Actions\Auth\VerifyEmailAction;
use App\Http\Controllers\Newsletter\UnsubscribeController;
use App\Http\Controllers\Public\ProfileBadgeController;
use App\Http\Controllers\TrixAttachmentController;
use App\Http\Middleware\EnsureUsernameHasAtPrefix;
use App\Livewire\Public\Site\ExplorePosts;
use App\Livewire\Public\Site\ExploreWriters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rotas Públicas (Visitantes)
|--------------------------------------------------------------------------
*/
Route::livewire('/', 'public.site.home')
    ->name('home');
Route::get('/sitemap.xml', [App\Http\Controllers\Public\SitemapController::class, 'index'])->name('sitemap');
Route::get('/artigos', ExplorePosts::class)
    ->name('posts.explore');
Route::get('/escritores', ExploreWriters::class)
    ->name('writers.explore');

/*
|--------------------------------------------------------------------------
| Autenticação & Verificação
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::livewire('/login', 'auth.login')->name('login');
    Route::livewire('/register', 'auth.register')->name('register');
    Route::livewire('/forgot-password', 'auth.forgot-password')->name('password.request');
    Route::livewire('/reset-password/{token}', 'auth.reset-password')->name('password.reset');
});

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
    return view('auth.verify-email'); // Você pode criar esta view simples depois se quiser
})->middleware('auth')->name('verification.notice');

/*
|--------------------------------------------------------------------------
| Rotas Protegidas (Dashboard Comum)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'check.verification.interval'])->prefix('dashboard')->name('dashboard.')->group(function () {

    Route::livewire('/', 'dashboard.index')->name('index');

    // Perfil e Conta (Base para todos)
    Route::livewire('/perfil/editar', 'dashboard.profile.edit-profile')
        ->middleware(['module:profile', 'module.access:profile'])
        ->name('profile');
    Route::livewire('/perfil/cracha', 'dashboard.profile.profile-badge-generator')
        ->name('profile.badge');
    Route::livewire('/conta', 'dashboard.settings.account-settings')
        ->middleware('module:account')->name('account');

    /*
    |--- Área do Escritor (Writer/Admin) ---
    */
    Route::prefix('posts')
        ->name('posts.')
        ->group(function () {
            Route::livewire('/meus-conteudos', 'dashboard.posts.index-posts')
                ->middleware(['module:my_posts', 'module.access:my_posts'])
                ->name('index');
            Route::livewire('/rascunhos', 'dashboard.posts.draft-index')
                ->middleware('module:draft')
                ->name('draft');
            Route::livewire('/create', 'dashboard.posts.manage-post')
                ->middleware('module:my_posts')
                ->name('create');
            Route::livewire('/{post}/edit', 'dashboard.posts.manage-post')
                ->middleware('module:my_posts')
                ->name('edit');
            Route::livewire('/categorias', 'dashboard.categories.category-index')
                ->middleware('module:categories')
                ->name('categories.index');
    });

    /*
    |--- Área do Leitor (Reader) ---
    */
    Route::livewire('/salvos', 'dashboard.saved.saved-index')->middleware('module:saved_post')->name('posts.saved');
    Route::livewire('/comentarios', 'dashboard.comments.comment-index')->middleware('module:comments')->name('comments');
    Route::livewire('/comunidade', 'dashboard.follows.follow-index')->middleware('module:follows')->name('follows');

    Route::prefix('faturamento')
        ->name('billing.')
        ->middleware([
            'module:subscriptions',
            'module.access:subscriptions'
        ])
        ->group(function () {
            Route::livewire('/planos', 'dashboard.billing.plans-index')->name('plans');

            Route::get('/portal', function (Request $request) {
                return $request->user()->redirectToBillingPortal(route('dashboard.account'));
            })->name('portal');
    });

    /*
    |--- Gestão Master (Admin Only) ---
    */
    require __DIR__ . '/parts/admin-routes.php';
});

/*
|--------------------------------------------------------------------------
| Recursos de Sistema & Públicos Específicos
|--------------------------------------------------------------------------
*/
Route::post('/trix/attachments', TrixAttachmentController::class)
    ->middleware('auth')
    ->name('trix.attachments.store');

Route::livewire('/@{username}', 'public.profile.show-profile')->name('profile.show')
    ->middleware(EnsureUsernameHasAtPrefix::class)->where('username', '[a-z0-9._]+');

Route::livewire('/posts/{slug}', 'public.posts.show-post')
    ->middleware(['track.post'])
    ->name('posts.show');

Route::get('/badge/@{username}', [ProfileBadgeController::class, 'show'])
    ->name('public.profile.badge');

Route::get('/newsletter/unsubscribe/{email}', UnsubscribeController::class)->name('newsletter.unsubscribe')->middleware('signed');

Route::view('/diretrizes', 'public.pages.guidelines')->name('pages.guidelines');
