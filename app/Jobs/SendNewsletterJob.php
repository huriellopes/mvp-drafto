<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\Services\LoggerInterface;
use App\Enums\LogCategoryEnum;
use App\Mail\NewsletterNotification;
use App\Models\NewsletterSubscriber;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Sênior: O número de vezes que o job pode ser tentado.
     */
    public int $tries = 3;

    /**
     * Sênior: O número de segundos a aguardar antes de tentar novamente.
     */
    public array $backoff = [60, 300, 600]; // 1min, 5min, 10min

    public function __construct(
        protected NewsletterSubscriber $subscriber,
        protected array $posts = [],
        protected string $categoryName = 'Informativo',
        protected ?string $customMessage = null,
    ) {}

    public function handle(): void
    {
        try {
            Mail::to($this->subscriber->email)->send(
                new NewsletterNotification(
                    $this->posts,
                    $this->categoryName,
                    $this->subscriber,
                    $this->customMessage,
                ),
            );
        } catch (Exception $e) {
            app(LoggerInterface::class)->error("Falha ao enviar newsletter para {$this->subscriber->email}", LogCategoryEnum::QUEUE, [
                'exception' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
