<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Forms;

use App\Enums\RoleEnum;
use App\Livewire\Dashboard\Posts\ManagePost;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;

beforeEach(function () {
    File::cleanDirectory(config('purifier.cachePath'));
    $this->user = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
    $this->actingAs($this->user);
    RateLimiter::clear('publish-post:' . $this->user->id);
});

/**
 * Covers the numeric-invalid branch of the category_id rule closure
 * (PostForm lines 64-70): a numeric id that does not resolve to a usable
 * category fails validation.
 */
it('rejects a numeric category id that does not exist', function () {
    Livewire::test(ManagePost::class)
        ->set('form.title', 'Categoria Inexistente')
        ->set('form.content', '<p>Conteúdo</p>')
        ->set('form.category_id', 999999)
        ->call('publish')
        ->assertHasErrors('form.category_id');
});

/**
 * Covers the foreign-owner branch of the same closure (lines 67-70): a category
 * belonging to another user is invalid.
 */
it('rejects a category owned by another user', function () {
    $other = User::factory()->writer()->create();
    $foreign = PostCategory::factory()->create(['user_id' => $other->id]);

    Livewire::test(ManagePost::class)
        ->set('form.title', 'Categoria de Outro')
        ->set('form.content', '<p>Conteúdo</p>')
        ->set('form.category_id', $foreign->id)
        ->call('publish')
        ->assertHasErrors('form.category_id');
});

/**
 * Covers the non-numeric/empty branch of the closure (lines 71-72): a
 * whitespace-only string is treated as a missing category.
 */
it('rejects a blank non-numeric category value', function () {
    Livewire::test(ManagePost::class)
        ->set('form.title', 'Categoria em Branco')
        ->set('form.content', '<p>Conteúdo</p>')
        ->set('form.category_id', '   ')
        ->call('publish')
        ->assertHasErrors('form.category_id');
});
