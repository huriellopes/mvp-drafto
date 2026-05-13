@props(['sub'])

@php
    $colorMap = [
        'active' => 'green',
        'trialing' => 'indigo',
        'past_due' => 'red',
        'canceled' => 'gray',
    ];
    $color = $colorMap[$sub->stripe_status] ?? 'gray';
@endphp

<tr wire:key="sub-{{ $sub->id }}" class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/50 transition">
    <td class="px-6 py-4">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center font-bold text-zinc-400">
                {{ substr($sub->user->name, 0, 1) }}
            </div>
            <div>
                <p class="font-bold text-zinc-900 dark:text-white">{{ $sub->user->name }}</p>
                <p class="text-[10px] text-zinc-400 font-mono">{{ $sub->user->email }}</p>
            </div>
        </div>
    </td>
    <td class="px-6 py-4">
        <div class="flex flex-col">
            <span class="text-xs font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400">{{ $sub->type }}</span>
            <span class="text-[10px] text-zinc-400 italic">ID: {{ $sub->stripe_id }}</span>
        </div>
    </td>
    <td class="px-6 py-4">
        <x-ui.badge :label="$sub->stripe_status" :color="$color" />
    </td>
    <td class="px-6 py-4">
        <div class="flex flex-col text-xs text-zinc-500">
            <span>Desde: {{ $sub->created_at->format('d/m/Y') }}</span>
            @if($sub->ends_at)
                <span class="text-red-400 font-bold">Expira: {{ $sub->ends_at->format('d/m/Y') }}</span>
            @else
                <span class="text-zinc-400 italic">Renova mensalmente</span>
            @endif
        </div>
    </td>
    <td class="px-6 py-4 text-right">
        <div class="flex justify-end gap-2">
            <button 
                wire:click="showDetails({{ $sub->id }})"
                class="h-9 w-9 flex items-center justify-center rounded-xl text-zinc-400 hover:bg-zinc-100 hover:text-zinc-900 transition"
                title="Ver Detalhes"
            >
                <x-lucide-eye class="h-4 w-4" />
            </button>

            @if($sub->active())
                <button 
                    wire:click="confirmCancel({{ $sub->id }})"
                    class="h-9 w-9 flex items-center justify-center rounded-xl text-zinc-400 hover:bg-red-50 hover:text-red-600 transition"
                    title="Bloquear/Cancelar Agora"
                >
                    <x-lucide-ban class="h-4 w-4" />
                </button>
            @elseif($sub->onGracePeriod())
                <button 
                    wire:click="resumeSubscription({{ $sub->id }})"
                    class="h-9 w-9 flex items-center justify-center rounded-xl text-zinc-400 hover:bg-emerald-50 hover:text-emerald-600 transition"
                    title="Reativar Assinatura"
                >
                    <x-lucide-play class="h-4 w-4" />
                </button>
            @endif

            <a 
                href="https://dashboard.stripe.com/subscriptions/{{ $sub->stripe_id }}" 
                target="_blank"
                class="h-9 w-9 flex items-center justify-center rounded-xl text-zinc-400 hover:bg-zinc-100 hover:text-zinc-900 transition"
                title="Ver no Stripe"
            >
                <x-lucide-external-link class="h-4 w-4" />
            </a>
        </div>
    </td>
</tr>
