<?php

declare(strict_types=1);

use App\Services\Post\ReadingTimeCalculator;

it('calculates reading time correctly for small texts', function () {
    $text = str_repeat('word ', 100); // 100 words

    $time = ReadingTimeCalculator::calculate($text);

    expect($time)->toBe(1); // 100 / 200 = 0.5 -> ceil(0.5) = 1
});

it('calculates reading time correctly for large texts', function () {
    $text = str_repeat('word ', 500); // 500 words

    $time = ReadingTimeCalculator::calculate($text);

    expect($time)->toBe(3); // 500 / 200 = 2.5 -> ceil(2.5) = 3
});

it('strips html tags before calculating', function () {
    $text = '<p>' . str_repeat('word ', 200) . '</p>';

    $time = ReadingTimeCalculator::calculate($text);

    expect($time)->toBe(1);
});

it('returns minimum 1 minute for empty or very small text', function () {
    expect(ReadingTimeCalculator::calculate(''))->toBe(1)
        ->and(ReadingTimeCalculator::calculate('Hello world'))->toBe(1);
});
