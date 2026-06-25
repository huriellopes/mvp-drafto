<x-layouts.guest title="Termos de Uso | Drafto">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 py-20">
        <div class="max-w-3xl mx-auto px-6">

            <header class="mb-12 space-y-3">
                <h1 class="text-5xl font-black tracking-tighter italic text-zinc-900 dark:text-white">
                    Termos de <span class="text-indigo-600">Uso.</span>
                </h1>
                <p class="text-base text-zinc-500 dark:text-zinc-400 font-medium">
                    As regras para uso da plataforma Drafto.
                </p>
                <p class="text-xs text-zinc-400">Última atualização: {{ now()->format('d/m/Y') }}</p>
            </header>

            <div class="space-y-10 text-sm leading-7 text-zinc-600 dark:text-zinc-300">

                <section class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">1. Aceitação</h2>
                    <p>Ao criar uma conta ou utilizar o Drafto, você concorda com estes Termos e com a nossa
                        <a href="{{ route('pages.privacy') }}" class="font-semibold text-indigo-600 dark:text-indigo-400">Política de Privacidade</a>.</p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">2. Sua conta</h2>
                    <p>Você é responsável por manter a confidencialidade das suas credenciais e por toda atividade
                        realizada na sua conta. Você pode encerrar sua conta a qualquer momento nas Configurações.</p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">3. Conteúdo do usuário</h2>
                    <p>Você mantém a titularidade do conteúdo que publica e é o único responsável por ele. Ao publicar,
                        você concede ao Drafto licença para exibir e distribuir esse conteúdo na plataforma. O conteúdo
                        deve respeitar nossas <a href="{{ route('pages.guidelines') }}" class="font-semibold text-indigo-600 dark:text-indigo-400">Diretrizes da Comunidade</a>.</p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">4. Condutas proibidas</h2>
                    <p>É vedado usar a plataforma para fins ilícitos, publicar conteúdo que viole direitos de terceiros,
                        praticar spam, ou tentar comprometer a segurança e a integridade do serviço.</p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">5. Disponibilidade e alterações</h2>
                    <p>O serviço é oferecido "como está". Podemos modificar, suspender ou descontinuar funcionalidades,
                        bem como atualizar estes Termos, comunicando mudanças relevantes pelos canais da plataforma.</p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">6. Contato</h2>
                    <p>Dúvidas sobre estes Termos? Fale conosco em
                        <a href="mailto:support@drafto.pro" target="_blank" class="font-semibold text-indigo-600 dark:text-indigo-400">support@drafto.pro</a>.</p>
                </section>

            </div>
        </div>
    </div>
</x-layouts.guest>
