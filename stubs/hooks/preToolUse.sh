#!/bin/bash
# ──────────────────────────────────────────────────────────────────────────────
# Claude Boost — PreToolUse Guard (Universal)
# ──────────────────────────────────────────────────────────────────────────────
#
# Works with ANY language/framework. No PHP required at runtime.
# This hook is ALWAYS active, regardless of permission level.
# Even 'bypass_all' cannot skip Tier 1 guards.
#
# Install location: .claude/hooks/preToolUse.sh
# Managed by: claude-boost package
# ──────────────────────────────────────────────────────────────────────────────

set -euo pipefail

# ── Verify jq is available ──────────────────────────────────────────────────
if ! command -v jq &> /dev/null; then
    echo '{"decision":"block","reason":"Guard: jq is not installed. Install: brew install jq (macOS) or apt install jq (Linux)."}'
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
    local reason="$1"
    mkdir -p "$(dirname "$GUARD_LOG")"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] BLOCKED | tool=$tool_name | cmd=$command | file=$file_path | reason=$reason" >> "$GUARD_LOG"
}

block() {
    local reason="$1"
    log_block "$reason"
    echo "{\"decision\":\"block\",\"reason\":\"Guard: $reason\"}"
    exit 0
}

# ──────────────────────────────────────────────────────────────────────────────
# TIER 1: ABSOLUTE BLOCKS — Cannot be overridden. Any stack.
# ──────────────────────────────────────────────────────────────────────────────

if [ "$tool_name" = "bash" ] || [ "$tool_name" = "shell" ]; then

    # ── Database destruction ─────────────────────────────────────────────
    echo "$command" | grep -qiE "drop\s+(database|schema)\b" && block "DROP DATABASE/SCHEMA is permanently destructive."
    echo "$command" | grep -qiE "drop\s+table\s+(if\s+exists\s+)?['\"]?(users|migrations|jobs|sessions|password_resets|accounts)['\"]?" && block "DROP TABLE on critical system table."
    echo "$command" | grep -qiE "truncate\s+table\s" && block "TRUNCATE TABLE is irreversible data loss."
    echo "$command" | grep -qiE "delete\s+from\s+\S+\s*(;|\s*$|where\s+1|where\s+true)" && block "DELETE without meaningful WHERE clause."

    # ── Filesystem destruction ───────────────────────────────────────────
    echo "$command" | grep -qE "rm\s+-rf\s+(/|/home|/var|/etc|/opt|\.)$" && block "rm -rf on system/project root."
    echo "$command" | grep -qE "rm\s+-rf\s+\.(env|git|claude)" && block "rm -rf on critical files (.env/.git/.claude)."
    echo "$command" | grep -qE "rm\s+(-[a-zA-Z]*f[a-zA-Z]*\s+).*(/|app/|src/|config/|database/|resources/|routes/|storage/|public/|lib/|pkg/|internal/|cmd/)" && block "Forced removal of project directory."
    echo "$command" | grep -qE "chmod\s+(-R\s+)?777\s+" && block "chmod 777 is world-writable. Use 755 or 644."

    # ── Git destruction ──────────────────────────────────────────────────
    echo "$command" | grep -qE "git\s+push\s+.*(-f|--force)\s+.*(main|master|production|staging|release)" && block "Force push to protected branch."
    echo "$command" | grep -qE "git\s+push\s+.*(main|master|production|staging|release).*(-f|--force)" && block "Force push to protected branch."
    echo "$command" | grep -qE "git\s+branch\s+-D\s+(main|master|production|staging|release)" && block "Deleting protected branch."
    echo "$command" | grep -qE "git\s+reset\s+--hard\s+.*(main|master|production)" && block "Hard reset on protected branch."
    echo "$command" | grep -qE "git\s+clean\s+-fd" && block "git clean -fd removes all untracked files permanently."

    # ── Docker destruction ───────────────────────────────────────────────
    echo "$command" | grep -qE "docker\s+(system\s+prune|volume\s+prune|image\s+prune)\s+-a?f?" && block "Docker prune removes ALL unused resources."
    echo "$command" | grep -qE "docker-compose\s+down\s+-v" && block "docker-compose down -v destroys all volumes."
    echo "$command" | grep -qE "docker\s+rm\s+-f\s+\$\(docker\s+ps" && block "Bulk force-removing all containers."

    # ── Security ─────────────────────────────────────────────────────────
    echo "$command" | grep -qE "cat\s+\.env|echo\s+.*\.env|cp\s+\.env\s|mv\s+\.env\s" && block "Direct .env manipulation."
    echo "$command" | grep -qiE "(curl|wget).*\|.*(bash|sh|zsh|exec)" && block "Piping remote script to shell."
    echo "$command" | grep -qE "kill\s+-9\s+" && block "kill -9 is unsafe. Use graceful shutdown."
    echo "$command" | grep -qE "ln\s+-s.*\.(env|git|claude)" && block "Symlinks to protected paths."
    echo "$command" | grep -qE "(sed|perl)\s+-i.*\.(env|lock)" && block "Inline editing protected files."

    # ── System services ──────────────────────────────────────────────────
    echo "$command" | grep -qE "systemctl\s+(stop|disable|mask)\s+(nginx|apache|mysql|postgres|redis|supervisor)" && block "Stopping system services."

    # ── PHP/Laravel specific ─────────────────────────────────────────────
    echo "$command" | grep -qE "php\s+artisan\s+migrate:fresh\s+--force" && block "migrate:fresh --force drops ALL tables."
    echo "$command" | grep -qE "php\s+artisan\s+db:wipe" && block "db:wipe is irreversible."
    echo "$command" | grep -qE "php\s+artisan\s+tinker" && block "Tinker bypasses guard checks."
    echo "$command" | grep -qE "composer\s+remove\s+(laravel/framework|illuminate/)" && block "Removing core framework."

    # ── Python/Django specific ───────────────────────────────────────────
    echo "$command" | grep -qE "manage\.py\s+flush" && block "Django flush deletes all data."
    echo "$command" | grep -qE "python.*-i\b|ipython|bpython" && block "Interactive REPL bypasses guards."

    # ── Node specific ────────────────────────────────────────────────────
    echo "$command" | grep -qE "npm\s+publish" && block "npm publish requires explicit confirmation."

    # ── Ruby/Rails specific ──────────────────────────────────────────────
    echo "$command" | grep -qE "rails\s+(console|c)\b" && block "Rails console bypasses guards."
    echo "$command" | grep -qE "rails\s+db:drop" && block "rails db:drop destroys the database."
