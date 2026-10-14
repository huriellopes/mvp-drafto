<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Events\Exports\ExportFailed;
use App\Events\Exports\ExportReady;
use App\Jobs\ExportDataJob;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

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

it('stores the export in the owner directory on the given disk', function (): void {
    Event::fake([ExportReady::class]);

    $job = new ExportDataJob(
        exportClass: StubExport::class,
        params: ['label' => 'hello'],
        fileName: 'subscribers.xlsx',
        userId: 7,
        disk: 'local',
    );

    app()->call([$job, 'handle']);

    Excel::assertStored('temp/exports/7/subscribers.xlsx', 'local');
});

it('passes constructor params to the resolved export class', function (): void {
    Event::fake([ExportReady::class]);

    $job = new ExportDataJob(
        exportClass: StubExport::class,
        params: ['label' => 'custom-label'],
        fileName: 'report.xlsx',
        userId: 7,
    );

    app()->call([$job, 'handle']);

    Excel::assertStored('temp/exports/7/report.xlsx', 'local', function (StubExport $export): bool {
        return $export->label === 'custom-label';
    });
});

it('pushes ExportReady to the owner once the file is stored', function (): void {
    Event::fake([ExportReady::class]);

    app()->call([new ExportDataJob(StubExport::class, [], 'report.xlsx', 7), 'handle']);

    Event::assertDispatched(
        ExportReady::class,
        fn (ExportReady $event): bool => $event->userId === 7 && $event->path === 'temp/exports/7/report.xlsx',
    );
});

it('does not fail the job when broadcasting the completion fails', function (): void {
    Event::listen(ExportReady::class, fn () => throw new RuntimeException('reverb down'));

    app()->call([new ExportDataJob(StubExport::class, [], 'report.xlsx', 7), 'handle']);

    Excel::assertStored('temp/exports/7/report.xlsx', 'local');
});

it('pushes ExportFailed to the owner when the job fails', function (): void {
    Event::fake([ExportFailed::class]);

    (new ExportDataJob(StubExport::class, [], 'report.xlsx', 7))->failed(new RuntimeException('boom'));

    Event::assertDispatched(
        ExportFailed::class,
        fn (ExportFailed $event): bool => $event->userId === 7 && $event->path === 'temp/exports/7/report.xlsx',
    );
});

it('runs on the dedicated exports queue', function (): void {
    Queue::fake();

    dispatch(new ExportDataJob(StubExport::class, [], 'report.xlsx', 7));

    Queue::assertPushedOn(ExportDataJob::QUEUE, ExportDataJob::class);
});

it('uses the long retry_after redis connection when the queue runs on redis', function (): void {
    config(['queue.default' => 'redis']);

    $job = new ExportDataJob(StubExport::class, [], 'report.xlsx', 7);

    expect($job->connection)->toBe(ExportDataJob::CONNECTION)
        ->and(config('queue.connections.redis-exports.retry_after'))->toBeGreaterThan($job->timeout);
});
