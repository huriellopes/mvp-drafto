<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Paths of background-generated files (Excel exports, recovery codes).
 *
 * Every file lives under a per-user directory so the download endpoint can
 * verify ownership — a predictable file name alone must never be enough to
 * fetch another user's file.
 */
final class TemporaryExport
{
    public const string DISK = 'local';

    public static function pathFor(int|string $userId, string $fileName): string
    {
        return self::directoryFor($userId) . basename($fileName);
    }

    public static function isOwnedBy(string $path, int|string $userId): bool
    {
        if (str_contains($path, '..') || str_contains($path, "\0")) {
            return false;
        }

        $directory = self::directoryFor($userId);

        return str_starts_with($path, $directory) && mb_strlen($path) > mb_strlen($directory);
    }

    private static function directoryFor(int|string $userId): string
    {
        return "temp/exports/{$userId}/";
    }
}
