<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Engagement\SendReengagementEmailAction;
use App\Enums\UserStatusEnum;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('users:reengage')]
#[Description('Envia e-mails de retorno (win-back) para usuários inativos (sem logar e sem escrever) nas faixas de 15/30/60 dias.')]
class SendReengagementEmailsCommand extends Command
{
    public function handle(SendReengagementEmailAction $action): int
    {
        $this->info('Verificando usuários inativos...');

        $sent = 0;
        $scanned = 0;

        User::query()
            ->where('status', UserStatusEnum::ACTIVE)
            ->whereNotNull('email_verified_at')
            ->where('wants_reengagement_emails', true)
            ->where(fn ($q) => $q->whereNull('banned_until')->orWhere('banned_until', '<=', now()))
            ->withMax('posts', 'created_at')
            ->chunkById(200, function ($users) use ($action, &$sent, &$scanned) {
                foreach ($users as $user) {
                    $scanned++;

                    if ($action->exec($user)) {
                        $sent++;
                    }
                }
            });

        $this->info("Concluído. {$scanned} usuários verificados, {$sent} e-mails de retorno enfileirados.");

        return self::SUCCESS;
    }
}
