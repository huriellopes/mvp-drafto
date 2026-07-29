<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Public;

use App\Actions\Public\ListExploreWritersAction;
use App\DTOs\Public\ExploreWritersFilterData;
use App\Enums\ProfileVisibilityEnum;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
    $this->action = app(ListExploreWritersAction::class);
});

function publicWriter(array $profile = []): User
{
    $name = $profile['name'] ?? fake()->name();

    $user = User::factory()->writer()->active()->create(['name' => $name]);

    $user->profile()->create(array_merge([
        'name' => $name,
        'username' => 'user' . fake()->unique()->numberBetween(1000, 999999),
        'email' => fake()->unique()->safeEmail(),
        'visibility' => ProfileVisibilityEnum::PUBLIC,
    ], $profile));

    return $user->fresh();
}

it('lists active writers with complete public profiles', function () {
    $writer = publicWriter();

    $result = $this->action->exec(new ExploreWritersFilterData);

    expect($result->total())->toBe(1)
        ->and($result->first()->id)->toBe($writer->id);
});

it('excludes profiles that are not public', function () {
    publicWriter(['visibility' => ProfileVisibilityEnum::PRIVATE]);

    $result = $this->action->exec(new ExploreWritersFilterData);

    expect($result->total())->toBe(0);
});

it('excludes profiles missing a username', function () {
    $user = User::factory()->writer()->active()->create();
    $user->profile()->create([
        'name' => 'No Username',
        'username' => 'temp' . fake()->unique()->numberBetween(1000, 999999),
        'email' => fake()->unique()->safeEmail(),
        'visibility' => ProfileVisibilityEnum::PUBLIC,
    ]);
    // Force the username empty to trigger the exclusion rule.
    $user->profile()->update(['username' => '']);

    $result = $this->action->exec(new ExploreWritersFilterData);

    expect($result->total())->toBe(0);
});

it('filters writers by search term', function () {
    $match = publicWriter(['name' => 'Findable Writer']);
    publicWriter();

    $result = $this->action->exec(new ExploreWritersFilterData(search: 'Findable Writer'));

    expect($result->total())->toBe(1)
        ->and($result->first()->id)->toBe($match->id);
});
