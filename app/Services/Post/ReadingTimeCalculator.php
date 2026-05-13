<?php

declare(strict_types=1);

namespace App\Services\Post;

use Illuminate\Support\Str;

final class ReadingTimeCalculator
{
    private const int WORDS_PER_MINUTE = 200;

    public static function calculate(string $content): int
    {
        $stripContent = strip_tags($content);

        $wordCount = Str::wordCount($stripContent);

        return (int) ceil($wordCount / self::WORDS_PER_MINUTE) ?: 1;
    }
}
