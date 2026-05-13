<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\Services\LoggerInterface;
use App\Enums\LogCategoryEnum;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class ProcessPostViewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Sênior: O número de vezes que o job pode ser tentado.
     */
    public int $tries = 5;

    /**
     * Sênior: O número de segundos a aguardar antes de tentar novamente.
     */
    public array $backoff = [10, 30, 60, 120];

    public function __construct(
        private readonly int $postId,
        private readonly ?int $userId,
        private readonly string $sessionId,
        private readonly string $ipHash,
        private readonly string $userAgent,
    ) {}

    public function handle(): void
    {
        // 1. Throttling: Verifica se já houve visualização recente (1 hora)
        $exists = DB::table('post_views')
            ->where('post_id', $this->postId)
            ->where(function ($q) {
                $q->where('session_id', $this->sessionId)->orWhere('ip_hash', $this->ipHash);
            })
            ->where('viewed_at', '>', now()->subHour())
            ->exists();

        if ($exists) {
            return;
        }

        // 2. Registra o log de visualização
        DB::table('post_views')->insert([
            'post_id' => $this->postId,
            'user_id' => $this->userId,
            'session_id' => $this->sessionId,
            'ip_hash' => $this->ipHash,
            'user_agent' => mb_substr($this->userAgent, 0, 255),
            'viewed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Buffer de Performance: Incrementa no Redis
        try {
            Redis::incr("post_views_buffer:{$this->postId}");
        } catch (Exception $e) {
            app(LoggerInterface::class)->error("Redis falhou ao incrementar view para o Post #{$this->postId}", LogCategoryEnum::QUEUE, [
                'exception' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Tratamento de falha definitiva.
     */
    public function failed(Throwable $exception): void
    {
        app(LoggerInterface::class)->error("Job ProcessPostViewJob falhou definitivamente para o Post #{$this->postId}", LogCategoryEnum::QUEUE, [
            'exception' => $exception->getMessage(),
        ]);
    }
}
