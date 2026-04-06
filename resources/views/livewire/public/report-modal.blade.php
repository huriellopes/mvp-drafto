@use(App\Enums\ReportReasonEnum)
<div x-data="{ open: @entangle('show') }" x-show="open" x-cloak class="fixed inset-0 z-[110] flex items-center justify-center p-4" wire:ignore.self>
    <div class="absolute inset-0 bg-zinc-900/60 backdrop-blur-sm" @click="open = false"></div>

    <div class="relative w-full max-w-lg rounded-4xl bg-white dark:bg-zinc-900 p-8 shadow-2xl animate-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-zinc-900 dark:text-white">Denunciar</h3>
            <button @click="open = false" class="text-zinc-400 hover:text-zinc-600"><x-lucide-x class="h-5 w-5"/></button>
        </div>

        <form wire:submit.prevent="submit" class="space-y-6">
            <x-ui.select wire:model="form.reason" label="Motivo" :error="$errors->first('form.reason')">
                @foreach(ReportReasonEnum::options() as $reason)
                    <option value="{{ $reason['value'] }}">{{ $reason['label'] }}</option>
                @endforeach
            </x-ui.select>

            <div>
                <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Descrição</label>
                <textarea wire:model="form.description" rows="4"
                          class="w-full rounded-2xl border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-950 p-4 text-sm outline-none focus:border-profile-primary transition dark:text-white"
                          placeholder="Detalhes..."></textarea>
                @error('form.description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" @click="open = false" class="px-6 py-3 rounded-xl text-zinc-500 font-bold">Cancelar</button>
                <x-ui.button loading="submit" class="bg-red-600 hover:brightness-110 border-none shadow-lg shadow-danger/20">
                    Enviar Denúncia
                </x-ui.button>
            </div>
        </form>
    </div>
</div>
