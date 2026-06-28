<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Contracts\Services\LoggerInterface;
use App\Jobs\ProcessProfileViewJob;
use Mockery;
use RuntimeException;

afterEach(function () {
    Mockery::close();
});

it('logs through the failed hook', function () {
    $logger = Mockery::mock(LoggerInterface::class);
    $logger->shouldReceive('error')->once();
    app()->instance(LoggerInterface::class, $logger);

    $job = new ProcessProfileViewJob(123, 1, 'ip-hash');
    $job->failed(new RuntimeException('boom'));

    expect(true)->toBeTrue();
});
