<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Module;
use App\Models\User;

class UserObserver
{
    /**
     * Executado após o usuário ser criado no banco.
     */
    public function created(User $user): void
    {
        $this->syncModules($user);
    }

    /**
     * Executado sempre que o usuário for atualizado.
     */
    public function updated(User $user): void
    {
        // Só re-sincroniza se a coluna 'role' mudou
        if ($user->isDirty(['role'])) {
            $this->syncModules($user);
        }
    }

    /**
     * Lógica centralizada de provisionamento de módulos.
     * Como a plataforma é gratuita, todos os módulos habilitados globalmente são ativados para o usuário.
     */
    private function syncModules(User $user): void
    {
        $modules = Module::query()
            ->where('is_enabled', true)
            ->get();

        $syncData = [];

        foreach ($modules as $module) {
            $globalSettings = $module->settings ?? [];

            // Usamos as configurações globais do módulo como padrão
            $userSettings = $globalSettings;

            $syncData[$module->id] = [
                'is_enabled' => true,
                'settings' => json_encode($userSettings),
            ];
        }

        $user->modules()->sync($syncData);
    }
}
