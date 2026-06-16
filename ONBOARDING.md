# Drafto — Guia de Onboarding

Plataforma de publicação/escrita (estilo Medium) em PT‑BR: perfis públicos, posts/artigos, seguidores, comentários, newsletter, coleções salvas, painel administrativo e um sistema de **módulos** ativáveis por usuário.

> Este guia foca no que um dev precisa para ser produtivo rápido: como rodar, como o projeto está organizado e as convenções que o time segue. Para um mapeamento exaustivo, veja a seção "Estrutura" abaixo.

---

## 1. Stack

| Camada | Tecnologia |
|---|---|
| Backend | **Laravel 13**, PHP **8.3+** (o app roda em PHP 8.4 no container) |
| UI server-driven | **Livewire 4** (~75 componentes) |
| Interatividade | **Alpine.js** |
| CSS | **Tailwind 4** (`@tailwindcss/vite`) |
| Build / dev server | **Vite 8** + `laravel-vite-plugin` |
| Editor rico | **Quill 2.0.3** (carregado via CDN no layout) |
| Ambiente local | **Laravel Sail** (Docker) |

Pacotes notáveis: `spatie/laravel-data` (DTOs), `mews/purifier` (sanitização XSS), `owen-it/laravel-auditing` (auditoria), `pragmarx/google2fa-laravel` (2FA), `masmerise/livewire-toaster` (toasts), `ralphjsmit/laravel-seo` + `spatie/laravel-sitemap` (SEO), `spatie/laravel-health`, `maatwebsite/excel` (exports), `dompdf` + libs de QR (PDF/crachá de perfil), `diglactic/laravel-breadcrumbs`, `intervention/image`, `blade-lucide-icons`.

---

## 2. Subindo o ambiente

O projeto usa **Laravel Sail** (Docker). Os comandos PHP devem rodar **dentro do container** (o PHP do host pode ser 8.3 e quebrar; o app exige 8.4).

```bash
# 1. Subir os containers (app, mysql, redis, mailpit)
./vendor/bin/sail up -d

# 2. Dependências
./vendor/bin/sail composer install
npm install

# 3. App key / migrações / seed
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed

# 4. Dev (serve + queue + logs + vite via concurrently)
composer dev        # ou: ./vendor/bin/sail npm run dev (HMR) em paralelo
```

- App: **http://localhost:9396** (porta do container 80 → host `APP_PORT=9396`).
- Vite dev server (HMR): porta `VITE_PORT` (ex.: 5536) — o `@vite` usa o `public/hot` quando o dev server está ativo.
- Mailpit para e-mails de dev.
- Sempre rode artisan/composer via `./vendor/bin/sail`.

> Limites de upload no PHP do container: `post_max_size`/`upload_max_filesize` = **100M** (relevante para upload de imagem/vídeo no editor).

---

## 3. Arquitetura e convenções

A regra de ouro: **lógica de negócio vive em Actions**, não em componentes Livewire nem controllers.

- **Action pattern** — `app/Actions/**` (~70 actions). Cada caso de uso é uma classe com `exec(...)`. Ex.: `SavePostAction`, `PublishScheduledPostsAction`, `ToggleFollowAction`. Componentes Livewire/Controllers apenas orquestram (validam input, chamam a Action, devolvem feedback).
- **DTOs** — `app/DTOs/**` (spatie/laravel-data) para transportar dados entre camadas (ex.: `SavePostData`).
- **Form Objects (Livewire)** — `app/Livewire/Forms/**` concentram regras de validação e binding (`#[Validate]`).
- **Enums tipados** — `app/Enums/**` para estados (status, tipo, visibilidade, papéis, módulos…).
- **Pipeline assíncrono** — `Events` → `Listeners`/`Jobs`/`Observers` para efeitos colaterais (SEO, mídia, views, notificações) sem bloquear o request.
- **Policies** — `app/Policies/**` para autorização (`$this->authorize(...)`).
- **Traits** — utilidades reutilizáveis (ex.: `App\Traits\Livewire\HasStandardResponses` padroniza toasts: `notifySuccess/Error/Warning`).
- **Feedback ao usuário** — sempre via toaster (`masmerise`), não `alert()`.

### Mapa de `app/`
```
Actions/      regras de negócio por domínio (Auth, Posts, Users, Saved, Comments, Reports, Newsletter, Modules, Profile, Support…)
DTOs/         objetos de transporte (spatie/laravel-data)
Enums/        Role, PostStatus, PostType, PostVisibility, Module, Report*, Comment*, Support*, etc.
Events/Listeners/Jobs/Observers/   efeitos colaterais assíncronos
Http/         Controllers + Middleware (module:, module.access:, must.change.password…)
Livewire/     UI: Dashboard/ Public/ Auth/ Forms/ Actions/
Models/       18 models
Notifications/ por domínio
Policies/     8 policies
Services/     SystemLogger, IbgeService, Post\ReadingTimeCalculator, Post\PostSeoGenerator, Profile\ProfileSeoGenerator
Console/Commands/  comandos agendados
Providers/    AppServiceProvider, HealthServiceProvider
```

