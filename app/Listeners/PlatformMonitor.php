<?php

declare(strict_types=1);

namespace App\Listeners;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Support\Facades\Log;

/**
 * Espelha a observabilidade da plataforma para os canais de log do Telegram.
 *
 * Convenção de canais (config/logging.php):
 * - telegram_alerts (warning+): falhas acionáveis (comandos, crons, jobs, notifications)
 * - telegram_debug (debug):     ruído de execução (tarefas agendadas concluídas)
 *
 * Novos usuários e demais warnings/errors já são cobertos (RegisterUserAction e o
 * stack de log padrão, respectivamente).
 */
final class PlatformMonitor
{
    /**
     * Cron de altíssima frequência cujo "concluído" não vale a pena logar.
     */
    private const string NOISY_EXPRESSION = '* * * * *';

    public function commandFinished(CommandFinished $event): void
    {
        if (($event->exitCode ?? 0) === 0) {
            return;
        }

        $this->report(
            'telegram_alerts',
            'error',
            "Comando finalizou com erro: {$event->command} (exit code {$event->exitCode})",
        );
    }

    public function scheduledTaskFinished(ScheduledTaskFinished $event): void
    {
        // Evita flood de tarefas que rodam a cada minuto (falhas continuam cobertas).
        if ($event->task->expression === self::NOISY_EXPRESSION) {
            return;
        }

        $this->report(
            'telegram_debug',
            'debug',
            "Tarefa agendada concluída: {$event->task->getSummaryForDisplay()}",
        );
    }

    public function scheduledTaskFailed(ScheduledTaskFailed $event): void
    {
        $this->report(
            'telegram_alerts',
            'error',
            "Tarefa agendada falhou: {$event->task->getSummaryForDisplay()}",
            ['exception' => $event->exception],
        );
    }

    public function jobFailed(JobFailed $event): void
    {
        $this->report(
            'telegram_alerts',
            'error',
            "Job de fila falhou: {$event->job->resolveName()} (conexão {$event->connectionName})",
            ['exception' => $event->exception],
        );
    }

    public function notificationFailed(NotificationFailed $event): void
    {
        $this->report(
            'telegram_alerts',
            'error',
            'Falha ao enviar notification: ' . $event->notification::class . " (canal {$event->channel})",
            ['data' => $event->data],
        );
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private function report(string $channel, string $level, string $message, array $context = []): void
    {
        // Não tenta alertar se o Telegram não estiver configurado.
        if (blank(config('services.telegram.token'))) {
            return;
        }

        Log::channel($channel)->{$level}($message, $context);
    }
}
