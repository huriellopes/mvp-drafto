<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

#[Signature('drafto:test-mail {email : Destinatário do e-mail de teste} {--mailer=contact : Mailer a usar (contact, support ou smtp)}')]
#[Description('Envia um e-mail de teste (síncrono) para validar a configuração SMTP em produção.')]
final class TestMailCommand extends Command
{
    private const ALLOWED_MAILERS = ['smtp', 'contact', 'support'];

    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $mailer = (string) $this->option('mailer');

        if (Validator::make(['email' => $email], ['email' => ['required', 'email']])->fails()) {
            $this->error("E-mail inválido: {$email}");

            return self::FAILURE;
        }

        if (!in_array($mailer, self::ALLOWED_MAILERS, true)) {
            $this->error("Mailer inválido: {$mailer}. Use: " . implode(', ', self::ALLOWED_MAILERS) . '.');

            return self::FAILURE;
        }

        $scheme = config("mail.mailers.{$mailer}.scheme") ?: '(não definido)';
        $host = config("mail.mailers.{$mailer}.host");
        $port = config("mail.mailers.{$mailer}.port");

        $this->info("Enviando via mailer '{$mailer}' ({$host}:{$port}, scheme={$scheme}) para {$email}...");

        try {
            Mail::mailer($mailer)->raw(
                "Este é um e-mail de teste do Drafto enviado pelo mailer '{$mailer}' em " . now()->format('d/m/Y H:i:s') . '.',
                function ($message) use ($email, $mailer): void {
                    $message->to($email)->subject("Drafto — teste de e-mail ({$mailer})");
                },
            );
        } catch (Throwable $e) {
            $this->error('Falha no envio: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info('✅ E-mail de teste enviado com sucesso.');

        return self::SUCCESS;
    }
}
