<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Users;

use App\Actions\Users\ListUsersAction;
use App\DTOs\UserFilterData;
use App\Enums\RoleEnum;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

it('paginates users and eager loads the profile relation', function () {
    User::factory()->count(3)->create();

    $result = app(ListUsersAction::class)->exec(new UserFilterData());

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($result->total())->toBeGreaterThanOrEqual(3)
        ->and($result->first()->relationLoaded('profile'))->toBeTrue();
});

it('filters by search across name and email', function () {
    $match = User::factory()->create([
        'name' => 'Zephyrine Searchable',
        'email' => 'zephyrine@example.com',
    ]);
    User::factory()->create([
        'name' => 'Unrelated Person',
        'email' => 'unrelated@example.com',
    ]);

    $byName = app(ListUsersAction::class)->exec(new UserFilterData(search: 'Zephyrine'));
    $byEmail = app(ListUsersAction::class)->exec(new UserFilterData(search: 'zephyrine@example.com'));

    expect($byName->pluck('id')->all())->toBe([$match->id])
        ->and($byEmail->pluck('id')->all())->toBe([$match->id]);
});

it('filters by role and status', function () {
    $writer = User::factory()->writer()->active()->create([
        'name' => 'Active Writer Unique',
    ]);
    User::factory()->reader()->active()->create();

    $result = app(ListUsersAction::class)->exec(new UserFilterData(
        role: RoleEnum::WRITER->value,
        status: UserStatusEnum::ACTIVE->value,
    ));

    expect($result->pluck('id')->all())->toContain($writer->id)
        ->and($result->pluck('role')->unique()->all())->toBe([RoleEnum::WRITER]);
});

it('respects the per_page limit', function () {
    User::factory()->count(5)->create();

    $result = app(ListUsersAction::class)->exec(new UserFilterData(per_page: 2));

    expect($result->perPage())->toBe(2)
        ->and($result->count())->toBe(2);
});
