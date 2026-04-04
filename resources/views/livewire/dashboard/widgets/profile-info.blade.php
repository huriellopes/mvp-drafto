<x-ui.section-card
    title="Seu espaço"
    description="Resumo da sua conta."
>
    <div class="space-y-4">
        <x-ui.info-card
            title="Perfil público"
            :value="$this->user->profile->handle"
        />

        <x-ui.info-card
            title="E-mail"
            :value="$this->user->email"
        />

        <x-ui.info-card
            title="Papel"
            :value="$this->user->role->label()"
        />

        <div class="mt-6 pt-6 border-t border-zinc-100">
            <a href="#" class="text-sm font-semibold text-zinc-900 hover:underline flex items-center gap-2">
                <x-lucide-settings class="h-4 w-4" />
                Configurações da conta
            </a>
        </div>
    </div>
</x-ui.section-card>
