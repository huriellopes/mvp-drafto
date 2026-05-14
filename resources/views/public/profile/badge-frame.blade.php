@php
    $plan = $user->plan?->slug ?? 'free';
    $isLifetime = (bool) $user->is_lifetime;
    $isAdmin = $user->isAdmin();
    $onTrial = $user->onTrial();

    $isPrivileged = $isAdmin || $isLifetime;
    $isPro = $isPrivileged || ($plan === 'pro' && !$onTrial);
    $isPlus = $plan === 'plus' && !$onTrial;

    // A marca d'água de "Powered by" some para Premium, mas o LOGO da plataforma deve ficar.
    $showPoweredBy = !$isPrivileged && ($plan === 'free' || $onTrial);

    $colors = match($theme) {
        'light' => [
            'bg' => 'bg-white',
            'text' => 'text-zinc-900',
            'muted' => 'text-zinc-500',
            'accent' => $profile->primary_color,
            'border' => 'border-zinc-200',
            'card' => 'shadow-[0_40px_80px_-15px_rgba(0,0,0,0.1)]',
            'qr_bg' => '#f4f4f5',
            'qr_color' => '#000000',
            'logo' => 'text-zinc-900'
        ],
        'brand' => [
            'bg' => '',
            'text' => 'text-white',
            'muted' => 'text-white/70',
            'accent' => '#ffffff',
            'border' => 'border-white/20',
            'card' => 'shadow-[0_40px_80px_-15px_rgba(0,0,0,0.3)]',
            'qr_bg' => '#ffffff',
            'qr_color' => $profile->primary_color,
            'logo' => 'text-white'
        ],
        default => [ // dark
            'bg' => 'bg-[#09090b]',
            'text' => 'text-white',
            'muted' => 'text-zinc-400',
            'accent' => $profile->primary_color,
            'border' => 'border-white/10',
            'card' => 'shadow-[0_40px_80px_-15px_rgba(0,0,0,0.6)]',
            'qr_bg' => '#18181b',
            'qr_color' => '#ffffff',
            'logo' => 'text-white'
        ]
    };
@endphp

