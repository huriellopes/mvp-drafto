<?php

declare(strict_types=1);

namespace Tests\Feature\Policies;

use App\Enums\ModuleEnum;
use App\Models\Module;
use App\Models\Profile;
use App\Models\User;
use App\Policies\ProfilePolicy;

beforeEach(function (): void {
    $this->policy = new ProfilePolicy();
});

function attachProfileModule(User $user, array $settings): void
{
    $module = Module::where('slug', ModuleEnum::PROFILE->value)->firstOrFail();
    $user->modules()->syncWithoutDetaching([
        $module->id => ['is_enabled' => true, 'settings' => $settings],
    ]);
}

it('before grants everything to super admin', function (): void {
    expect($this->policy->before(User::factory()->superAdmin()->create(), 'update'))->toBeTrue();
});

it('before returns null for non admin', function (): void {
    expect($this->policy->before(User::factory()->writer()->create(), 'update'))->toBeNull();
});

it('viewAny always allows even guests', function (): void {
    expect($this->policy->viewAny(null))->toBeTrue();
});

it('view always allows even guests', function (): void {
    $profile = Profile::factory()->create();

    expect($this->policy->view(null, $profile))->toBeTrue();
});

it('create allows active user without profile', function (): void {
    $user = User::factory()->active()->create();

    expect($this->policy->create($user))->toBeTrue();
});

it('create denies user that already has a profile', function (): void {
    $user = User::factory()->active()->withProfile()->create();

    expect($this->policy->create($user->fresh()))->toBeFalse();
});

it('create denies suspended user', function (): void {
    $user = User::factory()->suspended()->create();

    expect($this->policy->create($user))->toBeFalse();
});

it('update denies suspended owner', function (): void {
    $user = User::factory()->suspended()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    expect($this->policy->update($user, $profile))->toBeFalse();
});

it('update denies non owner', function (): void {
    $user = User::factory()->active()->create();
    $profile = Profile::factory()->create();

    expect($this->policy->update($user, $profile))->toBeFalse();
});

it('update allows owner when no color fields are present', function (): void {
    $user = User::factory()->active()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    expect($this->policy->update($user, $profile))->toBeTrue();
});

it('update allows owner changing colors when module permits', function (): void {
    $user = User::factory()->active()->create();
    attachProfileModule($user, ['allow_custom_colors' => true]);
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    request()->merge(['primary_color' => '#ffffff']);

    expect($this->policy->update($user->fresh(), $profile))->toBeTrue();
});

it('update denies owner changing colors when module forbids', function (): void {
    $user = User::factory()->active()->create();
    attachProfileModule($user, ['allow_custom_colors' => false]);
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    request()->merge(['accent_color' => '#000000']);

    expect($this->policy->update($user->fresh(), $profile))->toBeFalse();
});

it('delete always denies', function (): void {
    $user = User::factory()->create();
    $profile = Profile::factory()->create(['user_id' => $user->id]);

    expect($this->policy->delete($user, $profile))->toBeFalse();
});
