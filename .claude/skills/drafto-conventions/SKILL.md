---
name: drafto-conventions
description: Convenções de arquitetura e código do projeto Drafto (Laravel/Livewire). Use sempre que for escrever, revisar ou planejar código PHP/Livewire/Blade neste repositório — Actions, DTOs, Forms, Enums, testes Pest, estilo e regras de branch/deploy.
---

# Convenções do Drafto

Fonte de verdade única para as convenções deste projeto. Substitui o conteúdo
duplicado de `agent-dev.md`, `agent-qa.md` e `GEMINI.md` (mantidos por
compatibilidade com outras ferramentas, mas não são lidos automaticamente
pelo Claude Code — esta Skill é).

## Stack

- Laravel 13, PHP 8.3+ (container roda 8.4).
- Livewire 4 + Alpine.js + Tailwind 4 (Vite 8).
- MySQL 8.4, Redis (cache/filas/sessão), Laravel Sail (Docker).
- Pest para testes, Pint para estilo, Larastan/PHPStan para análise estática, Rector para modernização.

> Não assuma Laravel Cashier/Stripe como dependência ativa: não está no
> `composer.json` hoje (apesar de referências residuais em `README.md` e no
> `phpstan.neon`/`bootstrap/app.php`). Confirme com o usuário antes de
> escrever código que dependa de cobrança.

## Arquitetura obrigatória

1. **Actions** (`app/Actions/<Domínio>/NomeAction.php`) — lógica de negócio
   atômica, método público `exec(...)`. Controllers e componentes Livewire
   apenas orquestram: validam input, chamam a Action, devolvem feedback.
2. **DTOs** (`app/DTOs/<Domínio>/`) via `spatie/laravel-data`, classes
   `readonly`, tipagem estrita — nunca arrays associativos "mágicos" entre
   camadas.
3. **Livewire Forms** (`app/Livewire/Forms/`) concentram validação e binding
   (`#[Validate]`); o componente converte o Form em DTO antes de chamar a
   Action.
4. **Enums** (`app/Enums/`) para todo estado fechado (status, tipo,
   visibilidade, papéis, módulos).
5. **Efeitos colaterais assíncronos** via Events → Listeners/Jobs/Observers
   (SEO, mídia, views, notificações), nunca bloqueando a resposta.
6. **Policies** (`app/Policies/`) para autorização — `$this->authorize(...)`.
7. **Feedback ao usuário** sempre via toaster (`masmerise/livewire-toaster`,
   trait `HasStandardResponses` → `notifySuccess/Error/Warning`), nunca
   `alert()`.

## Regras de código

- Todo arquivo PHP começa com `declare(strict_types=1);`.
- 100% inglês em classes, métodos, variáveis e comentários — mesmo com UI e
  commits em PT-BR.
- Classes: substantivos (`PostForm`). Métodos: verbos (`exec`, `toDTO`).
- PSR-12 via Pint — não formate manualmente, rode `vendor/bin/pint <arquivo>`.
- Eager loading (`with()`) obrigatório para evitar N+1; pense em índices ao
  adicionar queries novas.

## Testes (Pest)

- Framework padrão: Pest, sintaxe nativa `describe()`/`it()`/datasets.
- Toda feature nova precisa de: caminho feliz, falhas de validação (estado
  do Form Livewire) e isolamento da Action (mock de Services quando
  necessário).
- O CI exige **cobertura mínima de 98%** (`phpunit.xml` + `ci.yml`) nas
  suítes Unit+Feature. A suíte `tests/Feature/Performance` roda separada,
  não conta para o gate de PR.
- Antes de abrir PR, rode `/pre-pr` (comando deste projeto) para replicar os
  checks do CI localmente.

## Fluxo de git (não pular)

- `main` e `dev` são protegidas (hooks locais em `.githooks/` + regra
  documentada em `.github/BRANCH_PROTECTION.md`). Nunca commitar ou dar push
  direto nelas.
- Fluxo: criar branch a partir de `dev` → trabalhar → PR para `dev` → depois
  de validado, PR de `dev` para `main`. Merges exigem CI verde e aprovação;
  não faça merge automático destas branches.
- Deploy (`deploy.php`, Deployer) só a partir de `main`.

## Diretórios-chave

```
app/Actions/        regras de negócio por domínio
app/DTOs/            objetos de transporte (spatie/laravel-data)
app/Enums/           estados tipados
app/Livewire/Forms/  validação/binding isolados
app/Models/Concerns/ traits de model (ex.: HasPlanLimits)
app/Console/Commands/ comandos agendados (ver routes/console.php)
database/migrations/ schema
resources/views/     Blade e Livewire
tests/Feature|Unit/  espelham a estrutura de app/
```
