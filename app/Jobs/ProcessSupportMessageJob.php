<?php

declare(strict_types=1);

namespace App\Jobs;

use App\DTOs\SupportContactData;
use App\Notifications\SupportMessageReceivedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class ProcessSupportMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public SupportContactData $data
    ) {}

    public function handle(): void
    {
        $recipient = config('support.email');

        Notification::route('mail', $recipient)
            ->notify(new SupportMessageReceivedNotification($this->data));
    }
}
