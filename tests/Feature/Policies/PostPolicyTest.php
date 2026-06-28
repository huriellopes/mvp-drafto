<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\Post;
use App\Models\User;
use App\Policies\PostPolicy;

beforeEach(function (): void {
    $this->policy = new PostPolicy();
});

it('before grants everything to super admin', function (): void {
    $admin = User::factory()->superAdmin()->create();

    expect($this->policy->before($admin, 'forceDelete'))->toBeTrue();
});

it('before returns null for non admin', function (): void {
    $user = User::factory()->writer()->create();

    expect($this->policy->before($user, 'update'))->toBeNull();
});

it('viewAny always allows even guests', function (): void {
    expect($this->policy->viewAny(null))->toBeTrue();
    expect($this->policy->viewAny(User::factory()->create()))->toBeTrue();
});

it('view allows guest on published public post', function (): void {
    $post = Post::factory()->published()->public()->create();

    expect($this->policy->view(null, $post))->toBeTrue();
});

it('view denies guest on non public post', function (): void {
    $post = Post::factory()->draft()->create();

    expect($this->policy->view(null, $post))->toBeFalse();
});

it('view allows admin on any post', function (): void {
    $admin = User::factory()->superAdmin()->create();
    $post = Post::factory()->draft()->create();

    expect($this->policy->view($admin, $post))->toBeTrue();
});

it('view allows author on own draft', function (): void {
    $author = User::factory()->writer()->create();
    $post = Post::factory()->draft()->forAuthor($author)->create();

    expect($this->policy->view($author, $post))->toBeTrue();
});

it('view allows logged user on published unlisted post', function (): void {
    $user = User::factory()->create();
    $post = Post::factory()->published()->unlisted()->create();

    expect($this->policy->view($user, $post))->toBeTrue();
});

it('view allows follower on followers only post', function (): void {
    $author = User::factory()->writer()->create();
    $follower = User::factory()->create();
    $follower->following()->attach($author->id);

    $post = Post::factory()->published()->followersOnly()->forAuthor($author)->create();

    expect($this->policy->view($follower, $post))->toBeTrue();
});

it('view denies non follower on followers only post', function (): void {
    $author = User::factory()->writer()->create();
    $stranger = User::factory()->create();

    $post = Post::factory()->published()->followersOnly()->forAuthor($author)->create();

    expect($this->policy->view($stranger, $post))->toBeFalse();
});

it('viewContent delegates to view', function (): void {
    $post = Post::factory()->published()->public()->create();

    expect($this->policy->viewContent(null, $post))->toBeTrue();
});

it('create allows active writer reader and super admin', function (): void {
    expect($this->policy->create(User::factory()->writer()->active()->create()))->toBeTrue();
    expect($this->policy->create(User::factory()->reader()->active()->create()))->toBeTrue();
    expect($this->policy->create(User::factory()->superAdmin()->active()->create()))->toBeTrue();
});

it('create denies suspended user', function (): void {
    $user = User::factory()->writer()->suspended()->create();

    expect($this->policy->create($user))->toBeFalse();
});

it('update allows admin and author but denies stranger', function (): void {
    $author = User::factory()->writer()->create();
    $post = Post::factory()->forAuthor($author)->create();

    expect($this->policy->update($author, $post))->toBeTrue();
    expect($this->policy->update(User::factory()->superAdmin()->create(), $post))->toBeTrue();
    expect($this->policy->update(User::factory()->writer()->create(), $post))->toBeFalse();
});

it('update denies inactive author', function (): void {
    $author = User::factory()->writer()->suspended()->create();
    $post = Post::factory()->forAuthor($author)->create();

    expect($this->policy->update($author, $post))->toBeFalse();
});

it('delete follows ownership and active rules', function (): void {
    $author = User::factory()->writer()->create();
    $post = Post::factory()->forAuthor($author)->create();

    expect($this->policy->delete($author, $post))->toBeTrue();
    expect($this->policy->delete(User::factory()->writer()->create(), $post))->toBeFalse();
});

it('restore follows ownership and active rules', function (): void {
    $author = User::factory()->writer()->create();
    $post = Post::factory()->forAuthor($author)->create();

    expect($this->policy->restore($author, $post))->toBeTrue();
    expect($this->policy->restore(User::factory()->writer()->suspended()->create(), $post))->toBeFalse();
});

it('forceDelete only allows admin', function (): void {
    $post = Post::factory()->create();

    expect($this->policy->forceDelete(User::factory()->superAdmin()->create(), $post))->toBeTrue();
    expect($this->policy->forceDelete(User::factory()->writer()->create(), $post))->toBeFalse();
});

it('publish follows ownership and active rules', function (): void {
    $author = User::factory()->writer()->create();
    $post = Post::factory()->forAuthor($author)->create();

    expect($this->policy->publish($author, $post))->toBeTrue();
    expect($this->policy->publish(User::factory()->writer()->create(), $post))->toBeFalse();
});

it('unpublish follows ownership and active rules', function (): void {
    $author = User::factory()->writer()->create();
    $post = Post::factory()->forAuthor($author)->create();

    expect($this->policy->unpublish($author, $post))->toBeTrue();
    expect($this->policy->unpublish(User::factory()->writer()->suspended()->create(), $post))->toBeFalse();
});

it('feature only allows admin', function (): void {
    $post = Post::factory()->create();

    expect($this->policy->feature(User::factory()->superAdmin()->create(), $post))->toBeTrue();
    expect($this->policy->feature(User::factory()->writer()->create(), $post))->toBeFalse();
});
