#!/usr/bin/env bash
# PreToolUse (Bash): rede de segurança extra, na camada do Claude Code, para a
# mesma política já aplicada por .githooks/pre-commit e .githooks/pre-push
# (ver .github/BRANCH_PROTECTION.md). Bloqueia:
#   - commit direto em main/dev sem ALLOW_DIRECT_COMMIT=1 no próprio comando
#   - push direto para main/dev sem ALLOW_DIRECT_PUSH=1 no próprio comando
#   - qualquer push --force / -f / --force-with-lease
#   - git reset --hard
#   - git branch -D em main/dev
# Deixa passar tudo o mais. Só atua se o comando parece um comando git.
set -euo pipefail

payload="$(cat)"
cmd="$(printf '%s' "$payload" | jq -r '.tool_input.command // empty')"

[ -z "$cmd" ] && exit 0

case "$cmd" in
    *git*) ;;
    *) exit 0 ;;
esac

deny() {
    jq -n --arg reason "$1" \
        '{hookSpecificOutput:{hookEventName:"PreToolUse",permissionDecision:"deny",permissionDecisionReason:$reason}}'
    exit 0
}

branch="$(git symbolic-ref --short HEAD 2>/dev/null || echo '')"
is_protected_branch=false
case "$branch" in
    main|dev) is_protected_branch=true ;;
esac

# push --force / -f / --force-with-lease: bloqueado sempre, independente da branch.
if printf '%s' "$cmd" | grep -qE 'git push' \
    && printf '%s' "$cmd" | grep -qE -- '(--force\b|--force-with-lease\b|(^| )-f( |$))'; then
    deny "Push com --force/-f/--force-with-lease está bloqueado por este hook de segurança do projeto Drafto. Confirme explicitamente com o usuário antes de usar --force, ou rode fora do Claude Code."
fi

# git reset --hard: bloqueado sempre (ação destrutiva e difícil de reverter).
if printf '%s' "$cmd" | grep -qE 'git reset[^|;&]*--hard'; then
    deny "git reset --hard está bloqueado por este hook de segurança do projeto Drafto (descarta mudanças locais). Peça confirmação explícita ao usuário antes de considerar essa ação."
fi

if [ "$is_protected_branch" = true ]; then
    # git commit direto em main/dev, sem o override do próprio repo.
    if printf '%s' "$cmd" | grep -qE 'git commit' \
        && ! printf '%s' "$cmd" | grep -q 'ALLOW_DIRECT_COMMIT=1'; then
        deny "Commit direto na branch '$branch' está bloqueado (política do repo Drafto, ver .github/BRANCH_PROTECTION.md). Fluxo correto: branch de feature a partir de dev -> PR. Emergência: ALLOW_DIRECT_COMMIT=1 git commit ..."
    fi

    # git push a partir de main/dev, sem o override do próprio repo.
    if printf '%s' "$cmd" | grep -qE 'git push' \
        && ! printf '%s' "$cmd" | grep -q 'ALLOW_DIRECT_PUSH=1'; then
        deny "Push direto a partir da branch '$branch' está bloqueado (política do repo Drafto, ver .github/BRANCH_PROTECTION.md). Fluxo correto: push da branch de feature + PR. Emergência: ALLOW_DIRECT_PUSH=1 git push ..."
    fi
fi

# git branch -D em main/dev, de qualquer branch atual.
if printf '%s' "$cmd" | grep -qE 'git branch' \
    && printf '%s' "$cmd" | grep -qE -- '-D\b' \
    && printf '%s' "$cmd" | grep -qE '\b(main|dev)\b'; then
    deny "Excluir a branch protegida ('main'/'dev') com -D está bloqueado por este hook de segurança do projeto Drafto."
fi

exit 0
