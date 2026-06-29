@use(App\Enums\ReportReasonEnum)
<div
    x-data="{ open: @entangle('show').live }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[110] flex items-center justify-center p-4"
    wire:ignore.self
    x-on:keydown.escape.window="open = false"
>
    <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm"
         x-on:click="open = false"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"></div>

    <div role="dialog" aria-modal="true" aria-labelledby="report-modal-title"
         class="relative w-full max-w-lg rounded-[2.5rem] bg-white dark:bg-zinc-900 p-8 shadow-2xl animate-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between mb-8">
            <div class="space-y-1">
                <h3 id="report-modal-title" class="text-2xl font-black text-zinc-900 dark:text-white tracking-tighter">
                    @if(in_array($form->reason, [ReportReasonEnum::PRAISE->value, ReportReasonEnum::SUGGESTION->value]))
                        Enviar <span class="text-profile-primary">Feedback</span>
                    @elseif($form->reason === ReportReasonEnum::BUG->value)
                        Relatar <span class="text-purple-500">Problema</span>
                    @else
                        Reportar <span class="text-red-500">Conteúdo</span>
                    @endif
                </h3>
                <p class="text-xs text-zinc-500 font-medium italic">Sua mensagem ajuda a tornar a Drafto melhor.</p>
            </div>
            <x-ui.tooltip text="Fechar">
                <button @click="open = false" type="button" aria-label="Fechar" class="h-10 w-10 flex items-center justify-center rounded-xl bg-zinc-50 dark:bg-zinc-800 text-zinc-400 hover:text-zinc-600 transition">
                    <x-lucide-x class="h-5 w-5" aria-hidden="true"/>
                </button>
            </x-ui.tooltip>
        </div>

        <form wire:submit.prevent="submit" class="space-y-6">
            <x-ui.select
                wire:model.live="form.reason"
                label="Qual o motivo do seu contato?"
                :error="$errors->first('form.reason')"
                @class([
                    '!rounded-2xl',
                    '!border-red-500 !ring-4 !ring-red-500/10' => $errors->has('form.reason'),
                    'focus:!ring-4 focus:!ring-profile-primary/10 focus:!border-profile-primary' => !$errors->has('form.reason')
                ])
            >
                @foreach(ReportReasonEnum::cases() as $reason)
                    <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                @endforeach
            </x-ui.select>

            <div>
                <x-ui.textarea
                    label="Detalhes adicionais"
                    wire:model="form.description"
                    rows="4"
                    @class([
                        '!rounded-[2rem] !p-5 !ring-offset-0',
                        '!border-red-500 !ring-4 !ring-red-500/10' => $errors->has('form.description'),
                        'focus:!ring-4 focus:!ring-profile-primary/10 focus:!border-profile-primary' => !$errors->has('form.description')
                    ])
                    placeholder="Conte-nos mais detalhes para podermos ajudar..."
                    :error="$errors->first('form.description')"
                ></x-ui.textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <x-ui.button variant="ghost" @click="open = false" class="!rounded-xl text-[10px] uppercase font-black tracking-widest">
                    Cancelar
                </x-ui.button>
                <x-ui.button
                    type="submit"
                    loading="submit"
                    variant="{{ in_array($form->reason, [ReportReasonEnum::PRAISE->value, ReportReasonEnum::SUGGESTION->value]) ? 'dark' : ($form->reason === ReportReasonEnum::BUG->value ? 'dark' : 'danger') }}"
                    @class([
                        '!w-auto px-10 border-none shadow-xl transition-all font-black uppercase text-[10px] tracking-widest !rounded-xl',
                        '!bg-profile-primary hover:!bg-profile-accent shadow-profile-primary/20 !text-[var(--profile-primary-text)]' => in_array($form->reason, [ReportReasonEnum::PRAISE->value, ReportReasonEnum::SUGGESTION->value]),
                        'bg-purple-600 shadow-purple-500/20 text-white' => $form->reason === ReportReasonEnum::BUG->value,
                    ])
                >
                    {{ in_array($form->reason, [ReportReasonEnum::PRAISE->value, ReportReasonEnum::SUGGESTION->value]) ? 'Enviar agora' : 'Reportar agora' }}
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
