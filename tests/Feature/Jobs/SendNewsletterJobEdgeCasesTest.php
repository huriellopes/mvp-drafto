<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs;

use App\Contracts\Services\LoggerInterface;
use App\Jobs\SendNewsletterJob;
use App\Models\NewsletterSubscriber;
use Exception;
use Illuminate\Support\Facades\Mail;
use Mockery;

afterEach(function () {
    Mockery::close();
});

it('logs and rethrows when sending the newsletter fails', function () {
    $subscriber = NewsletterSubscriber::factory()->verified()->create([
        'email' => 'fails@example.com',
    ]);

    // Make the Mail facade throw so the catch block (lines 51-56) runs.
    $pendingMail = Mockery::mock();
    $pendingMail->shouldReceive('send')->andThrow(new Exception('smtp down'));
    Mail::shouldReceive('to')->andReturn($pendingMail);

    $logger = Mockery::mock(LoggerInterface::class);
    $logger->shouldReceive('error')->once();
    app()->instance(LoggerInterface::class, $logger);

    expect(fn () => app()->call([new SendNewsletterJob($subscriber), 'handle']))
        ->toThrow(Exception::class, 'smtp down');
});
