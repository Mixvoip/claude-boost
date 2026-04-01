#!/bin/bash
# ──────────────────────────────────────────────────────────────────────────────
# Claude Boost — PostToolUse Convention Checker (Universal)
# ──────────────────────────────────────────────────────────────────────────────
#
# Works with ANY language/framework. No PHP required at runtime.
# Runs after Claude creates or modifies files. Reminds about conventions
# and anti-duplication. Does NOT block — only informs.
#
# Install location: .claude/hooks/postToolUse.sh
# Managed by: claude-boost package
# ──────────────────────────────────────────────────────────────────────────────

set -euo pipefail

input=$(cat)
tool_name=$(echo "$input" | jq -r '.tool_name // ""' 2>/dev/null)
file_path=$(echo "$input" | jq -r '.tool_input.file_path // .tool_input.path // ""' 2>/dev/null)

# Only check after write/edit operations
if [ "$tool_name" != "write" ] && [ "$tool_name" != "edit" ] && [ "$tool_name" != "str_replace_editor" ]; then
    exit 0
fi

# Skip vendor/build/generated directories
case "$file_path" in
    *vendor/*|*node_modules/*|*dist/*|*build/*|*__pycache__/*|*target/*|*.next/*|*.nuxt/*) exit 0 ;;
esac

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$(dirname "$SCRIPT_DIR")")"
LOG_FILE="$PROJECT_ROOT/.claude/logs/convention.log"

mkdir -p "$(dirname "$LOG_FILE")"

messages=""

# ── Anti-duplication reminder ────────────────────────────────────────────────
# If registry exists, remind Claude to check before creating new code
if [ -f "$PROJECT_ROOT/.claude/registry.json" ]; then
    # Check if the file is a new file (creation, not edit)
    if [ "$tool_name" = "write" ]; then
        messages="New file created. Check .claude/registry.json — ensure no duplicate functionality exists. "
    fi
fi

# ── Convention reminder ──────────────────────────────────────────────────────
if [ -f "$PROJECT_ROOT/.claude/guidelines.md" ]; then
    messages="${messages}Follow conventions in .claude/guidelines.md. "
fi

# ── Registry update reminder ────────────────────────────────────────────────
# If a new class/module/component file was created, remind to update registry
if [ "$tool_name" = "write" ]; then
    case "$file_path" in
        # PHP
        *Service*.php|*Controller*.php|*Model*.php|*Repository*.php|*Action*.php)
            messages="${messages}Update .claude/registry.json with this new PHP class. " ;;
        # JavaScript/TypeScript
        *Component*.tsx|*Component*.jsx|*Hook*.ts|*Store*.ts|*Context*.tsx)
            messages="${messages}Update .claude/registry.json with this new component/hook. " ;;
        # Python
        *views.py|*models.py|*serializers.py|*signals.py|*tasks.py)
            messages="${messages}Update .claude/registry.json with new entries. " ;;
        # Go
        *handler*.go|*service*.go|*repository*.go|*middleware*.go)
            messages="${messages}Update .claude/registry.json with this new Go type. " ;;
    esac
fi

# ── Test reminder ────────────────────────────────────────────────────────────
SETTINGS_FILE="$PROJECT_ROOT/.claude/claude-boost.json"
testing_enabled="false"
if [ -f "$SETTINGS_FILE" ]; then
    testing_enabled=$(jq -r '.features.testing // false' "$SETTINGS_FILE" 2>/dev/null)
fi

if [ "$testing_enabled" = "true" ] && [ "$tool_name" = "write" ]; then
    # Check if the file looks like it should have tests
    case "$file_path" in
        *test*|*spec*|*Test*|*Spec*|*_test.*) ;; # Already a test file, skip
        *Service*|*Controller*|*Handler*|*handler*|*service*|*views*|*serializer*)
            messages="${messages}Consider adding tests for this file. " ;;
    esac
fi

# Return result
if [ -n "$messages" ]; then
    echo "{\"decision\":\"allow\",\"message\":\"${messages}\"}"
    exit 0
fi

exit 0
