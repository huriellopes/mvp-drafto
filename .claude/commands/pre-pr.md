---
description: Roda localmente os mesmos checks do job de CI (Pint, PHPStan, Pest com cobertura) antes de abrir um PR.
---

Rode, nesta ordem exata, os checks que o workflow `.github/workflows/ci.yml` executa
nos jobs `quality` e `tests`. Pare no primeiro que falhar e relate a falha ao usuário
antes de seguir para o próximo — não tente corrigir automaticamente sem perguntar,
exceto problemas triviais de estilo (Pint é auto-fix por natureza).

1. **Estilo (Pint):**
   ```
   vendor/bin/pint --test
   ```
   Se falhar, rode `vendor/bin/pint` (sem `--test`) para corrigir automaticamente,
   mostre o `git diff` resultante e rode `--test` de novo para confirmar.

2. **Análise estática (Larastan/PHPStan):**
   ```
   vendor/bin/phpstan analyze --no-progress --memory-limit=512M
   ```
   Erros novos aqui exigem correção manual — não adicione supressões no
   `phpstan.neon` sem confirmar com o usuário.

3. **Testes + cobertura (Pest, gate de 98%):**
   ```
   php artisan config:clear --ansi
   php artisan test --testsuite=Unit,Feature --coverage --min=98
   ```
   Não rode a suíte `Performance` aqui — ela é pesada e só roda no CI fora de PRs.

Ao final, resuma para o usuário: qual dos 3 checks passou/falhou, e se o branch
está pronto para `git push` e abertura de PR contra `dev` (nunca contra `main`
diretamente, e nunca push direto em `main`/`dev`).
