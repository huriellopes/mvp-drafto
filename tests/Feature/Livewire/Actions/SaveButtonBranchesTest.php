<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Actions;

use App\Livewire\Actions\SaveButton;
use App\Models\Post;
use App\Models\User;
use Livewire\Livewire;

it('redirects a guest to login when toggling save', function () {
    $post = Post::factory()->published()->create();

    Livewire::test(SaveButton::class, ['post' => $post])
        ->call('toggle')
        ->assertRedirect(route('login'));
});

it('saves and unsaves a post for an authenticated user', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();

    $component = Livewire::actingAs($user)
        ->test(SaveButton::class, ['post' => $post])
        ->assertViewHas('isSaved', false)
        ->call('toggle');

    expect($user->savedPosts()->where('post_id', $post->id)->exists())->toBeTrue();

    $component->call('toggle');

    expect($user->savedPosts()->where('post_id', $post->id)->exists())->toBeFalse();
});

it('reflects a previously saved post as saved on render', function () {
    $user = User::factory()->create();
    $post = Post::factory()->published()->create();
    $user->savedPosts()->attach($post->id);

    Livewire::actingAs($user)
        ->test(SaveButton::class, ['post' => $post])
        ->assertViewHas('isSaved', true);
});
