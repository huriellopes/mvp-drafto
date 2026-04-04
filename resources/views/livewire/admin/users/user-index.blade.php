@use(App\Enums\RoleEnum)
@use(App\Enums\UserStatusEnum)
<div class="space-y-6">

    {{-- Header de Ações --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-1 items-center gap-3">
            <x-ui.input
                wire:model.live.debounce.300ms="search"
                placeholder="Buscar usuário..."
                class="max-w-md"
                type="search"
            />

            <x-ui.select wire:model.live="role" class="w-48">
                <option value="">Todos os papéis</option>

                @foreach(RoleEnum::options() as $role)
                    <option value="{{ $role['value'] }}">{{ $role['label'] }}</option>
                @endforeach
            </x-ui.select>
        </div>

        <x-ui.button wire:click="openCreateModal" class="sm:w-auto">
            <x-lucide-user-plus class="mr-2 h-4 w-4" />
            Novo Usuário
        </x-ui.button>
    </div>

    {{-- Tabela de Usuários --}}
    <x-ui.table>
        <x-slot:header>
            <th class="px-6 py-4">Usuário</th>
            <th class="px-6 py-4">Papel</th>
            <th class="px-6 py-4">Status</th>
            <th class="px-6 py-4">Último Login</th>
            <th class="px-6 py-4 text-right">Ações</th>
        </x-slot:header>

        @forelse($this->users as $user)
            <tr wire:key="user-row-{{ $user->id }}" class="hover:bg-zinc-50/50 transition">
                <td class="px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="h-10 w-10 rounded-full bg-zinc-100 flex items-center justify-center font-bold text-zinc-500">
                            {{ substr($user->name, 0, 1) }}
                        </div>
                        <div>
                            <p class="font-medium text-zinc-900">{{ $user->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 text-zinc-600 text-xs">
                    {{ $user->role->label() }}
                </td>
                <td class="px-6 py-4">
                    <button
                        wire:click="toggleStatus({{ $user->id }})"
                        wire:loading.attr="disabled"
                        wire:target="toggleStatus({{ $user->id }})"
                        class="group flex items-center gap-2 transition active:scale-95 disabled:opacity-50"
                    >
                        <span @class([
                            'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium transition',
                            'bg-emerald-50 text-emerald-700' => $user->status === UserStatusEnum::ACTIVE,
                            'bg-zinc-100 text-zinc-700' => $user->status === UserStatusEnum::PENDING,
                            'bg-amber-50 text-amber-700' => $user->status === UserStatusEnum::SUSPENDED,
                            'bg-red-50 text-red-700' => $user->status === UserStatusEnum::BLOCKED,
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
                    {{ $user->last_login_at?->diffForHumans() ?? 'Nunca' }}
                </td>
                <td class="px-6 py-4 text-right">
                    <div class="flex justify-end gap-2">
                        <button wire:click="edit({{ $user->id }})" class="p-2 text-zinc-400 hover:text-zinc-900">
                            <x-lucide-pencil class="h-4 w-4" />
                        </button>
                        @if($user->id !== auth()->id())
                            <button
                                type="button"
                                wire:click="confirmUserDeletion({{ $user->id }})"
                                class="p-2 text-zinc-400 hover:text-red-600 transition"
                            >
                                <x-lucide-trash-2 class="h-4 w-4" />
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-12">
                    <x-ui.empty-state title="Nenhum usuário encontrado" description="Tente ajustar seus filtros de busca." />
                </td>
            </tr>
        @endforelse

        <x-slot:footer>
            {{ $this->users->links() }}
        </x-slot:footer>
    </x-ui.table>

    {{-- Modal de Formulário --}}
    <x-ui.modal name="user-form-modal" :title="$form->user ? 'Editar Usuário' : 'Novo Usuário'">
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

            <x-ui.input wire:model="form.password" label="Senha" type="password" placeholder="{{ $form->user ? 'Deixe em branco para manter' : '••••••••' }}" :error="$errors->first('form.password')" />

            <div class="mt-8 flex justify-end gap-3">
                <x-ui.button type="button" x-on:click="$dispatch('close-modal', { name: 'user-form-modal' })" class="bg-white !text-gray-200 border border-zinc-200 hover:bg-zinc-50">
                    Cancelar
                </x-ui.button>
                <x-ui.button loading="save">
                    Salvar Usuário
                </x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <x-ui.confirm-modal
        name="confirm-user-deletion"
        title="Excluir Usuário"
        content="Tem certeza que deseja remover este usuário? Esta ação não pode ser desfeita e todos os dados vinculados poderão ser afetados."
        buttonText="Excluir Permanentemente"
        variant="danger"
        action="delete"
    />
</div>
