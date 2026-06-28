<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Widgets;

use App\Livewire\Dashboard\Widgets\SuggestedWriters;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
});

it('returns an empty collection when there are no suggestions', function () {
    $user = User::factory()->reader()->withProfile()->create();

    $this->actingAs($user);

    $suggestions = Livewire::test(SuggestedWriters::class)->get('suggestions');

    expect($suggestions)->toBeEmpty();
});

it('suggests writers in categories the user interacted with and does not follow', function () {
    $category = PostCategory::factory()->create();

    $writer = User::factory()->writer()->withProfile()->create();
    Post::factory()->published()->create([
        'user_id' => $writer->id,
        'category_id' => $category->id,
    ]);

    $reader = User::factory()->reader()->withProfile()->create();

    // Reader likes a post in that category, establishing category affinity.
    $likedPost = Post::factory()->published()->create([
        'user_id' => $writer->id,
        'category_id' => $category->id,
    ]);
    $reader->likedPosts()->attach($likedPost->id);

    $this->actingAs($reader);

    $suggestions = Livewire::test(SuggestedWriters::class)->get('suggestions');

    expect($suggestions->pluck('id'))->toContain($writer->id);
});

it('renders the suggested writers widget', function () {
    $user = User::factory()->reader()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(SuggestedWriters::class)->assertStatus(200);
});
