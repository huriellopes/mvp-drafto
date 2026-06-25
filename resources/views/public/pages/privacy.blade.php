<x-layouts.guest title="Política de Privacidade | Drafto">
    <div class="min-h-screen bg-zinc-50 dark:bg-zinc-950 py-20">
        <div class="max-w-3xl mx-auto px-6">

            <header class="mb-12 space-y-3">
                <h1 class="text-5xl font-black tracking-tighter italic text-zinc-900 dark:text-white">
                    Política de <span class="text-indigo-600">Privacidade.</span>
                </h1>
                <p class="text-base text-zinc-500 dark:text-zinc-400 font-medium">
                    Como o Drafto coleta, usa e protege seus dados pessoais, em conformidade com a
                    Lei Geral de Proteção de Dados (Lei nº 13.709/2018 — LGPD).
                </p>
                <p class="text-xs text-zinc-400">Última atualização: {{ now()->format('d/m/Y') }}</p>
            </header>

            <div class="space-y-10 text-sm leading-7 text-zinc-600 dark:text-zinc-300">

                <section class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">1. Quem é o controlador</h2>
                    <p>
                        O Drafto é o controlador dos dados tratados nesta plataforma. Para exercer seus direitos
                        ou tirar dúvidas sobre privacidade, contate nosso Encarregado (DPO) em
                        <a href="mailto:support@drafto.pro" target="_blank" class="font-semibold text-indigo-600 dark:text-indigo-400">support@drafto.pro</a>.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">2. Dados que coletamos</h2>
                    <ul class="list-disc space-y-1 pl-5">
                        <li><strong>Cadastro:</strong> nome e e-mail. Senha armazenada de forma criptografada (hash).</li>
                        <li><strong>Perfil público:</strong> nome de exibição, nome de usuário, biografia, localização, imagens e links que você optar por fornecer.</li>
                        <li><strong>Uso e segurança:</strong> endereço IP, data/hora de acesso, navegador (user-agent) e identificador de sessão.</li>
                        <li><strong>Conteúdo:</strong> artigos, comentários, coleções, curtidas e interações que você cria.</li>
                        <li><strong>Cookies e métricas:</strong> conforme sua escolha no banner de consentimento (ver seção 5).</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">3. Por que tratamos seus dados</h2>
                    <ul class="list-disc space-y-1 pl-5">
                        <li><strong>Execução do serviço</strong> (base legal: execução de contrato): manter sua conta, publicar seu conteúdo e exibir seu perfil.</li>
                        <li><strong>Segurança e prevenção a fraudes</strong> (legítimo interesse): registros de acesso e auditoria de alterações.</li>
                        <li><strong>Comunicações por e-mail</strong> (consentimento): novidades e lembretes, que você pode desativar a qualquer momento.</li>
                        <li><strong>Análise e marketing</strong> (consentimento): métricas de uso e anúncios, somente se você autorizar no banner de cookies.</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">4. Compartilhamento com terceiros</h2>
                    <p>
                        Não vendemos seus dados. Quando você consente com cookies de análise/marketing, dados de navegação
                        podem ser compartilhados com <strong>Google</strong> (Google Analytics e Google Ads) e
                        <strong>Meta</strong> (Meta Pixel), conforme as políticas de privacidade dessas empresas.
                        Sem o seu consentimento, esses serviços não são carregados.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">5. Cookies</h2>
                    <ul class="list-disc space-y-1 pl-5">
                        <li><strong>Necessários:</strong> sessão, autenticação, segurança (CSRF) e preferências (tema). Sempre ativos.</li>
                        <li><strong>Análise:</strong> Google Analytics e contagem de visitas — só com consentimento.</li>
                        <li><strong>Marketing:</strong> Google Ads e Meta Pixel — só com consentimento.</li>
                    </ul>
                    <p>
                        Você pode rever ou alterar suas escolhas a qualquer momento clicando em
                        <button type="button" onclick="window.draftoOpenConsent && window.draftoOpenConsent()" class="font-semibold text-indigo-600 underline dark:text-indigo-400">Preferências de cookies</button>.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">6. Seus direitos (Art. 18 da LGPD)</h2>
                    <p>Você pode, a qualquer momento:</p>
                    <ul class="list-disc space-y-1 pl-5">
                        <li>Confirmar a existência e acessar seus dados;</li>
                        <li>Corrigir dados incompletos ou desatualizados (em <em>Configurações</em> e <em>Editar Perfil</em>);</li>
                        <li>Eliminar sua conta e os dados associados (em <em>Configurações → Zona de perigo</em>);</li>
                        <li>Revogar o consentimento de cookies e de e-mails de marketing;</li>
                        <li>Solicitar portabilidade ou informações sobre o tratamento, via <a href="mailto:support@drafto.pro" target="_blank" class="font-semibold text-indigo-600 dark:text-indigo-400">support@drafto.pro</a>.</li>
                    </ul>
                </section>

                <section class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">7. Retenção e segurança</h2>
                    <p>
                        Mantemos seus dados pelo tempo necessário às finalidades acima ou conforme exigências legais.
                        Adotamos medidas técnicas e organizacionais para proteger seus dados, incluindo criptografia de
                        senhas e controle de acesso.
                    </p>
                </section>

                <section class="space-y-2">
                    <h2 class="text-lg font-bold text-zinc-900 dark:text-white">8. Alterações</h2>
                    <p>
                        Podemos atualizar esta política periodicamente. Mudanças relevantes serão comunicadas pelos
                        canais da plataforma, e a data de atualização no topo será revisada.
                    </p>
                </section>

            </div>
        </div>
    </div>
</x-layouts.guest>
