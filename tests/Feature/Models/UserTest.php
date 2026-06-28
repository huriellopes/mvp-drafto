<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Carbon;
use Spatie\Sitemap\Tags\Url;

it('returns an empty sitemap tag when there is no profile', function () {
    $user = User::factory()->active()->create();

    expect($user->toSitemapTag())->toBe([]);
});

it('returns an empty sitemap tag when the user is not active', function () {
    $user = User::factory()->suspended()->withProfile()->create();

    expect($user->toSitemapTag())->toBe([]);
});

it('builds a sitemap tag for an active user with a profile', function () {
    $user = User::factory()->active()->withProfile()->create();

    $tag = $user->toSitemapTag();

    expect($tag)->toBeInstanceOf(Url::class)
        ->and($tag->url)->toContain($user->profile->username);
});

it('treats super admins as verified regardless of profile', function () {
    $admin = User::factory()->superAdmin()->create();

    expect($admin->isVerified())->toBeTrue();
});

it('uses the profile verified flag for non-admins', function () {
    $verified = User::factory()->writer()->create();
    Profile::factory()->create(['user_id' => $verified->id, 'is_verified' => true]);

    $unverified = User::factory()->writer()->create();
    Profile::factory()->create(['user_id' => $unverified->id, 'is_verified' => false]);

    expect($verified->fresh()->isVerified())->toBeTrue()
        ->and($unverified->fresh()->isVerified())->toBeFalse();
});

it('returns false for verified when there is no profile', function () {
    $user = User::factory()->writer()->create();

    expect($user->isVerified())->toBeFalse();
});

it('checks roles and admin status', function () {
    $admin = User::factory()->superAdmin()->create();
    $writer = User::factory()->writer()->create();

    expect($admin->hasRole(RoleEnum::SUPER_ADMIN))->toBeTrue()
        ->and($admin->isAdmin())->toBeTrue()
        ->and($writer->hasRole(RoleEnum::WRITER))->toBeTrue()
        ->and($writer->isAdmin())->toBeFalse();
});

it('reports active status', function () {
    expect(User::factory()->active()->create()->isActive())->toBeTrue()
        ->and(User::factory()->suspended()->create()->isActive())->toBeFalse();

    $pending = User::factory()->create(['status' => UserStatusEnum::PENDING]);
    expect($pending->isActive())->toBeFalse();
});

it('greets based on the time of day', function () {
    $user = User::factory()->create();

    Carbon::setTestNow(Carbon::createFromTime(8));
    expect($user->greeting())->toBe('Bom dia');

    Carbon::setTestNow(Carbon::createFromTime(14));
    expect($user->greeting())->toBe('Boa tarde');

    Carbon::setTestNow(Carbon::createFromTime(22));
    expect($user->greeting())->toBe('Boa noite');

    Carbon::setTestNow();
});
