<?php

declare(strict_types=1);

namespace Tests\Feature\Actions\Support;

use App\Actions\Support\SendSupportMessageAction;
use App\DTOs\SupportContactData;
use App\Jobs\ProcessSupportMessageJob;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Queue::fake();
    $this->action = app(SendSupportMessageAction::class);
});

it('dispatches the support message processing job', function () {
    $data = new SupportContactData(
        name: 'John Doe',
        email: 'john@example.com',
        subject: 'Need help',
        message: 'Something is broken',
    );

    $this->action->exec($data);

    Queue::assertPushed(ProcessSupportMessageJob::class, function ($job) use ($data) {
        return $job->data->email === $data->email;
    });
});
