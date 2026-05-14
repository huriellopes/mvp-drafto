@props([
    'user',
    'mode' => 'profile', // 'profile' (with buttons) or 'embed' (for image/iframe)
    'theme' => 'light',  // 'light', 'dark', 'brand'
    'showStats' => true,
    'showBio' => true,
    'showLocation' => false,
])

@php
    $profile = $user->profile;
    
    $themeClasses = match($theme) {
        'dark' => 'bg-[#09090b] text-white border-white/10',
        'brand' => 'text-white border-white/20',
        default => 'bg-white text-zinc-900 border-zinc-100',
    };

    $brandStyle = $theme === 'brand' 
        ? "background: linear-gradient(135deg, {$profile->primary_color}, {$profile->accent_color});" 
        : "";
@endphp

<div {{ $attributes->merge(['class' => "group relative overflow-hidden rounded-[3.5rem] border p-10 shadow-sm backdrop-blur-sm transition-all hover:shadow-xl $themeClasses"]) }}
     @if($brandStyle) style="{{ $brandStyle }}" @endif
     id="{{ $mode === 'embed' ? 'badge-preview' : '' }}"
>
    {{-- Decorative Watermark (Only for embed) --}}
    @if($mode === 'embed')
        <div class="absolute -top-10 -right-10 opacity-[0.03] rotate-12 pointer-events-none">
            <x-application-logo class="h-64 w-auto fill-current" />
        </div>
        <div class="absolute top-10 right-10 opacity-30">
            <x-application-logo class="h-6 w-auto fill-current" />
        </div>
    @endif

    <div class="text-center space-y-6 relative z-10">
        {{-- Avatar Section --}}
        <div class="relative inline-block">
            <div class="absolute inset-0 bg-indigo-500/20 rounded-[2.5rem] blur-xl opacity-0 group-hover:opacity-100 transition duration-500"></div>
            
            @if($profile->avatar_path)
                <img src="{{ Storage::url($profile->avatar_path) }}"
                     class="relative mx-auto h-32 w-32 rounded-[2.5rem] object-cover shadow-2xl ring-4 ring-current/5 transition-transform duration-500 group-hover:scale-105"
                     alt="{{ $user->display_name }}"
                     crossorigin="anonymous"
                />
            @else
                <div @class([
                    "relative mx-auto h-32 w-32 rounded-[2.5rem] flex items-center justify-center text-3xl font-black tracking-widest shadow-2xl ring-4 ring-current/5 transition-transform duration-500 group-hover:scale-105",
                    $theme === 'light' ? 'bg-zinc-100 text-zinc-400' : 'bg-white/10 text-white/50'
                ])>
                    {{ get_initials($user->display_name) }}
                </div>
            @endif
        </div>

        {{-- Identity Section --}}
        <div class="space-y-1">
            <h4 class="text-2xl font-black tracking-tighter italic leading-tight">{{ $user->display_name }}</h4>
            <p @class([
                "text-xs font-bold uppercase tracking-widest italic",
                $theme === 'light' ? 'text-indigo-600' : 'opacity-60'
            ])>@<span></span>{{ $profile->username }}</p>

            @if($showLocation && $profile->location)
                <div @class([
                    "flex items-center justify-center gap-1.5 text-[10px] font-bold uppercase tracking-widest mt-2",
                    $theme === 'light' ? 'text-zinc-400' : 'opacity-40'
                ])>
                    <x-lucide-map-pin class="h-3 w-3" />
                    <span>{{ $profile->location }}</span>
                </div>
            @endif
        </div>

        {{-- Bio Section --}}
        @if($showBio && $profile->bio)
            <p class="text-sm leading-relaxed font-medium italic opacity-80 line-clamp-2">
                "{{ $profile->bio }}"
            </p>
        @endif

        {{-- Stats Section (Only for embed/customized) --}}
        @if($showStats)
            <div @class([
                "pt-6 mt-6 border-t flex items-center justify-center gap-10",
                $theme === 'light' ? 'border-zinc-100' : 'border-white/10'
            ])>
                <div class="space-y-1">
                    <p class="text-2xl font-black leading-none tracking-tighter">{{ number_format($user->followers()->count()) }}</p>
                    <p class="text-[9px] font-black uppercase tracking-[0.2em] opacity-40">Leitores</p>
                </div>
                <div class="space-y-1">
                    <p class="text-2xl font-black leading-none tracking-tighter">{{ number_format($user->posts()->published()->count()) }}</p>
                    <p class="text-[9px] font-black uppercase tracking-[0.2em] opacity-40">Obras</p>
                </div>
            </div>
        @endif

        {{-- Actions / Footer --}}
        @if($mode === 'profile')
            <div class="flex items-center gap-3 pt-4">
                <a href="{{ route('profile.show', $profile->username) }}" 
                   wire:navigate
                   class="flex-1 inline-flex items-center justify-center h-11 px-6 text-xs font-black uppercase tracking-widest rounded-2xl bg-indigo-600 border border-indigo-600 text-white shadow-sm transition-all hover:bg-indigo-700 active:scale-95"
                >
                    Ver Perfil
                </a>
                <livewire:actions.follow-button :user="$user" :iconOnly="true" :key="'badge-follow-'.$user->id"/>
            </div>
        @else
            <div class="pt-6 flex justify-center">
                <div @class([
                    "flex items-center gap-2 px-6 py-4 rounded-2xl text-[10px] font-black uppercase tracking-widest border shadow-inner",
                    $theme === 'light' ? 'bg-zinc-50 border-zinc-100' : 'bg-white/5 border-white/5'
                ])>
                    Acesse no Drafto <x-lucide-arrow-right class="h-3 w-3" />
                </div>
            </div>
        @endif
    </div>
</div>
