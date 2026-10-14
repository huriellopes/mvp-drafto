<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\TemporaryExport;

it('builds the per-user path and strips directories from the file name', function () {
    expect(TemporaryExport::pathFor(3, 'report.xlsx'))->toBe('temp/exports/3/report.xlsx')
        ->and(TemporaryExport::pathFor(3, '../../etc/passwd'))->toBe('temp/exports/3/passwd');
});

it('recognizes ownership', function (string $path, int $userId, bool $expected) {
    expect(TemporaryExport::isOwnedBy($path, $userId))->toBe($expected);
})->with([
    'owner file' => ['temp/exports/3/report.xlsx', 3, true],
    'other user file' => ['temp/exports/4/report.xlsx', 3, false],
    'prefix collision' => ['temp/exports/31/report.xlsx', 3, false],
    'directory only' => ['temp/exports/3/', 3, false],
    'traversal' => ['temp/exports/3/../4/report.xlsx', 3, false],
    'null byte' => ["temp/exports/3/a\0.xlsx", 3, false],
    'legacy path' => ['temp/report.xlsx', 3, false],
    'empty' => ['', 3, false],
]);
