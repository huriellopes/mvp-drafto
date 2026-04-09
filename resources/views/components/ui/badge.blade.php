@props([
    'label' => null,
    'color' => 'gray',
])

@php
    $colors = [
        'gray' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 border-zinc-200 dark:border-zinc-700',
        'red' => 'bg-red-50 text-red-700 dark:bg-red-900/20 dark:text-red-400 border-red-100 dark:border-red-800/30',
        'green' => 'bg-green-50 text-green-700 dark:bg-green-900/20 dark:text-green-400 border-green-100 dark:border-green-800/30',
        'blue' => 'bg-blue-50 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400 border-blue-100 dark:border-blue-800/30',
        'orange' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/20 dark:text-orange-400 border-orange-100 dark:border-orange-800/30',
        'purple' => 'bg-purple-50 text-purple-700 dark:bg-purple-900/20 dark:text-purple-400 border-purple-100 dark:border-purple-800/30',
        'indigo' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/20 dark:text-indigo-400 border-indigo-100 dark:border-indigo-800/30',
        'primary' => 'bg-indigo-600 text-white dark:bg-indigo-500 border-transparent',
    ];

    $classes = "inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-widest border " . ($colors[$color] ?? $colors['gray']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $label ?? $slot }}
</span>
