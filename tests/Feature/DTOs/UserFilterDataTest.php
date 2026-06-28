<?php

declare(strict_types=1);

namespace Tests\Feature\DTOs;

use App\DTOs\UserFilterData;

it('uses sensible defaults', function () {
    $dto = new UserFilterData();

    expect($dto->search)->toBeNull()
        ->and($dto->role)->toBeNull()
        ->and($dto->status)->toBeNull()
        ->and($dto->sort)->toBe('created_at')
        ->and($dto->direction)->toBe('desc')
        ->and($dto->per_page)->toBe(15);
});

it('serializes to array', function () {
    $dto = new UserFilterData(search: 'john', role: 'writer', status: 'active');

    expect($dto->toArray())->toMatchArray([
        'search' => 'john',
        'role' => 'writer',
        'status' => 'active',
        'sort' => 'created_at',
        'direction' => 'desc',
        'per_page' => 15,
    ]);
});

it('builds a deterministic cache key from its state', function () {
    $a = new UserFilterData(search: 'john');
    $b = new UserFilterData(search: 'john');
    $c = new UserFilterData(search: 'jane');

    expect($a->getCacheKey())->toStartWith('users_list_')
        ->and($a->getCacheKey())->toBe($b->getCacheKey())
        ->and($a->getCacheKey())->not->toBe($c->getCacheKey());
});

it('hydrates from an array', function () {
    $dto = UserFilterData::from([
        'search' => 'foo',
        'role' => 'reader',
        'direction' => 'asc',
    ]);

    expect($dto->search)->toBe('foo')
        ->and($dto->role)->toBe('reader')
        ->and($dto->direction)->toBe('asc');
});
