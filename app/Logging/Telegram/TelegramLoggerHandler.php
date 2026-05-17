<?php

declare(strict_types=1);

namespace App\Logging\Telegram;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Throwable;

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

        } catch (Throwable $e) {
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

        $levelName = mb_strtoupper($record->level->name);
        $env = mb_strtoupper(config('app.env'));

        $isConsole = app()->runningInConsole();

        $url = $isConsole ? 'CLI' : request()->fullUrl();
        $method = $isConsole ? 'COMMAND' : request()->method();
        $ip = $isConsole ? 'LOCAL' : request()->ip();
        $userAgent = $isConsole ? 'PHP CLI' : (request()->userAgent() ?? 'N/A');

        $user = Auth::user()
            ? '👤 <b>User:</b> #' . Auth::id() . ' - ' . Auth::user()->name . ' (' . Auth::user()->email . ")\n"
            : '👤 <b>User:</b> Guest/System';

        $message = "<b>{$emoji} DRAFTO ALERTA [{$levelName}]</b>\n";
        $message .= "<b>🌍 Ambiente:</b> <code>{$env}</code>\n\n";

        if ($isConsole) {
            $message .= "<b>💻 Execução:</b> <code>CLI COMMAND</code>\n";
        } else {
            $message .= "<b>📍 Endpoint:</b> <code>{$method} {$url}</code>\n";
        }

        $message .= "{$user}\n";
        $message .= "<b>🌐 IP:</b> <code>{$ip}</code>\n";
        $message .= "<b>🖥️ UA:</b> <code>{$userAgent}</code>\n\n";

        // Detalhes da Exceção (se houver)
        $exception = $record->context['exception'] ?? null;

        if ($exception instanceof Throwable) {
            $message .= '<b>❌ Erro:</b> <code>' . get_class($exception) . "</code>\n";
            $message .= '<b>📂 Arquivo:</b> <code>' . basename($exception->getFile()) . "</code> (Linha: {$exception->getLine()})\n\n";
        }

        $message .= "<b>💬 Mensagem:</b>\n<pre>{$record->message}</pre>\n\n";

        // Contexto Adicional
        $context = $record->context;
        unset($context['exception']);

        if (!empty($context)) {
            $jsonContext = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $message .= "<b>📦 Contexto:</b>\n<pre>{$jsonContext}</pre>\n\n";
        }

        // Input Data (Filtrando campos sensíveis)
        if (!$isConsole) {
            $inputs = request()->except(['password', 'password_confirmation', 'current_password', 'token', 'credit_card']);

            if (!empty($inputs) && request()->isMethodSafe() === false) {
                $jsonInput = json_encode($inputs, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                $message .= "<b>📥 Input:</b>\n<pre>{$jsonInput}</pre>\n\n";
            }
        }

        $message .= '<i>⏰ Gerado em: ' . now()->format('d/m/Y H:i:s') . '</i>';

        return $message;
    }
}
