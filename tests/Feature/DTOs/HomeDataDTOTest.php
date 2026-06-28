<?php

declare(strict_types=1);

namespace Tests\Feature\DTOs;

use App\DTOs\Public\HomeDataDTO;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

function makeHomeData(): HomeDataDTO
{
    $posts = new LengthAwarePaginator(
        items: collect([['id' => 1, 'title' => 'A']]),
        total: 1,
        perPage: 10,
        currentPage: 1,
    );

    return new HomeDataDTO(
        totalPosts: 42,
        totalUsers: 7,
        featuredWriters: collect([['id' => 1]]),
        posts: $posts,
        categories: collect([['id' => 1, 'name' => 'Tech']]),
    );
}

it('constructs and exposes its properties', function () {
    $dto = makeHomeData();

    expect($dto->totalPosts)->toBe(42)
        ->and($dto->totalUsers)->toBe(7)
        ->and($dto->featuredWriters)->toBeInstanceOf(Collection::class)
        ->and($dto->featuredWriters)->toHaveCount(1)
        ->and($dto->posts)->toBeInstanceOf(LengthAwarePaginator::class)
        ->and($dto->posts->total())->toBe(1)
        ->and($dto->categories)->toBeInstanceOf(Collection::class)
        ->and($dto->categories->first()['name'])->toBe('Tech');
});

it('serializes to an array with all keys', function () {
    expect(makeHomeData()->toArray())
        ->toHaveKeys(['totalPosts', 'totalUsers', 'featuredWriters', 'posts', 'categories']);
});
