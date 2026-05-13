<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:expire-trials')]
#[Description('Remove o plano Pro de usuários cujo trial de 15 dias expirou')]
class ExpireTrialsCommand extends Command
{
    public function handle(): void
    {
        $this->info('Verificando trials expirados...');

        // Buscamos o ID do plano Free (ID 1, conforme configurado no config/plans.php)
        $freePlan = Plan::where('slug', 'free')->first();
        $freePlanId = $freePlan?->id;

        $users = User::query()
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->where('plan_id', 3) // Somente quem ainda está marcado como Pro Trial
            ->get();

        $count = 0;

        foreach ($users as $user) {
            // Sênior: Se o usuário já assinou via Stripe entre o registro e agora,
            // não removemos o plano, pois o Cashier deve gerenciar isso.
            if ($user->subscribed('pro') || $user->subscribed('plus')) {
                continue;
            }

            // Ao atualizar o plan_id, o UserObserver irá disparar o syncModules()
            // e o usuário perderá as funcionalidades Pro.
            $user->update([
                'plan_id' => $freePlanId,
                'trial_ends_at' => null, // Limpamos para não processar novamente
            ]);

            $count++;
        }

        $this->info("✔ {$count} usuários voltaram ao plano gratuito após expiração do trial.");
    }
}
