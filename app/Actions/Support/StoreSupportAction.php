<?php

declare(strict_types=1);

namespace App\Actions\Support;

use App\DTOs\SupportData;
use App\Enums\SupportStatusEnum;
use App\Models\Support;
use App\Models\User;
use App\Notifications\SupportMessageReceivedNotification;
use App\DTOs\SupportContactData;
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

        // Notifica Admins (Email/Database)
        $admins = User::where('role', \App\Enums\RoleEnum::SUPER_ADMIN)->get();

        // Reutiliza a notificação existente ou cria uma nova específica para o model Support
        // Vamos usar a SupportMessageReceivedNotification adaptada
        Notification::send($admins, new \App\Notifications\SupportMessageReceivedNotification(
            new SupportContactData(
                name: $user->name,
                email: $user->email,
                subject: "Novo Ticket: " . $support->subject,
                message: $support->message
            )
        ));

        return $support;
    }
}
