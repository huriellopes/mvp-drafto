<x-ui.section-card
    title="Seu espaço"
    description="Resumo da sua conta."
>
    <div class="space-y-4">
        <x-ui.info-card
            title="Perfil público"
            :value="$this->user->profile->username ? '@' . $this->user->profile->username : 'Não configurado'"
        />

        <x-ui.info-card
            title="E-mail"
            :value="$this->user?->profile?->email ?? $this->user->email"
        />

        <div class="flex items-center justify-between p-4 rounded-2xl bg-zinc-50 border border-zinc-100">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-zinc-400">Nível de Acesso</p>
                <p class="text-sm font-bold text-zinc-900">{{ $this->user->role->label() }}</p>
            </div>
            <x-lucide-shield-check class="h-5 w-5 text-profile-primary" />
        </div>

        <div class="mt-6 pt-6 border-t border-zinc-100">
            <a href="{{ route('dashboard.account') }}" wire:navigate.hover class="text-sm font-bold text-zinc-900 hover:text-profile-primary transition flex items-center gap-2">
                <x-lucide-settings class="h-4 w-4" />
                Configurações da conta
            </a>
        </div>
    </div>
</x-ui.section-card>
