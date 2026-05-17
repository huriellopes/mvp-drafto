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

        $threadId = array_key_exists('thread', $config)
            ? $config['thread']
            : config('services.telegram.thread');

        $threadId = (is_numeric($threadId)) ? (string) $threadId : null;

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
