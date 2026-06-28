<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\Collection;
use App\Models\User;
use App\Policies\CollectionPolicy;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->policy = new CollectionPolicy();
});

function makeCollection(User $owner, bool $isPublic): Collection
{
    $collection = new Collection();
    $collection->user_id = $owner->id;
    $collection->name = 'My Collection';
    $collection->slug = 'my-collection-' . Str::lower(Str::random(6));
    $collection->save();

    // is_public is a policy-evaluated attribute, set dynamically on the instance.
    $collection->is_public = $isPublic;

    return $collection;
}

it('before grants everything to super admin', function (): void {
    expect($this->policy->before(User::factory()->superAdmin()->create(), 'delete'))->toBeTrue();
});

it('before returns null for non admin', function (): void {
    expect($this->policy->before(User::factory()->writer()->create(), 'update'))->toBeNull();
});

it('viewAny always allows', function (): void {
    expect($this->policy->viewAny(User::factory()->create()))->toBeTrue();
});

it('view allows anyone on public collection', function (): void {
    $collection = makeCollection(User::factory()->create(), true);

    expect($this->policy->view(User::factory()->create(), $collection))->toBeTrue();
});

it('view allows owner on private collection', function (): void {
    $owner = User::factory()->create();
    $collection = makeCollection($owner, false);

    expect($this->policy->view($owner, $collection))->toBeTrue();
});

it('view denies non owner on private collection', function (): void {
    $collection = makeCollection(User::factory()->create(), false);

    expect($this->policy->view(User::factory()->create(), $collection))->toBeFalse();
});

it('create allows active user and denies suspended', function (): void {
    expect($this->policy->create(User::factory()->active()->create()))->toBeTrue();
    expect($this->policy->create(User::factory()->suspended()->create()))->toBeFalse();
});

it('update allows active owner only', function (): void {
    $owner = User::factory()->active()->create();
    $collection = makeCollection($owner, false);

    expect($this->policy->update($owner, $collection))->toBeTrue();
    expect($this->policy->update(User::factory()->active()->create(), $collection))->toBeFalse();
});

it('update denies suspended owner', function (): void {
    $owner = User::factory()->suspended()->create();
    $collection = makeCollection($owner, false);

    expect($this->policy->update($owner, $collection))->toBeFalse();
});

it('delete allows active owner only', function (): void {
    $owner = User::factory()->active()->create();
    $collection = makeCollection($owner, false);

    expect($this->policy->delete($owner, $collection))->toBeTrue();
    expect($this->policy->delete(User::factory()->active()->create(), $collection))->toBeFalse();
});

it('delete denies suspended owner', function (): void {
    $owner = User::factory()->suspended()->create();
    $collection = makeCollection($owner, false);

    expect($this->policy->delete($owner, $collection))->toBeFalse();
});
