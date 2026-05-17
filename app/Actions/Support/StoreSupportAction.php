<?php

declare(strict_types=1);

namespace App\Actions\Support;

use App\DTOs\SupportContactData;
use App\DTOs\SupportData;
use App\Enums\RoleEnum;
use App\Enums\SupportStatusEnum;
use App\Models\Support;
use App\Models\User;
use App\Notifications\SupportMessageReceivedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

final class StoreSupportAction
{
    public function exec(User $user, SupportData $data): Support
    {
        $support = Support::create([
            'user_id' => $user->id,
            'subject' => $data->subject,
            'message' => $data->message,
            'status' => SupportStatusEnum::PENDING,
        ]);

        // Notifica o Suporte via Email
        $supportEmail = config('support.email', 'support@drafto.pro');

        Notification::route('mail', $supportEmail)
            ->notify(new SupportMessageReceivedNotification(
                new SupportContactData(
                    name: $user->name,
                    email: $user->email,
                    subject: 'Novo Ticket: ' . $support->subject,
                    message: $support->message,
                ),
            ));

        // Mantém a notificação interna para os Admins (opcional, mas comum para dashboard)
        $admins = User::where('role', RoleEnum::SUPER_ADMIN)->get();
        Notification::send($admins, new SupportMessageReceivedNotification(
            new SupportContactData(
                name: $user->name,
                email: $user->email,
                subject: 'Novo Ticket: ' . $support->subject,
                message: $support->message,
            ),
        ));

        // Sênior: Notifica o novo ticket no canal de suporte do Telegram
        Log::channel('telegram_support')->info("🎫 Novo ticket de suporte aberto por **{$user->name}**. \nAssunto: {$support->subject}");

        return $support;
    }
}
