<?php

declare(strict_types=1);

namespace Tests\Feature\Enums;

use App\Enums\LinkVisibilityEnum;
use App\Enums\PostTypeEnum;
use App\Enums\PostVisibilityEnum;
use App\Enums\ReportReasonEnum;
use App\Enums\ReportStatusEnum;
use App\Enums\ThemePlatformEnum;
use App\Enums\UpdateAudienceEnum;

// Sweep: every backed enum under App\Enums that defines label() must return a
// non-empty string for every case, and color() must too. Some labels resolve
// through __() (translations), so this runs as a Feature test to boot the app.
dataset('all_enums', function () {
    $enums = [];

    // Resolved from this file's location; app_path()/the container is not
    // booted yet when datasets are evaluated.
    $enumDir = dirname(__DIR__, 3) . '/app/Enums';

    foreach (glob($enumDir . '/*.php') as $file) {
        $class = 'App\\Enums\\' . basename($file, '.php');

        if (!enum_exists($class)) {
            continue;
        }

        if (!method_exists($class, 'label')) {
            continue;
        }

        foreach ($class::cases() as $case) {
            $enums["{$class}::{$case->name}"] = [$case];
        }
    }

    return $enums;
});

it('returns a non-empty label and color for every enum case', function ($case) {
    expect($case->label())->toBeString()->not->toBeEmpty();

    if (method_exists($case, 'color')) {
        expect($case->color())->toBeString()->not->toBeEmpty();
    }
})->with('all_enums');

it('covers LinkVisibilityEnum labels and colors', function () {
    foreach (LinkVisibilityEnum::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty()
            ->and($case->color())->toBeString()->not->toBeEmpty();
    }

    expect(LinkVisibilityEnum::PUBLIC->color())->toBe('emerald')
        ->and(LinkVisibilityEnum::PRIVATE->color())->toBe('zinc');
});

it('covers PostTypeEnum labels and colors', function () {
    foreach (PostTypeEnum::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty()
            ->and($case->color())->toBeString()->not->toBeEmpty();
    }

    expect(PostTypeEnum::POST->color())->toBe('blue')
        ->and(PostTypeEnum::ARTICLE->color())->toBe('green');
});

it('covers PostVisibilityEnum labels and colors', function () {
    foreach (PostVisibilityEnum::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty()
            ->and($case->color())->toBeString()->not->toBeEmpty();
    }

    expect(PostVisibilityEnum::PUBLIC->color())->toBe('green')
        ->and(PostVisibilityEnum::UNLISTED->color())->toBe('yellow')
        ->and(PostVisibilityEnum::FOLLOWERS_ONLY->color())->toBe('blue')
        ->and(PostVisibilityEnum::REGISTERED->color())->toBe('purple')
        ->and(PostVisibilityEnum::REGISTERED->label())->toBe('Exclusivo (Membros)');
});

it('covers ReportReasonEnum labels and colors', function () {
    foreach (ReportReasonEnum::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty()
            ->and($case->color())->toBeString()->not->toBeEmpty();
    }

    expect(ReportReasonEnum::SPAM->color())->toBe('yellow')
        ->and(ReportReasonEnum::ABUSE->color())->toBe('red')
        ->and(ReportReasonEnum::HARASSMENT->color())->toBe('red')
        ->and(ReportReasonEnum::INAPPROPRIATE->color())->toBe('red')
        ->and(ReportReasonEnum::PLAGIARISM->color())->toBe('orange')
        ->and(ReportReasonEnum::PRAISE->color())->toBe('green')
        ->and(ReportReasonEnum::SUGGESTION->color())->toBe('blue')
        ->and(ReportReasonEnum::BUG->color())->toBe('purple')
        ->and(ReportReasonEnum::OTHER->color())->toBe('gray');
});

it('covers ReportStatusEnum labels and colors', function () {
    foreach (ReportStatusEnum::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty()
            ->and($case->color())->toBeString()->not->toBeEmpty();
    }

    expect(ReportStatusEnum::PENDING->color())->toBe('orange')
        ->and(ReportStatusEnum::REVIEWED->color())->toBe('blue')
        ->and(ReportStatusEnum::DISMISSED->color())->toBe('gray')
        ->and(ReportStatusEnum::ACTION_TAKEN->color())->toBe('red')
        ->and(ReportStatusEnum::ACKNOWLEDGED->color())->toBe('green')
        ->and(ReportStatusEnum::IN_PLANNING->color())->toBe('purple')
        ->and(ReportStatusEnum::IMPLEMENTED->color())->toBe('indigo');
});

it('covers ThemePlatformEnum labels, colors and defaults', function () {
    foreach (ThemePlatformEnum::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty()
            ->and($case->color())->toBeString()->not->toBeEmpty();
    }

    expect(ThemePlatformEnum::LIGHT->color())->toBe('yellow')
        ->and(ThemePlatformEnum::DARK->color())->toBe('gray')
        ->and(ThemePlatformEnum::SYSTEM->color())->toBe('blue')
        ->and(ThemePlatformEnum::defaultPrimary())->toBe('#18181b')
        ->and(ThemePlatformEnum::defaultAccent())->toBe('#3f3f46');
});

it('covers UpdateAudienceEnum labels, colors and descriptions', function () {
    foreach (UpdateAudienceEnum::cases() as $case) {
        expect($case->label())->toBeString()->not->toBeEmpty()
            ->and($case->color())->toBeString()->not->toBeEmpty()
            ->and($case->description())->toBeString()->not->toBeEmpty();
    }

    expect(UpdateAudienceEnum::ALL->color())->toBe('gray')
        ->and(UpdateAudienceEnum::WRITERS->color())->toBe('blue')
        ->and(UpdateAudienceEnum::READERS->color())->toBe('emerald');
});

it('exposes options/values/labels via the EnumOptions trait', function () {
    foreach ([LinkVisibilityEnum::class, PostTypeEnum::class, ReportReasonEnum::class] as $enum) {
        expect($enum::options())->toHaveCount(count($enum::cases()))
            ->and($enum::values())->toHaveCount(count($enum::cases()))
            ->and($enum::labels())->toHaveCount(count($enum::cases()));
    }
});
