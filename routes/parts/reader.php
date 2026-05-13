<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::livewire('/salvos', 'dashboard.saved.saved-index')
    ->middleware('module:saved_post')
    ->name('posts.saved');

Route::livewire('/comentarios', 'dashboard.comments.comment-index')
    ->middleware('module:comments')
    ->name('comments');

Route::livewire('/comunidade', 'dashboard.follows.follow-index')
    ->middleware('module:follows')
    ->name('follows');
