# Proteção de branches — `main` e `dev`

Objetivo: **ninguém faz commit/push direto em `main` ou `dev`**. Alterações entram
apenas via **Pull Request aprovado** e com o CI verde.

Há duas camadas:

1. **Local (já configurada)** — git hooks versionados em `.githooks/` que bloqueiam
   commit/push direto nessas branches. É uma conveniência/defesa rápida, **não** uma
   garantia (pode ser ignorada com `--no-verify`). Veja a seção "Hooks locais".
2. **Servidor (GitHub) — a garantia real.** Branch protection / rulesets que o GitHub
   aplica e que ninguém consegue burlar. **Precisa ser ativada por um admin do repo.**

---

## 1. GitHub — pela interface (recomendado, sem ferramentas)

`Settings` → `Branches` → `Add branch ruleset` (ou `Add classic branch protection rule`).
Repita para `main` e para `dev`:

- **Branch name pattern:** `main` (depois `dev`)
- ✅ Require a pull request before merging
  - ✅ Require approvals → **1** (ou mais)
  - ✅ Dismiss stale pull request approvals when new commits are pushed
- ✅ Require status checks to pass before merging
  - ✅ Require branches to be up to date before merging
  - Selecione os checks do CI: **`Lint & Static Analysis`** e **`Tests (Pest)`**
    (não selecione *Performance Tests* — ele não roda em Pull Requests)
- ✅ Require linear history (opcional, evita merges bagunçados)
- ✅ Do not allow bypassing the above settings / **Include administrators**
- ✅ Block force pushes
- ✅ Restrict deletions

> Os nomes dos checks só aparecem na lista depois que o workflow `CI` rodou ao menos
> uma vez em um PR.

---

## 2. GitHub — via `gh` CLI

Requer o GitHub CLI (`gh`) autenticado. (No WSL: `sudo apt install gh && gh auth login`.)

```bash
for BR in main dev; do
  gh api -X PUT "repos/huriellopes/mvp-drafto/branches/$BR/protection" \
    -H "Accept: application/vnd.github+json" \
    -f 'required_status_checks[strict]=true' \
    -f 'required_status_checks[contexts][]=Lint & Static Analysis' \
    -f 'required_status_checks[contexts][]=Tests (Pest)' \
    -F 'enforce_admins=true' \
    -f 'required_pull_request_reviews[required_approving_review_count]=1' \
    -F 'required_pull_request_reviews[dismiss_stale_reviews]=true' \
    -F 'restrictions=' \
    -F 'allow_force_pushes=false' \
    -F 'allow_deletions=false' \
    -F 'required_linear_history=true'
done
```

## 3. GitHub — via REST API (curl + token)

Use um Personal Access Token com escopo `repo` (clássico) ou permissão
`Administration: write` (fine-grained).

```bash
TOKEN=ghp_xxx   # NÃO comite isto
for BR in main dev; do
  curl -sS -X PUT \
    -H "Authorization: Bearer $TOKEN" \
    -H "Accept: application/vnd.github+json" \
    "https://api.github.com/repos/huriellopes/mvp-drafto/branches/$BR/protection" \
    -d '{
      "required_status_checks": { "strict": true, "contexts": ["Lint & Static Analysis", "Tests (Pest)"] },
      "enforce_admins": true,
      "required_pull_request_reviews": { "required_approving_review_count": 1, "dismiss_stale_reviews": true },
      "restrictions": null,
      "allow_force_pushes": false,
      "allow_deletions": false,
      "required_linear_history": true
    }'
done
```

> ⚠️ **Repositório com um só mantenedor:** "Require approvals = 1" exige a aprovação de
> **outra** pessoa (o autor do PR não pode aprovar o próprio). Se você trabalha sozinho,
> ou adicione um colaborador para revisar, ou deixe approvals = 0 mantendo os checks de
> CL obrigatórios (CI verde + PR), ou desmarque temporariamente "Include administrators".

---

## Hooks locais (`.githooks/`)

Já versionados e ativados neste clone. Para um clone novo eles são configurados
automaticamente no `composer install` (script `post-autoload-dump`). Para ativar à mão:

```bash
git config core.hooksPath .githooks
```

- `pre-commit` — bloqueia commit direto em `main`/`dev`.
- `pre-push` — bloqueia push direto para `main`/`dev`.
- Emergência: `ALLOW_DIRECT_COMMIT=1 git commit ...` / `ALLOW_DIRECT_PUSH=1 git push ...`.

Fluxo recomendado:

```bash
git switch -c feature/minha-mudanca
# ... commits ...
git push -u origin feature/minha-mudanca
# abrir Pull Request para dev (ou main) e aguardar aprovação + CI verde
```
