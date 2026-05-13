<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
    <style>
        body { background: transparent; margin: 0; padding: 0; overflow: hidden; }
        :root {
            --profile-primary: {{ $profile->primary_color ?? '#18181b' }};
        }
    </style>
</head>
<body class="antialiased">
<div @class([
        'w-full h-screen p-8 transition-all relative overflow-hidden flex flex-col justify-center',
        'bg-zinc-950 text-white' => $theme === 'dark',
        'bg-white text-zinc-950 border border-zinc-100' => $theme === 'light',
        'bg-[var(--profile-primary)] text-white' => $theme === 'brand',
    ])>
    {{-- Logo Drafto Sutil --}}
    <div class="absolute top-6 right-8 opacity-20">
        <x-application-logo class="h-5 w-auto fill-current" />
    </div>

    <div class="flex items-center gap-6">
        <img src="{{ $profile->avatar_path ? Storage::url($profile->avatar_path) : 'https://ui-avatars.com/api/?name='.urlencode($user->name) }}"
             class="h-20 w-20 rounded-3xl object-cover ring-4 ring-white/10">
        <div class="space-y-1">
            <h3 class="text-2xl font-black tracking-tighter italic leading-tight">{{ $profile->name ?? $user->name }}</h3>
            <p class="text-sm font-bold opacity-60">@<span></span>{{ $profile->username }}</p>
        </div>
    </div>

    @if($profile->bio)
        <p class="mt-6 text-sm line-clamp-2 opacity-80 leading-relaxed italic font-medium">
            "{{ $profile->bio }}"
        </p>
    @endif

    <div class="mt-8 pt-6 border-t border-white/10 flex items-center justify-between">
        <div class="flex gap-4">
            <div class="text-center">
                <p class="text-lg font-black leading-none">{{ number_format($user->followers_count ?? $user->followers()->count()) }}</p>
                <p class="text-[8px] font-bold uppercase tracking-widest opacity-50">Seguidores</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-black leading-none">{{ number_format($user->published_posts_count ?? $user->posts()->published()->count()) }}</p>
                <p class="text-[8px] font-bold uppercase tracking-widest opacity-50">Posts</p>
            </div>
        </div>

        <a href="{{ route('profile.show', $profile->username) }}" target="_blank" class="flex items-center gap-2 px-4 py-2 bg-white/10 rounded-xl text-[10px] font-black uppercase tracking-widest no-underline text-current">
            Ler Perfil <x-lucide-arrow-right class="h-3 w-3" />
        </a>
    </div>
</div>
</body>
</html>
