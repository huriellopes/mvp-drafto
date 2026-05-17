<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

#[Signature('drafto:test-telegram')]
#[Description('Envia alertas de teste detalhados para o Telegram para validar o novo formatador.')]
final class TestTelegramAlerts extends Command
{
    public function handle(): void
    {
        $this->info('🚀 Iniciando testes de alertas do Telegram...');

        $this->info('Enviando alerta de INFO (Simulando Registro)...');
        Log::channel('telegram_support')->info('✅ Teste de Novo Usuário', [
            'user_id' => 999,
            'name' => 'Usuário de Teste',
            'email' => 'teste@drafto.com',
            'role' => 'Escritor',
            'ip_address' => '127.0.0.1',
        ]);

        $this->info('Enviando alerta de ERROR (Simulando Exceção)...');

        try {
            throw new Exception('Esta é uma exceção de teste para validar o formatador sênior!');
        } catch (Throwable $e) {
            Log::channel('telegram_alerts')->error('🚨 Falha Crítica de Teste', [
                'exception' => $e,
                'additional_info' => 'Este erro foi gerado propositalmente.',
            ]);
        }

        $this->info('✅ Todos os alertas de teste foram enviados!');
    }
}
