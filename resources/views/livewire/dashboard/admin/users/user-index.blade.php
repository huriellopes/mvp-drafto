@use(App\Enums\RoleEnum)
@use(App\Enums\UserStatusEnum)
<div class="space-y-6">
    {{ Breadcrumbs::render('dashboard.users.index') }}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-zinc-900 dark:text-white leading-tight">{{ __('dashboard.admin.users.title') }}</h2>
        <p class="text-sm text-zinc-500 dark:text-zinc-400">{{ __('dashboard.admin.users.subtitle') }}</p>
    </div>

    {{-- Header de Ações --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white dark:bg-zinc-900 p-4 rounded-3xl border border-zinc-200 dark:border-zinc-800 shadow-sm">
        <div class="flex flex-1 items-center gap-3">
            <div class="w-full md:w-80">
                <x-ui.input
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('dashboard.admin.users.search_placeholder') }}"
                    type="search"
                >
                    <x-slot:prefix><x-lucide-search class="h-4 w-4 text-zinc-400" /></x-slot:prefix>
                </x-ui.input>
            </div>

            <x-ui.select wire:model.live="role">
                <option value="">{{ __('dashboard.admin.users.all_roles') }}</option>
                @foreach(RoleEnum::assignableOptions() as $role)
                    <option value="{{ $role['value'] }}">{{ $role['label'] }}</option>
                @endforeach
            </x-ui.select>
        </div>

        <div class="flex items-center gap-3">
            <x-ui.button wire:click="export" wire:loading.attr="disabled" class="sm:w-auto px-6" variant="secondary">
                <x-lucide-download wire:loading.remove wire:target="export" class="mr-2 h-4 w-4" />
                <x-lucide-loader-2 wire:loading wire:target="export" class="mr-2 h-4 w-4 animate-spin" />
                {{ __('dashboard.admin.users.export_button') ?? 'Exportar Excel' }}
            </x-ui.button>

            <x-ui.button wire:click="openCreateModal" class="sm:w-auto">
                <x-lucide-user-plus class="mr-2 h-4 w-4" />
                {{ __('dashboard.admin.users.new_button') }}
            </x-ui.button>
        </div>
    </div>

    {{-- Tabela de Usuários --}}
    <div class="min-h-[600px] transition-all duration-300" wire:loading.class="opacity-60 pointer-events-none">
        <x-ui.table>
            <x-slot:header>
            <x-ui.table.th label="{{ __('dashboard.admin.users.table.user') }}" column="name" :sort="$sort" :direction="$direction" />
            <x-ui.table.th label="{{ __('dashboard.admin.users.table.role') }}" column="role" :sort="$sort" :direction="$direction" />
            <x-ui.table.th label="Verificado" />
            <x-ui.table.th label="{{ __('dashboard.admin.users.table.access') }}" />
            <x-ui.table.th label="{{ __('dashboard.admin.users.table.status') }}" column="status" :sort="$sort" :direction="$direction" />
            <x-ui.table.th label="{{ __('dashboard.admin.users.table.last_login') }}" column="last_login_at" :sort="$sort" :direction="$direction" />
            <th class="px-6 py-4 text-right">{{ __('dashboard.admin.users.table.actions') }}</th>
        </x-slot:header>

        @forelse($this->users as $user)
            <tr wire:key="user-row-{{ $user->id }}" class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/50 transition">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center font-bold text-zinc-500">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-bold text-zinc-900 dark:text-white">{{ $user->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-zinc-600 dark:text-zinc-400 text-xs italic">
                    {{ $user->role->label() }}
                </td>

                {{-- Coluna Verificado (Toggle) --}}
                <td class="px-6 py-4">
                    <button
                        wire:click="toggleVerification({{ $user->id }})"
                        @class([
                            'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2',
                            'bg-blue-600' => $user->profile?->is_verified,
                            'bg-zinc-200 dark:bg-zinc-700' => ! $user->profile?->is_verified,
                        ])
                    >
                        <span @class([
                            'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                            'translate-x-5' => $user->profile?->is_verified,
                            'translate-x-0' => ! $user->profile?->is_verified,
                        ])></span>

                        @if($user->isVerified())
                            <x-lucide-badge-check class="absolute -right-6 top-1 h-4 w-4 text-blue-500" />
                        @endif
                    </button>
                </td>

                {{-- Coluna Vitalício (Toggle) --}}
                <td class="px-6 py-4">
                    <button
                        wire:click="toggleLifetime({{ $user->id }})"
                        @class([
                            'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2',
                            'bg-indigo-600' => $user->is_lifetime,
                            'bg-zinc-200 dark:bg-zinc-700' => ! $user->is_lifetime,
                        ])
                    >
                        <span @class([
                            'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                            'translate-x-5' => $user->is_lifetime,
                            'translate-x-0' => ! $user->is_lifetime,
                        ])></span>

                        @if($user->is_lifetime)
                            <x-lucide-infinity class="absolute -right-6 top-1 h-4 w-4 text-indigo-500 animate-pulse" />
                        @endif
                    </button>
                </td>

                <td class="px-6 py-4">
                    <button
                        wire:click="toggleStatus({{ $user->id }})"
                        wire:loading.attr="disabled"
                        wire:target="toggleStatus({{ $user->id }})"
                        class="group flex items-center gap-2 transition active:scale-95 disabled:opacity-50"
                    >
                        <span @class([
                            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold transition',
                            'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' => $user->status === UserStatusEnum::ACTIVE,
                            'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-400' => $user->status === UserStatusEnum::PENDING,
                            'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400' => $user->status === UserStatusEnum::SUSPENDED,
                            'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400' => $user->status === UserStatusEnum::BLOCKED,
                        ])>
                            <x-lucide-refresh-cw
                                wire:loading
                                wire:target="toggleStatus({{ $user->id }})"
                                class="h-3 w-3 animate-spin"
                            />
                            <span wire:loading.remove wire:target="toggleStatus({{ $user->id }})" @class([
                                'h-1.5 w-1.5 rounded-full',
                                'bg-emerald-600' => $user->status === UserStatusEnum::ACTIVE,
                                'bg-zinc-600' => $user->status === UserStatusEnum::PENDING,
                                'bg-amber-600' => $user->status === UserStatusEnum::SUSPENDED,
                                'bg-red-600' => $user->status === UserStatusEnum::BLOCKED,
                            ])></span>
                            {{ $user->status->label() }}
                        </span>
                    </button>
                </td>
                <td class="px-6 py-4 text-xs text-zinc-500">
                    {{ $user->last_login_at?->diffForHumans() ?? __('dashboard.admin.users.table.never') }}
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                        <x-ui.tooltip text="Gerenciar Módulos">
                            <button
                                wire:click="manageModules({{ $user->id }})"
                                class="p-2 text-zinc-400 hover:text-indigo-600 transition"
                            >
                                <x-lucide-layout-grid class="h-4 w-4" />
                            </button>
                        </x-ui.tooltip>

                        <x-ui.tooltip text="Impersonar (Logar como)">
                            <button
                                wire:click="confirmImpersonation({{ $user->id }})"
                                class="p-2 text-zinc-400 hover:text-amber-600 transition"
                            >
                                <x-lucide-user-cog class="h-4 w-4" />
                            </button>
                        </x-ui.tooltip>
                        <x-ui.tooltip text="Editar Usuário">
                            <button
                                wire:click="edit({{ $user->id }})"
                                class="p-2 text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition"
                            >
                                <x-lucide-pencil class="h-4 w-4" />
                            </button>
                        </x-ui.tooltip>

                        @if($user->id !== auth()->id())
                            <x-ui.tooltip text="Excluir Usuário">
                                <button
                                    type="button"
                                    wire:click="confirmUserDeletion({{ $user->id }})"
                                    class="p-2 text-zinc-400 hover:text-red-600 transition"
                                >
                                    <x-lucide-trash-2 class="h-4 w-4" />
                                </button>
                            </x-ui.tooltip>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-12">
                    <x-ui.empty-state title="Nenhum usuário encontrado" description="Tente ajustar seus filtros de busca ou limpe os parâmetros de ordenação." />
                </td>
            </tr>
        @endforelse

        <x-slot:footer>
            {{ $this->users->links() }}
        </x-slot:footer>
    </x-ui.table>
    </div>

    {{-- Modal de Formulário --}}
    <x-ui.modal name="user-form-modal" :title="$form->user ? __('dashboard.admin.users.modal.edit_title') : __('dashboard.admin.users.modal.create_title')">
        <form wire:submit="save" class="space-y-4">
            <x-ui.input wire:model="form.name" label="Nome" :error="$errors->first('form.name')" />
            <x-ui.input wire:model="form.email" label="E-mail" type="email" :error="$errors->first('form.email')" />

            <div class="grid grid-cols-2 gap-4">
                <x-ui.select wire:model="form.role" label="Papel" :error="$errors->first('form.role')">
                    @foreach(RoleEnum::options() as $role)
                        <option value="{{ $role['value'] }}">{{ $role['label'] }}</option>
                    @endforeach
                </x-ui.select>

                <x-ui.select wire:model="form.status" label="Status" :error="$errors->first('form.status')">
                    @foreach(UserStatusEnum::options() as $status)
                        <option value="{{ $status['value'] }}">{{ $status['label'] }}</option>
                    @endforeach
                </x-ui.select>
            </div>

            <div class="p-4 bg-indigo-50 dark:bg-indigo-500/5 rounded-2xl border border-indigo-100 dark:border-indigo-500/10">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="h-8 w-8 rounded-lg bg-indigo-600 flex items-center justify-center text-white">
                            <x-lucide-infinity class="h-4 w-4" />
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-zinc-900 dark:text-white">Acesso Vitalício</h4>
                            <p class="text-[10px] text-zinc-500 leading-none">Ignora limites e assinaturas do Stripe</p>
                        </div>
                    </div>

                    <button
                        type="button"
                        wire:click="$set('form.is_lifetime', {{ $form->is_lifetime ? 'false' : 'true' }})"
                        @class([
                            'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none',
                            'bg-indigo-600' => $form->is_lifetime,
                            'bg-zinc-200 dark:bg-zinc-700' => ! $form->is_lifetime,
                        ])
                    >
                        <span @class([
                            'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out',
                            'translate-x-5' => $form->is_lifetime,
                            'translate-x-0' => ! $form->is_lifetime,
                        ])></span>
                    </button>
                </div>
            </div>

            <x-ui.input wire:model="form.password" label="Senha" type="password" placeholder="{{ $form->user ? __('dashboard.admin.users.modal.password_placeholder') : '••••••••' }}" :error="$errors->first('form.password')" />

            <div class="mt-8 flex justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <x-ui.button type="button" x-on:click="$dispatch('close-modal', { name: 'user-form-modal' })" variant="secondary" class="!w-auto px-8">
                    {{ __('dashboard.admin.users.modal.cancel') }}
                </x-ui.button>
                <x-ui.button loading="save" class="!w-auto px-10">
                    {{ __('dashboard.admin.users.modal.submit') }}
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <x-ui.confirm-modal
        name="confirm-impersonation"
        title="Confirmar Impersonação"
        content="Você está prestes a logar como {{ $selectedUserForImpersonation?->name }}. Esta ação será registrada para fins de auditoria. Deseja continuar?"
        buttonText="Sim, Logar como Usuário"
        variant="primary"
        action="impersonate"
    />

    <x-ui.confirm-modal
        name="confirm-user-deletion"
        title="{{ __('dashboard.admin.users.delete_modal.title') }}"
        content="{{ __('dashboard.admin.users.delete_modal.content') }}"
        buttonText="{{ __('dashboard.admin.users.delete_modal.confirm') }}"
        variant="danger"
        action="delete"
    />

    {{-- Modal de Módulos do Usuário --}}
    <x-ui.modal name="user-modules-modal" title="Gerenciar Módulos: {{ $selectedUserForModules?->name }}">
        <div class="space-y-4">
            <div class="flex items-start gap-3 p-4 bg-amber-50 dark:bg-amber-500/5 rounded-2xl border border-amber-100 dark:border-amber-500/10">
                <x-ui.tooltip text="Atenção: A configuração global prevalece">
                    <x-lucide-info class="h-5 w-5 text-amber-600 shrink-0" />
                </x-ui.tooltip>
                <p class="text-sm text-amber-800 dark:text-amber-200/70 leading-tight">
                    Ative ou desative funcionalidades específicas para este usuário.
                    <span class="block mt-1 font-bold italic opacity-70">Nota: Módulos desativados globalmente não ficarão disponíveis mesmo se ativos aqui.</span>
                </p>
            </div>

            <div class="grid gap-3">
                @if($selectedUserForModules)
                    @foreach($this->allModules as $module)
                        @php
                            $userPivot = $selectedUserForModules->modules->firstWhere('id', $module->id)?->pivot;
                            $isEnabledForUser = $userPivot ? (bool) $userPivot->is_enabled : false;
                        @endphp
                        <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-2xl border border-zinc-100 dark:border-zinc-700/50">
                            <div class="flex items-center gap-3">
                                <div @class([
                                    'h-10 w-10 rounded-xl flex items-center justify-center',
                                    'bg-zinc-900 text-white dark:bg-zinc-700' => $module->is_enabled,
                                    'bg-zinc-200 text-zinc-400' => !$module->is_enabled
                                ])>
                                    <x-dynamic-component :component="'lucide-'.$module->icon" class="h-5 w-5" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-zinc-900 dark:text-white text-sm">{{ $module->name }}</h4>
                                    <p class="text-[10px] text-zinc-500 uppercase font-black tracking-widest">{{ $module->slug->value }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                @if(!$module->is_enabled)
                                    <span class="text-[9px] font-black uppercase text-red-500 bg-red-50 dark:bg-red-500/10 px-2 py-0.5 rounded-full">Global OFF</span>
                                @endif

                                <button
                                    wire:click="toggleUserModule({{ $module->id }})"
                                    wire:loading.attr="disabled"
                                    @class([
                                        'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 transition-all duration-200 focus:outline-none',
                                        'bg-emerald-500 border-emerald-500 shadow-sm' => $isEnabledForUser,
                                        'bg-zinc-200 border-zinc-300 dark:bg-zinc-700 dark:border-zinc-600' => !$isEnabledForUser
                                    ])
                                >
                                    <span @class([
                                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200 ease-in-out',
                                        'translate-x-5' => $isEnabledForUser,
                                        'translate-x-0' => !$isEnabledForUser
                                    ])></span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>

        <div class="mt-8 flex justify-end pt-4 border-t border-zinc-100 dark:border-zinc-800">
            <x-ui.button type="button" x-on:click="$dispatch('close-modal', { name: 'user-modules-modal' })" variant="secondary" class="!w-auto px-8">
                Fechar
            </x-ui.button>
        </div>
    </x-ui.modal>
</div>
