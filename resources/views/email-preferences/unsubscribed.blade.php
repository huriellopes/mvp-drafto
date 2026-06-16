<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-zinc-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preferências de e-mail | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-full items-center justify-center bg-zinc-50 p-6 text-zinc-900 antialiased">
    <div class="w-full max-w-md rounded-[2rem] border border-zinc-200 bg-white p-10 text-center shadow-sm">
        <div class="mx-auto mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-indigo-50 ring-1 ring-indigo-100">
            <x-lucide-mail-check class="h-7 w-7 text-indigo-600" />
        </div>
        @if($valid)
            <h1 class="text-2xl font-black tracking-tight">Tudo certo!</h1>
            <p class="mt-3 text-sm leading-relaxed text-zinc-500">
                Você não receberá mais <strong>{{ $label }}</strong> do {{ config('app.name') }}.
                Pode ajustar suas preferências a qualquer momento nas configurações da sua conta.
            </p>
        @else
            <h1 class="text-2xl font-black tracking-tight">Link inválido</h1>
            <p class="mt-3 text-sm leading-relaxed text-zinc-500">
                Não foi possível identificar a preferência. Tente novamente pelo link do e-mail mais recente.
            </p>
        @endif
        <a href="{{ route('home') }}" class="mt-8 inline-flex items-center justify-center rounded-2xl bg-zinc-900 px-6 py-3 text-sm font-bold text-white transition hover:bg-zinc-800">
            Voltar ao {{ config('app.name') }}
        </a>
    </div>
</body>
</html>
