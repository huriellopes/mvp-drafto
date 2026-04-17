<x-layouts.guest title="Diretrizes da Comunidade | Drafto">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 py-20">
        <div class="max-w-4xl mx-auto px-6">

            <header class="text-center mb-20 space-y-4">
                <h1 class="text-6xl font-black tracking-tighter italic text-zinc-900 dark:text-white leading-tight">
                    Código de <span class="text-indigo-600">Conduta.</span>
                </h1>
                <p class="text-xl text-zinc-500 dark:text-zinc-400 font-medium max-w-2xl mx-auto">
                    O Drafto é um espaço para escrita autêntica e debates de alto nível. Para manter essa essência, estabelecemos algumas regras fundamentais.
                </p>
            </header>

            <div class="grid gap-12">
                {{-- Regra 01 --}}
                <section class="group p-8 rounded-[2.5rem] bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 transition-all hover:shadow-2xl">
                    <div class="flex items-start gap-6">
                        <div class="h-12 w-12 shrink-0 rounded-2xl bg-indigo-600/10 dark:bg-indigo-600/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black italic shadow-inner">01</div>
                        <div class="space-y-3">
                            <h2 class="text-2xl font-black italic text-zinc-900 dark:text-white tracking-tight">Respeito e Autenticidade</h2>
                            <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed font-medium">
                                Não toleramos discurso de ódio, assédio ou qualquer forma de discriminação. O Drafto valoriza a voz do autor, mas essa voz nunca deve ser usada para silenciar ou desumanizar outros membros.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- Regra 02 --}}
                <section class="group p-8 rounded-[2.5rem] bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 transition-all hover:shadow-2xl">
                    <div class="flex items-start gap-6">
                        <div class="h-12 w-12 shrink-0 rounded-2xl bg-indigo-600/10 dark:bg-indigo-600/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black italic shadow-inner">02</div>
                        <div class="space-y-3">
                            <h2 class="text-2xl font-black italic text-zinc-900 dark:text-white tracking-tight">Propriedade Intelectual</h2>
                            <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed font-medium">
                                Publique apenas conteúdos que você criou ou que tem permissão expressa para usar. Plágio resultará em bloqueio permanente e sem aviso prévio. Cite suas fontes sempre que necessário.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- Regra 03 --}}
                <section class="group p-8 rounded-[2.5rem] bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 transition-all hover:shadow-2xl">
                    <div class="flex items-start gap-6">
                        <div class="h-12 w-12 shrink-0 rounded-2xl bg-indigo-600/10 dark:bg-indigo-600/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black italic shadow-inner">03</div>
                        <div class="space-y-3">
                            <h2 class="text-2xl font-black italic text-zinc-900 dark:text-white tracking-tight">Spam e Conteúdo Inútil</h2>
                            <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed font-medium">
                                O Drafto não é um mural de classificados. Evite publicações repetitivas, links afiliados excessivos ou conteúdos gerados por IA sem curadoria humana que não agreguem valor real aos leitores.
                            </p>
                        </div>
                    </div>
                </section>

                {{-- Regra 04 --}}
                <section class="group p-8 rounded-[2.5rem] bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 transition-all hover:shadow-2xl">
                    <div class="flex items-start gap-6">
                        <div class="h-12 w-12 shrink-0 rounded-2xl bg-indigo-600/10 dark:bg-indigo-600/20 flex items-center justify-center text-indigo-600 dark:text-indigo-400 font-black italic shadow-inner">04</div>
                        <div class="space-y-3">
                            <h2 class="text-2xl font-black italic text-zinc-900 dark:text-white tracking-tight">Engajamento e Comentários</h2>
                            <p class="text-zinc-600 dark:text-zinc-400 leading-relaxed font-medium">
                                Críticas construtivas são bem-vindas. Ataques pessoais (Ad Hominem) não. Comentários abusivos em posts de outros escritores levarão à restrição imediata da sua conta.
                            </p>
                        </div>
                    </div>
                </section>
            </div>

            <footer class="mt-20 p-12 rounded-[3.5rem] bg-zinc-950 text-center space-y-6 shadow-2xl">
                <div class="flex justify-center">
                    <x-application-logo class="h-10 w-auto fill-current text-white opacity-50" />
                </div>
                <h3 class="text-white text-3xl font-black italic tracking-tighter">Consequências das violações</h3>
                <p class="text-zinc-400 max-w-xl mx-auto font-medium leading-relaxed">
                    As punições variam de advertências formais até a suspensão permanente da conta e de todas as assinaturas vinculadas (Plus/Pro), sem direito a reembolso em casos de violações graves.
                </p>
                <div class="pt-6">
                    <a href="mailto:suporte@drafto.com" class="inline-flex h-14 px-10 items-center justify-center rounded-[1.25rem] bg-white text-zinc-950 font-black uppercase text-[11px] tracking-[0.2em] hover:bg-indigo-50 transition-colors shadow-lg">
                        Falar com Suporte
                    </a>
                </div>
            </footer>
        </div>
    </div>
</x-layouts.guest>
