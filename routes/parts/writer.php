<?php

declare(strict_types=1);

use App\Livewire\Dashboard\Tags\TagsIndex;
use Illuminate\Support\Facades\Route;

Route::prefix('posts')->name('posts.')->group(function () {
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

    Route::livewire('/tags', TagsIndex::class)
        ->middleware('module:tags')
        ->name('tags.index');
});
