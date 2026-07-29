<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\PostCollection;
use App\Models\User;
use App\Policies\PostCollectionPolicy;

beforeEach(function (): void {
    $this->policy = new PostCollectionPolicy;
});

it('before grants everything to super admin', function (): void {
    expect($this->policy->before(User::factory()->superAdmin()->create(), 'view'))->toBeTrue();
});

it('before returns null for non admin', function (): void {
    expect($this->policy->before(User::factory()->writer()->create(), 'update'))->toBeNull();
});

it('viewAny allows writer and denies reader', function (): void {
    expect($this->policy->viewAny(User::factory()->writer()->create()))->toBeTrue();
    expect($this->policy->viewAny(User::factory()->reader()->create()))->toBeFalse();
});

it('view allows owner only', function (): void {
    $owner = User::factory()->writer()->create();
    $collection = PostCollection::factory()->create(['user_id' => $owner->id]);

    expect($this->policy->view($owner, $collection))->toBeTrue();
    expect($this->policy->view(User::factory()->writer()->create(), $collection))->toBeFalse();
});

it('update allows owner only', function (): void {
    $owner = User::factory()->writer()->create();
    $collection = PostCollection::factory()->create(['user_id' => $owner->id]);

    expect($this->policy->update($owner, $collection))->toBeTrue();
    expect($this->policy->update(User::factory()->writer()->create(), $collection))->toBeFalse();
});

it('delete allows owner only', function (): void {
    $owner = User::factory()->writer()->create();
    $collection = PostCollection::factory()->create(['user_id' => $owner->id]);

    expect($this->policy->delete($owner, $collection))->toBeTrue();
    expect($this->policy->delete(User::factory()->writer()->create(), $collection))->toBeFalse();
});