<!DOCTYPE html>
<html lang="pt_BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400;0,700;1,800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
        .glass-effect { backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); }
        .brand-gradient { background: linear-gradient(135deg, {{ $profile->primary_color }} 0%, {{ $profile->accent_color }} 100%); }
        .noise-bg { background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E"); }
        .logo-bright { filter: brightness(0) invert(1); -webkit-filter: brightness(0) invert(1); }
    </style>
</head>
<body class="bg-transparent antialiased h-full flex items-center justify-center">
    <div id="badge-container"
         @class([
            "relative w-[480px] overflow-hidden rounded-[3rem] p-10 transition-all duration-700 border",
            $colors['bg'], $colors['text'], $colors['border'], $colors['card'],
            'brand-gradient' => $theme === 'brand'
         ])
         @if($theme === 'brand') style="background: linear-gradient(135deg, {{ $profile->primary_color }}, {{ $profile->accent_color }})" @endif>

        {{-- Grainy Noise Overlay --}}
        <div class="absolute inset-0 noise-bg opacity-[0.03] pointer-events-none"></div>

        {{-- Header: Logo & Status --}}
        <div class="relative z-10 flex items-center justify-between mb-12">
            <div class="{{ $colors['logo'] }} flex items-center gap-2">
                <img src="{{ asset('images/favicon/android-chrome-192x192.png') }}" alt="Drafto" @class(['h-7 w-auto', 'logo-bright' => $theme !== 'light']) />
                <span class="text-sm font-black uppercase tracking-[0.2em] italic opacity-90">Drafto</span>
            </div>

            <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-current/5 border border-current/10 glass-effect">
                <div @class([
                    'h-1.5 w-1.5 rounded-full shadow-[0_0_8px_rgba(34,197,94,0.5)]',
                    'bg-green-500 animate-pulse' => !$onTrial,
                    'bg-amber-500' => $onTrial
                ])></div>
                <span class="text-[8px] font-black uppercase tracking-widest opacity-80">
                    {{ $isPrivileged ? 'Verified Member' : ($onTrial ? 'Trial Access' : 'Escritor ' . ($plan === 'free' ? 'Ativo' : $user->plan->name)) }}
                </span>
            </div>
        </div>

        {{-- Main Info --}}
        <div class="relative z-10 flex flex-col items-center text-center mb-10">
            <div class="relative mb-6">
                <div class="absolute -inset-4 bg-current opacity-[0.05] blur-3xl rounded-full"></div>
                <div class="relative h-32 w-32 rounded-full overflow-hidden ring-[8px] ring-current/5 shadow-2xl transition-transform hover:scale-105 duration-500">
                    <img
                        src="{{ $profile->avatar_path ? Storage::url($profile->avatar_path) : 'https://ui-avatars.com/api/?name='.$user->name.'&background=random&color=fff' }}"
                        class="h-full w-full object-cover"
                        alt="{{ $user->name }}"
                    />
                </div>
                @if($user->isVerified())
                    <div class="absolute -bottom-1 -right-1 bg-white dark:bg-zinc-950 rounded-full p-1.5 shadow-xl border border-zinc-100 dark:border-zinc-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23.33 10.11l-2-2.18 0.44-2.92c0.06-0.38-0.16-0.76-0.51-0.9l-2.73-1.12-1.39-2.61c-0.18-0.34-0.56-0.51-0.93-0.42l-2.84 0.65-2.22-1.89c-0.3-0.26-0.74-0.26-1.04 0l-2.22 1.89-2.84-0.65c-0.37-0.09-0.75 0.08-0.93 0.42l-1.39 2.61-2.73 1.12c-0.35 0.14-0.57 0.52-0.51 0.9l0.44 2.92-2 2.18c-0.26 0.29-0.26 0.73 0 1.02l2 2.18-0.44 2.92c-0.06 0.38 0.16 0.76 0.51 0.9l2.73 1.12 1.39 2.61c0.18 0.34 0.56 0.51 0.93 0.42l2.84-0.65 2.22 1.89c0.15 0.13 0.34 0.19 0.52 0.19s0.37-0.06 0.52-0.19l2.22-1.89 2.84 0.65c0.37 0.09 0.75-0.08 0.93-0.42l1.39-2.61 2.73-1.12c0.35-0.14 0.57-0.52 0.51-0.9l-0.44-2.92 2-2.18c0.27-0.29 0.27-0.73 0-1.02zM10.11 16.4l-4.24-4.24 1.41-1.41 2.83 2.83 6.36-6.36 1.41 1.41-7.77 7.77z"/>
                        </svg>
                    </div>
                @endif
            </div>

            <div class="space-y-1 flex flex-col items-center">
                <div class="flex items-center gap-2">
                    <h3 class="text-4xl font-black tracking-tighter italic leading-tight">{{ format_display_name($user->name) }}</h3>
                    @if($user->isVerified())
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-blue-500 fill-blue-500/10 drop-shadow-md" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M23.33 10.11l-2-2.18 0.44-2.92c0.06-0.38-0.16-0.76-0.51-0.9l-2.73-1.12-1.39-2.61c-0.18-0.34-0.56-0.51-0.93-0.42l-2.84 0.65-2.22-1.89c-0.3-0.26-0.74-0.26-1.04 0l-2.22 1.89-2.84-0.65c-0.37-0.09-0.75 0.08-0.93 0.42l-1.39 2.61-2.73 1.12c-0.35 0.14-0.57 0.52-0.51 0.9l0.44 2.92-2 2.18c-0.26 0.29-0.26 0.73 0 1.02l2 2.18-0.44 2.92c-0.06 0.38 0.16 0.76 0.51 0.9l2.73 1.12 1.39 2.61c0.18 0.34 0.56 0.51 0.93 0.42l2.84-0.65 2.22 1.89c0.15 0.13 0.34 0.19 0.52 0.19s0.37-0.06 0.52-0.19l2.22-1.89 2.84 0.65c0.37 0.09 0.75-0.08 0.93-0.42l1.39-2.61 2.73-1.12c0.35-0.14 0.57-0.52 0.51-0.9l-0.44-2.92 2-2.18c0.27-0.29 0.27-0.73 0-1.02zM10.11 16.4l-4.24-4.24 1.41-1.41 2.83 2.83 6.36-6.36 1.41 1.41-7.77 7.77z"/>
                        </svg>
                    @endif
                </div>
                <p class="text-lg font-bold opacity-50 tracking-tight">@<span></span>{{ $profile->username }}</p>
            </div>
        </div>

        {{-- Bio Section --}}
        @if($request->query('showBio', 'true') == 'true' && $profile->bio)
            <div class="relative z-10 mb-10 px-4 text-center">
                <p @class([
                    "text-sm line-clamp-2 leading-relaxed font-medium tracking-tight italic",
                    $colors['muted']
                ])>
                    "{{ $profile->bio }}"
                </p>
            </div>
        @endif

        {{-- Footer Section: Stats & QR --}}
        <div class="relative z-10 flex items-center justify-between p-6 rounded-[2.5rem] bg-current/5 border border-current/10 glass-effect">
            @if($request->query('showStats', 'true') == 'true')
                <div class="flex gap-8 px-2">
                    <div class="space-y-0.5">
                        <p class="text-2xl font-black leading-none tracking-tighter">{{ number_format($user->followers()->count()) }}</p>
                        <p class="text-[8px] font-black uppercase tracking-[0.2em] opacity-40">Leitores</p>
                    </div>
                    <div class="h-8 w-px bg-current/10 self-center"></div>
                    <div class="space-y-0.5">
                        <p class="text-2xl font-black leading-none tracking-tighter">{{ number_format($user->posts()->published()->count()) }}</p>
                        <p class="text-[8px] font-black uppercase tracking-[0.2em] opacity-40">Obras</p>
                    </div>
                </div>
            @else
                <div class="flex items-center gap-2 text-[10px] font-black uppercase tracking-[0.2em] opacity-60">
                    <x-lucide-scroll class="h-4 w-4" /> Portfólio Drafto
                </div>
            @endif

            <div class="p-2 rounded-2xl bg-white shadow-sm shrink-0 hover:scale-110 transition-transform duration-500">
                <x-u-i.qr-code
                    :data="route('profile.show', $profile->username)"
                    :size="56"
                    color="#000000"
                    bgcolor="#ffffff"
                />
            </div>
        </div>

        {{-- Final Branding --}}
        @if($showPoweredBy)
            <div class="mt-6 text-center">
                <span class="text-[8px] font-black uppercase tracking-[0.4em] opacity-20">Criado com paixão no Drafto</span>
            </div>
        @endif

        {{-- Abstract Decorative Elements --}}
        <div class="absolute -top-24 -left-24 h-64 w-64 rounded-full bg-current opacity-[0.02] blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 h-80 w-80 rounded-full bg-current opacity-[0.04] blur-3xl pointer-events-none animate-pulse"></div>
    </div>
</body>
</html>
