<?php

declare(strict_types=1);

use App\Http\Controllers\Public\SitemapController;
use App\Livewire\Public\Site\ExplorePosts;
use App\Livewire\Public\Site\ExploreWriters;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'public.site.home')->name('home');
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/artigos', ExplorePosts::class)->name('posts.explore');
Route::get('/escritores', ExploreWriters::class)->name('writers.explore');
Route::view('/diretrizes', 'public.pages.guidelines')->name('pages.guidelines');
Route::view('/privacidade', 'public.pages.privacy')->name('pages.privacy');
Route::view('/termos', 'public.pages.terms')->name('pages.terms');

Route::get('/.well-known/security.txt', function () {
    $body = implode("\n", [
        'Contact: mailto:' . config('support.email'),
        'Expires: ' . now()->addYear()->toIso8601ZuluString(),
        'Preferred-Languages: pt-BR, en',
        'Canonical: ' . url('/.well-known/security.txt'),
    ]) . "\n";

    return response($body)->header('Content-Type', 'text/plain; charset=utf-8');
})->name('security-txt');