---

## 4. Domínio (modelos)

**Conteúdo:** `Post` ↔ `PostCategory`, `Tag` (pivot `post_tag`), `PostView`, `Comment` (+ likes), likes de post, `SavedPost` ↔ `Collection`.
**Usuários/social:** `User` ↔ `Profile` (+ `ProfileSetting`), `Follower`, `NewsletterSubscriber`.
**Operação:** `Module` (+ `module_user`), `Report`, `Support`, `ShortLink`, `SiteView`, `ImpersonationLog`.

**Enums do Post:**
- Status: `draft` · `published` · `scheduled` · `archived`
- Tipo: `post` · `article`
- Visibilidade: `public` · `unlisted` · `followers_only` · `registered`

**Papéis:** `super_admin` · `writer` · `reader`.

---

## 5. Sistema de módulos (feature flags por usuário)

`ModuleEnum` + tabelas `modules`/`module_user` ligam/desligam recursos por usuário. Rotas protegidas por middleware `module:<nome>` e `module.access:<nome>`. Em Blade, checa-se `Module::isEnabled(ModuleEnum::X)` e `auth()->user()->getModuleSetting(...)`.

Módulos: `profile`, `profile_badge`, `account`, `my_posts`, `draft`, `follows`, `saved_post`, `comments`, `support`, `link_shortener`, `post_scheduler`.

---

## 6. Áreas e rotas

`routes/web.php` agrega grupos em `routes/parts/`:

- **`public.php`** — site público: home, explore, perfil `/@{username}`, post `/posts/{slug}`, newsletter, short link `/s/{code}`, crachá `/badge/@{user}`.
- **`auth.php`** — login, registro, reset de senha, 2FA, verificação de e-mail.
- **Dashboard** (middleware `auth` + `must.change.password`, prefixo `/dashboard`):
  - **`writer.php`** — `meus-conteudos`, `rascunhos`, `create`, `{post}/edit`, categorias, tags.
  - **`reader.php`** — salvos, comentários, comunidade (follows).
  - **`admin-routes.php`** — administração (usuários, módulos, auditoria, relatórios, analytics…).
- Sistema: `POST /trix/attachments` (upload de mídia do editor), `/analytics/duration`, `/health-check`.

> **Route key de Post = `slug`** (não id). URLs de edição usam o slug.

---

## 7. Frontend e o editor

- **Layouts:** `layouts/app` (dashboard), `layouts/site` (público), `auth`/`guest`.
- **Componentes UI** em `resources/views/components/ui/`: `button`, `input`, `select`, `textarea`, `modal`, `confirm-modal`, `table`, `avatar`, `suggestion-input`, **`quill-editor`**, `share-post/profile`, `report-button`, etc.
- **Modais** (`x-ui.modal`): abrem/fecham por eventos de janela — `$dispatch('open-modal', { name })` / `close-modal`. **Não** usam `wire:model`.
- **Editor Quill** (`components/ui/quill-editor.blade.php` + lógica em `resources/js/app.js`, registrado em `alpine:init` como `Alpine.data('quillEditor', ...)`):
  - Upload de **imagem/vídeo** (`/trix/attachments`), com validação de tamanho no cliente (100 MB) e **modal de erro** (sem `alert` nativo).
  - **Vídeo por link** (YouTube/Vimeo) e **link** via modais próprios.
  - Conteúdo inicial é renderizado dentro do elemento e **adotado na construção** do Quill (evita corromper a seleção).
  - Há contornos pontuais para um bug de seleção do **Quill 2.0.3** (documentados em `app.js`) — não mexer sem testar digitar/apagar/Enter/colar.
  - Conteúdo é **sanitizado no servidor** por `mews/purifier` (perfil `post_content` em `config/purifier.php`) antes de salvar.
- **Toaster** tem `z-index: 100000` (em `resources/css/app.css`) para sobrepor o blur dos modais.

---

## 8. Processos assíncronos

**Jobs** (`app/Jobs`): `ProcessPostMediaAndSeoJob`, `ProcessPostViewJob`, `ProcessProfileViewJob`, `ProcessSiteViewJob`, `SendNewsletterJob`, `ExportDataJob`, `GenerateRecoveryCodesJob`, `ProcessSupportMessageJob`.

**Agendamento** (`routes/console.php`):
- `posts:publish-scheduled` → **a cada minuto** (publica posts agendados)
- `drafto:send-newsletter` → diário 08:00
- `drafto:sync-views`, `GenerateSitemap`, `ArchivePostViews`, `GenerateMissingExcerpts`

