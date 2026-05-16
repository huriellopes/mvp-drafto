@props([
    'value' => '',
    'label' => null,
    'error' => null,
])

<div
    x-data="{
        value: @entangle($attributes->wire('model')),
        getContent() {
            return this.$refs.editable.innerHTML;
        },
        setContent(html) {
            this.$refs.editable.innerHTML = html || '';
        }
    }"
    x-init="
        setContent(value);
        $watch('value', v => {
            if (v !== getContent()) setContent(v);
        });
    "
    class="space-y-2"
>
    @if($label)
        <label class="text-sm font-bold text-zinc-700">{{ $label }}</label>
    @endif

    <div
        x-ref="editable"
        contenteditable="true"
        x-on:input="value = getContent()"
        x-on:blur="value = getContent()"
        {{ $attributes->except(['wire:model', 'label', 'error'])->class([
            'block w-full rounded-2xl border-zinc-200 bg-white p-4 text-sm text-zinc-600 focus:border-indigo-500 focus:ring-indigo-500 min-h-[120px] outline-none transition-all border',
            'border-red-300' => $error,
        ]) }}
    ></div>

    @if($error)
        <p class="text-xs font-bold text-red-500 mt-1">{{ $error }}</p>
    @endif
</div>
