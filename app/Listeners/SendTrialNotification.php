<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Mail\TrialStartedNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Mail;
use App\Models\User;

class SendTrialNotification
{
    public function handle(Registered $event): void
    {
        $user = $event->user;

        if (!$user instanceof User) {
            return;
        }

        // Se o usuário está no trial (Escritor recém registrado), enviamos o e-mail
        if ($user->onTrial()) {
            Mail::to($user->email)->queue(new TrialStartedNotification($user));
        }
    }
}
