<?php

declare(strict_types=1);

namespace App\Logging\Telegram;

use Monolog\Level;
use Monolog\Logger;

final class CreateTelegramLogger
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __invoke(array $config): Logger
    {
        $logger = new Logger('telegram');

        // Sênior: Garantimos que o thread ID da configuração do canal tenha prioridade total
        $threadId = array_key_exists('thread', $config) 
            ? $config['thread'] 
            : config('services.telegram.thread');

        // Sênior: Se o ID não for numérico ou estiver vazio, tratamos como NULL (Main Chat)
        $threadId = (is_numeric($threadId)) ? (string) $threadId : null;

        // Sênior: Resolvemos o nível de log para o Enum do Monolog 3.x
        $level = Level::fromName(ucfirst($config['level'] ?? 'error'));

        $handler = new TelegramLoggerHandler(
            token: (string) config('services.telegram.token'),
            chatId: (string) config('services.telegram.chat'),
            threadId: $threadId ? (string) $threadId : null,
            level: $level,
        );

        $logger->pushHandler($handler);

        return $logger;
    }
}
