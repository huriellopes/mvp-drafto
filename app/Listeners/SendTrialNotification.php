<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Mail\TrialStartedNotification;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;

class SendTrialNotification
{
    public function handle(Registered $event): void
    {
        $user = $event->user;

        if (!$user instanceof User) {
            return;
        }

        // Sênior: Garantimos que o e-mail de trial seja enviado apenas UMA VEZ
        // Mesmo que o evento Registered seja disparado múltiplas vezes por algum erro de sistema
        if ($user->onTrial() && is_null($user->trial_notification_sent_at)) {
            // Marcamos como enviado ANTES de enfileirar para evitar race conditions em disparos ultra-rápidos
            $user->update(['trial_notification_sent_at' => now()]);

            Mail::to($user->email)->queue(new TrialStartedNotification($user));
        }
    }
}
