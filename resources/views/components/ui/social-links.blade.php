@props([
    'context' => 'global'
])

<div {{ $attributes->merge(['class' => 'flex gap-6']) }}>
    @foreach(config('social.items', []) as $key => $social)
        @if($social['active'] && $social['url'])
            <a 
                href="{{ $social['url'] }}" 
                target="_blank" 
                rel="noopener noreferrer" 
                class="text-zinc-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-all duration-300 hover:scale-110 active:scale-95"
                data-tracking="dfto_click_social_{{ $key }}_{{ $context }}"
                title="{{ ucfirst($key) }}: {{ $social['handle'] }}"
            >
                <x-dynamic-component :component="'lucide-' . $social['icon']" class="h-5 w-5" />
            </a>
        @endif
    @endforeach
</div>
