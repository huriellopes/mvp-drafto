<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Contracts\Services\LoggerInterface;
use App\Enums\LogCategoryEnum;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Throwable;

final class ProcessProfileViewJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(
        private readonly int $profileId,
        private readonly ?int $userId,
        private readonly string $sessionId,
        private readonly string $ipHash,
        private readonly string $userAgent,
    ) {}

    public function handle(): void
    {
        // 1. Throttling: 1 view por hora por sessão/IP
        $cacheKey = "profile_view_throttle:{$this->profileId}:" . ($this->userId ?? $this->ipHash);

        if (Redis::get($cacheKey)) {
            return;
        }

        // 2. Registra no banco (Opcional: tabela dedicada de analytics de perfil)
        // Por enquanto vamos apenas incrementar a coluna profile_views
        DB::table('profiles')
            ->where('id', $this->profileId)
            ->increment('profile_views');

        // 3. Trava o throttle no Redis por 1 hora
        Redis::setex($cacheKey, 3600, '1');
    }

    public function failed(Throwable $exception): void
    {
        app(LoggerInterface::class)->error("Job ProcessProfileViewJob falhou para o Perfil #{$this->profileId}", LogCategoryEnum::QUEUE, [
            'exception' => $exception->getMessage(),
        ]);
    }
}
