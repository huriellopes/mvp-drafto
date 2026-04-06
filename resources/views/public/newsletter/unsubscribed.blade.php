<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-zinc-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inscrição Cancelada | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full antialiased text-zinc-900">
<div class="flex min-h-full flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white px-8 py-12 shadow-xl border border-zinc-200 rounded-3xl text-center">
            <div class="inline-flex h-16 w-16 items-center justify-center rounded-2xl bg-red-50 text-red-600 mb-6">
                <x-lucide-mail-x class="h-8 w-8" />
            </div>

            <h2 class="text-2xl font-bold tracking-tight text-zinc-900">Inscrição Cancelada</h2>
            <p class="mt-4 text-zinc-600 leading-relaxed">
                Seu e-mail foi removido da nossa lista de novidades. Você não receberá mais atualizações automáticas de posts.
            </p>

            <div class="mt-10">
                <a href="/" class="text-sm font-semibold text-profile-primary hover:underline flex items-center justify-center gap-2">
                    <x-lucide-arrow-left class="h-4 w-4" />
                    Voltar para a página inicial
                </a>
            </div>
        </div>

        <p class="mt-8 text-center text-xs text-zinc-400">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Todos os direitos reservados.
        </p>
    </div>
</div>
</body>
</html>
