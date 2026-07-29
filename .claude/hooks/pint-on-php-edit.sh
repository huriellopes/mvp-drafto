#!/usr/bin/env bash
# PostToolUse (Write|Edit|MultiEdit): roda o Pint automaticamente no arquivo
# PHP que acabou de ser editado, para manter PSR-12 sem intervenção manual.
set -euo pipefail

payload="$(cat)"
file="$(printf '%s' "$payload" | jq -r '.tool_input.file_path // empty')"

[ -z "$file" ] && exit 0

case "$file" in
    *.php) ;;
    *) exit 0 ;;
esac

[ -f "$file" ] || exit 0

root="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"

if [ -x "$root/vendor/bin/pint" ]; then
    "$root/vendor/bin/pint" "$file" >/dev/null 2>&1 || true
fi

exit 0
