<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard\Widgets;

use App\Livewire\Dashboard\Widgets\RecentActivity;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;

beforeEach(function () {
    Cache::flush();
});

it('returns the writer own recent posts', function () {
    $writer = User::factory()->writer()->withProfile()->create();
    $post = Post::factory()->published()->create(['user_id' => $writer->id]);

    $this->actingAs($writer);

    $items = Livewire::test(RecentActivity::class)->get('items');

    expect($items)->toHaveCount(1)
        ->and($items->first()->id)->toBe($post->id);
});

it('returns latest posts for an admin', function () {
    $admin = User::factory()->superAdmin()->withProfile()->create();
    Post::factory()->count(3)->published()->create();

    $this->actingAs($admin);

    $items = Livewire::test(RecentActivity::class)->get('items');

    expect($items)->toHaveCount(3);
});

it('returns posts the reader interacted with', function () {
    $reader = User::factory()->reader()->withProfile()->create();
    $post = Post::factory()->published()->create();
    $reader->savedPosts()->attach($post->id);

    $this->actingAs($reader);

    $items = Livewire::test(RecentActivity::class)->get('items');

    expect($items->pluck('id'))->toContain($post->id);
});

it('returns an empty collection when there is no activity', function () {
    $reader = User::factory()->reader()->withProfile()->create();

    $this->actingAs($reader);

    $items = Livewire::test(RecentActivity::class)->get('items');

    expect($items)->toBeEmpty();
});

it('renders the recent activity widget', function () {
    $user = User::factory()->writer()->withProfile()->create();

    $this->actingAs($user);

    Livewire::test(RecentActivity::class)->assertStatus(200);
});
