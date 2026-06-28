<?php

declare(strict_types=1);

namespace Tests\Feature\Enums;

use App\Enums\CommentStatusEnum;

it('returns the description for every comment status', function () {
    expect(CommentStatusEnum::VISIBLE->description())->not->toBe('')
        ->and(CommentStatusEnum::HIDDEN->description())->toContain('ocultado')
        ->and(CommentStatusEnum::PENDING->description())->toContain('aguardando');
});

it('returns a label and color for every comment status', function () {
    foreach (CommentStatusEnum::cases() as $case) {
        expect($case->label())->toBeString()
            ->and($case->color())->toBeString();
    }
});
