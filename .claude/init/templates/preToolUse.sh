#!/bin/bash
set -euo pipefail

if ! command -v jq &> /dev/null; then
    echo '{"decision":"block","reason":"Guard: jq required. Install: brew install jq (macOS) or apt install jq (Linux)."}'
    exit 0
fi

input=$(cat)
tool_name=$(echo "$input" | jq -r '.tool_name // ""')
command=$(echo "$input" | jq -r '.tool_input.command // ""')
file_path=$(echo "$input" | jq -r '.tool_input.file_path // .tool_input.path // ""')

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$(dirname "$SCRIPT_DIR")")"
GUARD_LOG="$PROJECT_ROOT/.claude/logs/guard.log"

log_block() {
    mkdir -p "$(dirname "$GUARD_LOG")"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] BLOCKED | tool=$tool_name | cmd=$command | file=$file_path | reason=$1" >> "$GUARD_LOG"
}

block() {
    log_block "$1"
    echo "{\"decision\":\"block\",\"reason\":\"Guard: $1\"}"
    exit 0
}

if [ "$tool_name" = "bash" ] || [ "$tool_name" = "shell" ]; then
    # Database destruction
    echo "$command" | grep -qiE "drop\s+(database|schema)\b" && block "DROP DATABASE is irreversible."
    echo "$command" | grep -qiE "drop\s+table\s+(if\s+exists\s+)?(users|migrations|sessions)" && block "DROP TABLE on critical table."
    echo "$command" | grep -qiE "truncate\s+table" && block "TRUNCATE is irreversible data loss."
    echo "$command" | grep -qiE "delete\s+from\s+\S+\s*(;|\s*$|where\s+1|where\s+true)" && block "DELETE without WHERE."

    # Filesystem destruction
    echo "$command" | grep -qE "rm\s+-rf\s+(/|/home|/var|/etc|\.)$" && block "rm -rf on system/project root."
    echo "$command" | grep -qE "rm\s+-rf\s+\.(env|git|claude)" && block "rm -rf on critical files."
    echo "$command" | grep -qE "rm\s+(-[a-zA-Z]*f[a-zA-Z]*\s+).*(/|app/|src/|config/|database/|resources/)" && block "Forced removal of project directory."
    echo "$command" | grep -qE "chmod\s+(-R\s+)?777" && block "chmod 777 is dangerous. Use 755 or 644."

    # Git destruction
    echo "$command" | grep -qE "git\s+push\s+.*(-f|--force).*(main|master|production|staging)" && block "Force push to protected branch."
    echo "$command" | grep -qE "git\s+push\s+.*(main|master|production|staging).*(-f|--force)" && block "Force push to protected branch."
    echo "$command" | grep -qE "git\s+branch\s+-D\s+(main|master|production|staging)" && block "Deleting protected branch."
    echo "$command" | grep -qE "git\s+reset\s+--hard" && block "git reset --hard is destructive. Use revert."
    echo "$command" | grep -qE "git\s+clean\s+-fd" && block "git clean -fd removes all untracked files."

    # Docker destruction
    echo "$command" | grep -qE "docker\s+(system|volume)\s+prune" && block "Docker prune removes all unused resources."
    echo "$command" | grep -qE "docker-compose\s+down\s+-v" && block "docker-compose down -v destroys volumes."

    # Security
    echo "$command" | grep -qiE "(curl|wget).*\|.*(bash|sh|zsh)" && block "Piping remote script to shell."
    echo "$command" | grep -qE "kill\s+-9" && block "kill -9 is unsafe. Use graceful shutdown."

    # Stack-specific
    echo "$command" | grep -qE "php\s+artisan\s+migrate:fresh\s+--force" && block "migrate:fresh --force drops ALL tables."
    echo "$command" | grep -qE "php\s+artisan\s+db:wipe" && block "db:wipe is irreversible."
    echo "$command" | grep -qE "php\s+artisan\s+tinker" && block "Tinker bypasses guard checks."
    echo "$command" | grep -qE "manage\.py\s+flush" && block "Django flush deletes all data."
    echo "$command" | grep -qE "npm\s+publish" && block "npm publish requires explicit confirmation."
fi

# File protection
if [ "$tool_name" = "write" ] || [ "$tool_name" = "edit" ]; then
    case "$file_path" in
        *.env|*.env.*) block "Direct write to environment file." ;;
        */composer.lock|*/package-lock.json|*/yarn.lock|*/pnpm-lock.yaml|*/Gemfile.lock|*/go.sum)
            block "Direct edit of lockfile. Run package manager instead." ;;
        */.git/*) block "Direct manipulation of .git internals." ;;
    esac
fi

# Permission-level enforcement (reads from settings.json)
PERM_LEVEL="standard"
if [ -f "$PROJECT_ROOT/.claude/settings.json" ]; then
    configured=$(jq -r '.permission_level // "standard"' "$PROJECT_ROOT/.claude/settings.json" 2>/dev/null)
    [ -n "$configured" ] && [ "$configured" != "null" ] && PERM_LEVEL="$configured"
fi

case "$PERM_LEVEL" in
    strict)
        [ "$tool_name" = "write" ] || [ "$tool_name" = "edit" ] && block "Strict mode — Claude cannot write files."
        echo "$command" | grep -qE "^git\s+(commit|push|merge)" && block "Strict mode — no git operations."
        ;;
    standard)
        echo "$command" | grep -qE "^git\s+push" && block "Standard mode — Claude cannot push. Review first."
        ;;
esac

echo '{"decision":"allow"}'
exit 0
