<?php

declare(strict_types=1);

namespace Tests\Feature\Livewire\Dashboard;

use App\Livewire\Dashboard\Saved\SavedIndex;
use App\Models\Post;
use App\Models\User;
use App\Enums\RoleEnum;
use App\Models\SavedPost;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SavedIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_only_sees_their_own_saved_posts()
    {
        $admin = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
        $user = User::factory()->create();
        $post = Post::factory()->create();

        // User saves the post
        $user->savedPosts()->attach($post->id);

        $this->actingAs($admin);

        // Admin should NOT see the user's post anymore
        Livewire::test(SavedIndex::class)
            ->assertDontSee($post->title);
    }

    /** @test */
    public function admin_can_move_their_own_post()
    {
        $admin = User::factory()->create(['role' => RoleEnum::SUPER_ADMIN]);
        $post = Post::factory()->create();
        $admin->savedPosts()->attach($post->id);

        $this->actingAs($admin);

        Livewire::test(SavedIndex::class)
            ->set('postIdBeingMoved', $post->id)
            ->set('targetCollectionId', null)
            ->call('moveToCollection')
            ->assertHasNoErrors()
            ->assertStatus(200);
    }

    /** @test */
    public function removing_post_from_collection_keeps_it_in_saved_items()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create();
        $collection = \App\Models\Collection::factory()->create(['user_id' => $user->id]);

        // Save post into a collection
        $user->savedPosts()->attach($post->id, ['collection_id' => $collection->id]);

        $this->actingAs($user);

        Livewire::test(SavedIndex::class)
            ->set('collection', $collection->slug)
            ->call('confirmUnsave', $post->id)
            ->assertSet('isRemovingFromCollection', true)
            ->call('unsave')
            ->assertHasNoErrors();

        // Verify it still exists in saved_posts but collection_id is null
        $this->assertDatabaseHas('saved_posts', [
            'user_id' => $user->id,
            'post_id' => $post->id,
            'collection_id' => null,
        ]);
    }
}
