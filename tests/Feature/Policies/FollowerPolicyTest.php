<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Models\User;
use App\Policies\FollowerPolicy;

beforeEach(function (): void {
    $this->policy = new FollowerPolicy;
});

it('before grants everything to super admin', function (): void {
    expect($this->policy->before(User::factory()->superAdmin()->create()))->toBeTrue();
});

it('before returns null for non admin', function (): void {
    expect($this->policy->before(User::factory()->writer()->create()))->toBeNull();
});

it('viewAny allows active user and denies suspended', function (): void {
    expect($this->policy->viewAny(User::factory()->active()->create()))->toBeTrue();
    expect($this->policy->viewAny(User::factory()->suspended()->create()))->toBeFalse();
});

it('canHaveFollowers always allows', function (): void {
    expect($this->policy->canHaveFollowers(User::factory()->create()))->toBeTrue();
});

it('delete allows active user and denies suspended', function (): void {
    $target = User::factory()->create();

    expect($this->policy->delete(User::factory()->active()->create(), $target))->toBeTrue();
    expect($this->policy->delete(User::factory()->suspended()->create(), $target))->toBeFalse();
});
