<?php

declare(strict_types=1);

use App\Http\Controllers\Public\SitemapController;
use App\Livewire\Public\Site\ExplorePosts;
use App\Livewire\Public\Site\ExploreWriters;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'public.site.home')->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/artigos', ExplorePosts::class)->middleware('throttle:public-content')->name('posts.explore');
Route::get('/escritores', ExploreWriters::class)->middleware('throttle:public-content')->name('writers.explore');
Route::view('/diretrizes', 'public.pages.guidelines')->name('pages.guidelines');
Route::view('/privacidade', 'public.pages.privacy')->name('pages.privacy');
Route::view('/termos', 'public.pages.terms')->name('pages.terms');

Route::get('/robots.txt', function () {
    $lines = [
        'User-agent: *',
        'Allow: /',
        // Áreas privadas, fluxos de auth e endpoints sem valor de indexação.
        'Disallow: /dashboard/',
        'Disallow: /login',
        'Disallow: /register',
        'Disallow: /forgot-password',
        'Disallow: /reset-password',
        'Disallow: /logout',
        'Disallow: /email/',
        'Disallow: /newsletter/',
        'Disallow: /s/',
        '',
        'Sitemap: ' . url('/sitemap.xml'),
    ];

    return response(implode("\n", $lines) . "\n")
        ->header('Content-Type', 'text/plain; charset=utf-8');
})->name('robots-txt');

Route::get('/.well-known/security.txt', function () {
    $body = implode("\n", [
        'Contact: mailto:' . config('support.email'),
        'Expires: ' . now()->addYear()->toIso8601ZuluString(),
        'Preferred-Languages: pt-BR, en',
        'Canonical: ' . url('/.well-known/security.txt'),
    ]) . "\n";

    return response($body)->header('Content-Type', 'text/plain; charset=utf-8');
})->name('security-txt');
