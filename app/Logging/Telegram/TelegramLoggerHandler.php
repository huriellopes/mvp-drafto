<?php

declare(strict_types=1);

namespace App\Logging\Telegram;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

final class TelegramLoggerHandler extends AbstractProcessingHandler
{
    protected string $token;

    protected string $chatId;

    protected ?string $threadId;

    public function __construct(
        string $token,
        string $chatId,
        ?string $threadId = null,
        $level = Level::Error,
    ) {
        parent::__construct($level);
        $this->token = $token;
        $this->chatId = $chatId;
        $this->threadId = $threadId;
    }

    protected function write(LogRecord $record): void
    {
        try {
            $message = $this->formatMessage($record);

            $data = [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ];

            if ($this->threadId) {
                $data['message_thread_id'] = $this->threadId;
            }

            $response = Http::timeout(5)
                ->withoutVerifying()
                ->post("https://api.telegram.org/bot{$this->token}/sendMessage", $data);

            if ($response->failed()) {
                Log::channel('daily')->error('🚨 ERRO API TELEGRAM: ' . $response->body(), [
                    'chat_id' => $this->chatId,
                    'thread_id' => $this->threadId,
                    'status' => $response->status(),
                ]);
            }

        } catch (\Throwable $e) {
            Log::channel('daily')->error(
                'FALHA AO ENVIAR PRO TELEGRAM: ' . $e->getMessage(),
            );
        }
    }

    private function formatMessage(LogRecord $record): string
    {
        $emoji = match ($record->level) {
            Level::Debug => '🔍',
            Level::Info => 'ℹ️',
            Level::Notice => '📝',
            Level::Warning => '⚠️',
            Level::Error => '🚨',
            Level::Critical => '🔥',
            Level::Alert => '🔔',
            Level::Emergency => '🆘',
            default => '🛠️',
        };

        $levelName = strtoupper($record->level->name);
        $env = config('app.env');
        $url = request()->fullUrl();
        $method = request()->method();
        $ip = request()->ip();

        $user = Auth::user() ? '#' . Auth::id() . ' - ' . Auth::user()->name : 'Guest';

        $errorMessage = mb_substr($record->message, 0, 1000);

        return "<b>{$emoji} Drafto [{$levelName}] - ({$env})</b>\n\n" .
            "<b>📍 Rota:</b> {$method} {$url}\n" .
            "<b>👤 User:</b> {$user}\n" .
            "<b>🌐 IP:</b> {$ip}\n\n" .
            "<b>💬 Mensagem:</b>\n<pre>{$errorMessage}</pre>\n\n" .
            '<i>Horário: ' . now()->format('d/m/Y H:i:s') . '</i>';
    }
}