fi

# ──────────────────────────────────────────────────────────────────────────────
# TIER 2: FILE PROTECTION
# ──────────────────────────────────────────────────────────────────────────────

if [ "$tool_name" = "write" ] || [ "$tool_name" = "edit" ] || [ "$tool_name" = "create" ] || [ "$tool_name" = "str_replace_editor" ]; then
    case "$file_path" in
        *.env|*.env.*|.env.production|.env.staging)
            block "Direct write to environment file." ;;
        */composer.lock|*/package-lock.json|*/yarn.lock|*/pnpm-lock.yaml|*/Gemfile.lock|*/go.sum|*/Cargo.lock|*/poetry.lock)
            block "Direct edit of lockfile. Run package manager instead." ;;
        */.git/*)
            block "Direct manipulation of .git internals." ;;
    esac
fi

# ──────────────────────────────────────────────────────────────────────────────
# TIER 3: PERMISSION-LEVEL ENFORCEMENT
# ──────────────────────────────────────────────────────────────────────────────

PERM_LEVEL="standard"
if [ -f "$PROJECT_ROOT/.claude/settings.json" ]; then
    configured_level=$(jq -r '.permission_level // "standard"' "$PROJECT_ROOT/.claude/settings.json" 2>/dev/null)
    if [ -n "$configured_level" ] && [ "$configured_level" != "null" ]; then
        PERM_LEVEL="$configured_level"
    fi
fi

case "$PERM_LEVEL" in
    strict)
        if [ "$tool_name" = "write" ] || [ "$tool_name" = "edit" ] || [ "$tool_name" = "create" ]; then
            block "Permission level is 'strict' — Claude cannot write files."
        fi
        if echo "$command" | grep -qE "^git\s+(commit|push|merge)"; then
            block "Permission level is 'strict' — no git operations."
        fi
        ;;
    standard)
        if echo "$command" | grep -qE "^git\s+push"; then
            block "Permission level is 'standard' — Claude cannot push. Review first."
        fi
        ;;
    autonomous)
        # Autonomous allows push but protected branches are caught by Tier 1
        ;;
    bypass_all)
        # Skips Tier 2 & 3, but NEVER Tier 1
        ;;
esac

# ──────────────────────────────────────────────────────────────────────────────
# TIER 4: SMART GUARDS — Notices (log, don't block)
# ──────────────────────────────────────────────────────────────────────────────

if [ "$tool_name" = "bash" ] || [ "$tool_name" = "shell" ]; then
    # Log migration commands
    if echo "$command" | grep -qE "(artisan\s+migrate|manage\.py\s+migrate|rails\s+db:migrate)" && ! echo "$command" | grep -qE "(--pretend|--dry-run|status)"; then
        log_block "NOTICE: Migration command executed."
    fi

    # Log package installs without version constraints
    if echo "$command" | grep -qE "composer\s+require\s+\S+$" && ! echo "$command" | grep -qE ":[\^~*]|:\d"; then
        log_block "NOTICE: composer require without version constraint."
    fi
    if echo "$command" | grep -qE "npm\s+install\s+\S+$" && ! echo "$command" | grep -qE "@"; then
        log_block "NOTICE: npm install without version constraint."
    fi
    if echo "$command" | grep -qE "pip\s+install\s+\S+$" && ! echo "$command" | grep -qE "==|>=|<=|~="; then
        log_block "NOTICE: pip install without version constraint."
    fi
fi

# ──────────────────────────────────────────────────────────────────────────────
# PASSED — Allow the action
# ──────────────────────────────────────────────────────────────────────────────
echo '{"decision":"allow"}'
exit 0
