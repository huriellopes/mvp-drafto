<?php

declare(strict_types=1);

namespace Tests\Feature\DTOs;

use App\DTOs\LogEntryData;

it('constructs and exposes its properties', function () {
    $entry = new LogEntryData(
        level: 'error',
        loggedAt: '2026-01-01 10:00:00',
        summary: 'Something broke',
        details: 'Stack trace',
    );

    expect($entry->level)->toBe('error')
        ->and($entry->loggedAt)->toBe('2026-01-01 10:00:00')
        ->and($entry->summary)->toBe('Something broke')
        ->and($entry->details)->toBe('Stack trace');
});

it('maps log levels to badge colors', function (string $level, string $color) {
    $entry = new LogEntryData($level, '2026-01-01', 'sum', 'det');

    expect($entry->color())->toBe($color);
})->with([
    ['emergency', 'red'],
    ['alert', 'red'],
    ['critical', 'red'],
    ['error', 'red'],
    ['ERROR', 'red'],
    ['warning', 'orange'],
    ['notice', 'blue'],
    ['info', 'blue'],
    ['debug', 'gray'],
    ['unknown-level', 'gray'],
]);

it('serializes to array via spatie data', function () {
    $entry = new LogEntryData('info', '2026-01-01', 'sum', 'det');

    expect($entry->toArray())->toMatchArray([
        'level' => 'info',
        'loggedAt' => '2026-01-01',
        'summary' => 'sum',
        'details' => 'det',
    ]);
});
