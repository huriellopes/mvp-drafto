@props(['disabled' => false])

@php
    $isPassword = $attributes->get('type') === 'password';
@endphp

<div @if($isPassword) x-data="{ show: false }" class="relative" @endif>
    <input 
        @disabled($disabled) 
        {{ $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm w-full']) }}
        @if($isPassword) :type="show ? 'text' : 'password'" @endif
    >

    @if($isPassword)
        <button
            type="button"
            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 focus:outline-none"
            x-on:click="show = !show"
        >
            <x-lucide-eye x-show="!show" class="h-4 w-4" />
            <x-lucide-eye-off x-show="show" class="h-4 w-4" x-cloak />
        </button>
    @endif
</div>
