<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\PostCategory;
use App\Models\User;
use App\Policies\PostCategoryPolicy;

beforeEach(function (): void {
    $this->policy = new PostCategoryPolicy;
});

it('before grants everything to super admin', function (): void {
    expect($this->policy->before(User::factory()->superAdmin()->create(), 'update'))->toBeTrue();
});

it('before returns null for non admin', function (): void {
    expect($this->policy->before(User::factory()->writer()->create(), 'delete'))->toBeNull();
});

it('update allows active owner of personal category', function (): void {
    $owner = User::factory()->active()->create();
    $category = PostCategory::factory()->create(['user_id' => $owner->id]);

    expect($this->policy->update($owner, $category))->toBeTrue();
});

it('update denies on global category', function (): void {
    $user = User::factory()->active()->create();
    $category = PostCategory::factory()->create(['user_id' => null]);

    expect($this->policy->update($user, $category))->toBeFalse();
});

it('update denies non owner', function (): void {
    $owner = User::factory()->active()->create();
    $category = PostCategory::factory()->create(['user_id' => $owner->id]);

    expect($this->policy->update(User::factory()->active()->create(), $category))->toBeFalse();
});

it('update denies suspended owner', function (): void {
    $owner = User::factory()->suspended()->create();
    $category = PostCategory::factory()->create(['user_id' => $owner->id]);

    expect($this->policy->update($owner, $category))->toBeFalse();
});

it('delete allows active owner of personal category', function (): void {
    $owner = User::factory()->active()->create();
    $category = PostCategory::factory()->create(['user_id' => $owner->id]);

    expect($this->policy->delete($owner, $category))->toBeTrue();
});

it('delete denies on global category', function (): void {
    $user = User::factory()->active()->create();
    $category = PostCategory::factory()->create(['user_id' => null]);

    expect($this->policy->delete($user, $category))->toBeFalse();
});

it('delete denies non owner', function (): void {
    $owner = User::factory()->active()->create();
    $category = PostCategory::factory()->create(['user_id' => $owner->id]);

    expect($this->policy->delete(User::factory()->active()->create(), $category))->toBeFalse();
});
