<?php

declare(strict_types=1);

namespace Tests\Feature\Enums;

use App\Enums\PostStatusEnum;

// O trait EnumOptions é a base de ~16 enums (dropdowns, filtros). Validamos sua
// mecânica através de um enum concreto (PostStatusEnum).

it('builds an options array with value, label and color for every case', function () {
    $options = PostStatusEnum::options();

    expect($options)->toHaveCount(count(PostStatusEnum::cases()));

    foreach ($options as $option) {
        expect($option)->toHaveKeys(['value', 'label', 'color'])
            ->and($option['label'])->toBeString()->not->toBeEmpty()
            ->and($option['color'])->toBeString()->not->toBeEmpty();
    }
});

it('exposes the backing values', function () {
    expect(PostStatusEnum::values())
        ->toBe(['draft', 'published', 'archived', 'scheduled']);
});

it('maps each value to its label', function () {
    $labels = PostStatusEnum::labels();

    expect($labels)->toHaveCount(4)
        ->and(array_keys($labels))->toBe(['draft', 'published', 'archived', 'scheduled'])
        ->and($labels['draft'])->toBeString()->not->toBeEmpty();
});

it('returns the configured color per case', function () {
    expect(PostStatusEnum::DRAFT->color())->toBe('yellow')
        ->and(PostStatusEnum::PUBLISHED->color())->toBe('green')
        ->and(PostStatusEnum::ARCHIVED->color())->toBe('gray')
        ->and(PostStatusEnum::SCHEDULED->color())->toBe('blue');
});
