@use(App\Enums\ReportReasonEnum)
<div x-data="{ open: @entangle('show') }" x-show="open" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center p-4" wire:ignore.self>
    <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm" @click="open = false"></div>

    <div class="relative w-full max-w-lg rounded-[2.5rem] bg-white dark:bg-zinc-900 p-8 shadow-2xl animate-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between mb-8">
            <div class="space-y-1">
                <h3 class="text-2xl font-black text-zinc-900 dark:text-white tracking-tighter">
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
            <button @click="open = false" class="h-10 w-10 flex items-center justify-center rounded-xl bg-zinc-50 dark:bg-zinc-800 text-zinc-400 hover:text-zinc-600 transition">
                <x-lucide-x class="h-5 w-5"/>
            </button>
        </div>

        <form wire:submit.prevent="submit" class="space-y-6">
            <x-ui.select wire:model.live="form.reason" label="Qual o motivo do seu contato?" :error="$errors->first('form.reason')">
                @foreach(ReportReasonEnum::cases() as $reason)
                    <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                @endforeach
            </x-ui.select>

            <div>
                <label class="block text-sm font-bold text-zinc-700 dark:text-zinc-300 mb-2 uppercase tracking-widest text-[10px]">Detalhes adicionais</label>
                <textarea wire:model="form.description" rows="4"
                          class="w-full rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-4 text-sm outline-none focus:ring-2 focus:ring-profile-primary/20 focus:border-profile-primary transition dark:text-white placeholder:text-zinc-400"
                          placeholder="Conte-nos mais detalhes..."></textarea>
                @error('form.description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-zinc-100 dark:border-zinc-800">
                <button type="button" @click="open = false" class="px-6 py-3 rounded-xl text-zinc-500 font-bold hover:text-zinc-900 transition">Cancelar</button>
                <x-ui.button type="submit" loading="submit" @class([
                    '!w-auto px-8 !rounded-xl border-none shadow-lg',
                    'bg-profile-primary shadow-profile-primary/20' => in_array($form->reason, [ReportReasonEnum::PRAISE->value, ReportReasonEnum::SUGGESTION->value]),
                    'bg-purple-600 shadow-purple-500/20' => $form->reason === ReportReasonEnum::BUG->value,
                    'bg-red-600 shadow-red-500/20' => !in_array($form->reason, [ReportReasonEnum::PRAISE->value, ReportReasonEnum::SUGGESTION->value, ReportReasonEnum::BUG->value]),
                ])>
                    {{ in_array($form->reason, [ReportReasonEnum::PRAISE->value, ReportReasonEnum::SUGGESTION->value]) ? 'Enviar agora' : 'Reportar agora' }}
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