> Em dev, o `queue:work` sobe junto via `composer dev`. Sem worker, jobs não processam (views/SEO/mídia ficam pendentes).

---

## 9. Segurança / Auth

- 3 papéis (`super_admin`/`writer`/`reader`) + **policies** por modelo.
- **2FA** (Google2FA) com recovery codes.
- **Impersonation** (admin assume usuário) com `ImpersonationLog`.
- **Auditoria** via `owen-it/laravel-auditing` (tabela `audits`).
- **`must.change.password`** força troca de senha (fluxo de reset por admin).
- Sanitização de HTML do editor obrigatória (Purifier).

---

## 10. Performance

Banco com índices avançados e **fulltext** para busca (`add_fulltext_indexes...`, `add_advanced_performance_indexes`, `optimize_database_structure`, `apply_final_performance_tuning`). Views de post/site são processadas em fila; `ShowPost` usa cache. Ao tocar em queries de listagem/busca, cheque os índices existentes.

---

## 11. Tarefas comuns (how-to)

- **Novo caso de uso** → crie uma `Action` em `app/Actions/<Dominio>/`, exponha `exec(...)`, e chame a partir do componente Livewire.
- **Nova tela** → componente Livewire em `app/Livewire/<area>/` + view em `resources/views/livewire/...`; registre a rota no `parts/` adequado com `Route::livewire(...)`.
- **Validação de formulário** → use um Form Object em `app/Livewire/Forms/`.
- **Feedback** → `HasStandardResponses` (`notifySuccess/Error/Warning`), nunca `alert()`.
- **Novo módulo (feature flag)** → adicione em `ModuleEnum`, migration para `modules`, e proteja a rota com `module:`/`module.access:`.
- **Rodar testes/artisan** → sempre com `./vendor/bin/sail artisan ...`.

---

## 12. Pontos de atenção / melhorias sugeridas

Itens identificados com evidência no código, em ordem de relação valor/esforço. (Branch de trabalho: `dev`.)

| # | Item | Evidência | Ação sugerida | Risco |
|---|---|---|---|---|
| 1 | **Health check do Stripe ativo sem billing** | `HealthServiceProvider.php` faz `PingCheck->name('Stripe API')->url('https://api.stripe.com')`, mas o billing foi removido | Remover esse `PingCheck` (o `/health-check` reporta/alerta sobre um serviço que o app não usa) | Baixo |
| 2 | **Dependência não usada: `mantix/livewire-jodit-text-editor`** | Sem nenhuma referência a `jodit` em `app/` ou `resources/` | Remover do `composer.json` | Baixo |
| 3 | **Helper `money()` morto** | `app/Helpers/functions.php` — formata centavos "do Stripe"; sem nenhum uso no código | Remover (ou redocumentar se for manter para futuro) | Baixo |
| 4 | **Naming debt "Trix"** | `TrixAttachmentController` + rota `trix.attachments.store` são usados pelo **Quill** (Trix era o editor antigo) | Renomear p/ algo editor‑agnóstico (`EditorAttachmentController` / `editor.attachments.store`) e atualizar 2 refs em Blade + o default do componente | Médio (refs) |
| 5 | **Limite de upload 100 MB duplicado** | `TrixAttachmentController` (`max:102400`) e `resources/js/app.js` (`100 * 1024 * 1024` + textos fixos) | Centralizar em `config/` e passar o limite ao componente Blade/JS (fonte única) | Baixo |
| 6 | **Dois editores diferentes** | Posts usam **Quill**; comentários usam um `contenteditable` cru (`components/ui/html-editor.blade.php`) | Avaliar consolidar no Quill para consistência de UX/manutenção. *(Conteúdo de comentário já é sanitizado em `StoreCommentAction` via Purifier — sem risco de XSS.)* | Médio |
| 7 | **Resíduo de migrations de billing** | `create_plans/subscriptions/customer_columns` + `complete_billing_and_subscription_cleanup` (as tabelas já foram dropadas) e um `..._fix` | Só clutter histórico — manter (já aplicadas); documentar que billing foi descontinuado | Nenhum |

**Convenções a preservar (não são dívida):**
- **PHP do host vs container:** rode tudo via Sail (host pode ser 8.3; o app exige 8.4).
- **Guards de seleção do Quill** em `app.js` contornam um bug real do **Quill 2.0.3** (latest) — só ajuste com verificação no navegador (digitar/apagar/Enter/colar).

---

*Para aprofundar: o fluxo de um Post (criar → agendar → publicar → exibir), o sistema de módulos, ou o pipeline de mídia/SEO são bons próximos pontos de estudo.*
