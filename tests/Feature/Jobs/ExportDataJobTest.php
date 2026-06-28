<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Jobs\ExportDataJob;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Minimal export stub so the job can be exercised without real export classes.
 */
class StubExport implements FromArray
{
    public function __construct(public string $label = 'default') {}

    public function array(): array
    {
        return [[$this->label]];
    }
}

beforeEach(function (): void {
    Excel::fake();
});

it('stores the export to the temp folder on the given disk', function (): void {
    $job = new ExportDataJob(
        exportClass: StubExport::class,
        params: ['label' => 'hello'],
        fileName: 'subscribers.xlsx',
        disk: 'local',
    );

    app()->call([$job, 'handle']);

    Excel::assertStored('temp/subscribers.xlsx', 'local');
});

it('passes constructor params to the resolved export class', function (): void {
    $job = new ExportDataJob(
        exportClass: StubExport::class,
        params: ['label' => 'custom-label'],
        fileName: 'report.xlsx',
    );

    app()->call([$job, 'handle']);

    Excel::assertStored('temp/report.xlsx', 'local', function (StubExport $export): bool {
        return $export->label === 'custom-label';
    });
});
