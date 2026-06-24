<?php

declare(strict_types=1);

use App\Livewire\Public\Profile\ShowProfileCollection;
use App\Models\Post;
use App\Models\PostCollection;
use App\Models\User;
use Livewire\Livewire;

it('shows the published posts of a public collection', function () {
    $writer = User::factory()->writer()->withProfile()->create();
    $collection = PostCollection::factory()->public()->for($writer)->create();

    $published = Post::factory()->published()->for($writer)->create();
    $draft = Post::factory()->for($writer)->create();
    $collection->posts()->attach([$published->id, $draft->id]);

    $username = $writer->profile->username;

    Livewire::test(ShowProfileCollection::class, ['username' => $username, 'collection' => $collection->slug])
        ->assertOk()
        ->assertSee($published->title)
        ->assertDontSee($draft->title);
});

it('returns 404 for a private collection', function () {
    $writer = User::factory()->writer()->withProfile()->create();
    $collection = PostCollection::factory()->for($writer)->create();

    $this->get(route('profile.collection', [
        'username' => $writer->profile->username,
        'collection' => $collection->slug,
    ]))->assertNotFound();
});

it('returns 404 for a collection that does not belong to the user', function () {
    $writer = User::factory()->writer()->withProfile()->create();
    $other = User::factory()->writer()->withProfile()->create();
    $collection = PostCollection::factory()->public()->for($other)->create();

    $this->get(route('profile.collection', [
        'username' => $writer->profile->username,
        'collection' => $collection->slug,
    ]))->assertNotFound();
});
