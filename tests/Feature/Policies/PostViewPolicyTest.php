<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\Post;
use App\Models\User;
use App\Policies\PostViewPolicy;

beforeEach(function (): void {
    $this->policy = new PostViewPolicy();
});

it('before grants everything to super admin', function (): void {
    expect($this->policy->before(User::factory()->superAdmin()->create(), 'delete'))->toBeTrue();
});

it('before returns null for non admin', function (): void {
    expect($this->policy->before(User::factory()->writer()->create(), 'viewStats'))->toBeNull();
});

it('viewAny allows admin and denies non admin', function (): void {
    expect($this->policy->viewAny(User::factory()->superAdmin()->create()))->toBeTrue();
    expect($this->policy->viewAny(User::factory()->writer()->create()))->toBeFalse();
});

it('viewStats allows post owner', function (): void {
    $owner = User::factory()->writer()->create();
    $post = Post::factory()->forAuthor($owner)->create();

    expect($this->policy->viewStats($owner, $post))->toBeTrue();
});

it('viewStats denies non owner', function (): void {
    $post = Post::factory()->create();

    expect($this->policy->viewStats(User::factory()->writer()->create(), $post))->toBeFalse();
});

it('delete allows admin and denies non admin', function (): void {
    expect($this->policy->delete(User::factory()->superAdmin()->create()))->toBeTrue();
    expect($this->policy->delete(User::factory()->writer()->create()))->toBeFalse();
});
