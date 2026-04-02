# Codebase Learning — Interactive Setup & Deep Scan

You are setting up project intelligence for a codebase. This is an **interactive** process.
**Do NOT skip steps. Do NOT auto-assume answers. ASK the user and wait for responses.**

This works for **any language** and **any framework** — PHP, JavaScript, Python, Go, Ruby, Rust, Java, or anything else.

## CRITICAL — Non-Negotiable Outputs

No matter what happens, you MUST produce these files before finishing:

1. **`CLAUDE.md` in the PROJECT ROOT** — This is the #1 most important output. Without it,
   no future Claude session will know anything about this project. If you do nothing else,
   you MUST create this file.
2. **`.claude/registry.json`** — JSON file (never markdown) cataloging the codebase.
3. **`.claude/architecture.md`** — Module map, data flow, key patterns (read on-demand).
4. **`.claude/guidelines.md`** — Conventions discovered from your code.

If you are running low on context, skip skills/guidelines but NEVER skip CLAUDE.md.

## CRITICAL — Stay On Task

Do NOT suggest installing tools, packages, extensions, Slack, or anything the user didn't ask for.
Do NOT deviate from these instructions. Your ONLY job is: ask questions, configure settings,
scan code, and create the intelligence files listed above.

---

## Step 0: Check for Previous Progress (ALWAYS DO THIS FIRST)

Before doing anything, check if `.claude/learn-progress.json` exists.

**If it EXISTS — this is a RESUME:**
1. Read the file
2. Check `completed_phases` array — skip all completed phases
3. Check `current_phase` and `current_phase_progress` — resume from that exact point
4. Read `user_config` — DO NOT re-ask questions, the answers are saved
5. Tell the user: "I see we were interrupted during {current_phase}. Picking up where we left off."
6. Jump directly to the incomplete phase

**If it DOES NOT exist — check for REFRESH mode:**

Check if `.claude/settings.json` exists AND has a `permission_level` field AND `CLAUDE.md` exists in the project root. If ALL three are true, this project was already set up — enter **Refresh Mode**.

### Refresh Mode

This mode re-scans the codebase and updates all intelligence files without re-asking setup questions.

1. Read `.claude/settings.json` to get `permission_level`, `git_platform`, and `permissions`
2. Read `CLAUDE.md` to extract the project name, description, and stack
3. Read `.claude/guidelines.md` if it exists (to preserve conventions)
4. Tell the user:

> **Refresh mode — existing setup detected.**
>
> | Setting | Value |
> |---------|-------|
> | Project | {name from CLAUDE.md} |
> | Stack | {stack from CLAUDE.md} |
> | Permissions | {permission_level from settings.json} |
> | Git Platform | {git_platform from settings.json} |
>
> I'll re-scan your codebase and update the registry, architecture, guidelines, skills, and CLAUDE.md.
> Your settings and permissions are preserved.
>
> **Want to change any settings before I scan?** (yes to reconfigure / no to proceed)

5. If the user says **no** (or just wants to proceed):
   - Create `learn-progress.json` with config reconstructed from existing files
   - Mark phases `discovery`, `settings`, and `claude_md_draft` as completed
   - **Jump directly to Phase 4 (Scanning)**

6. If the user says **yes** — fall through to Phase 1 (fresh start) so they can reconfigure

**If NONE of the above — this is a fresh start. Begin from Phase 1.**

### Progress File Format

After completing each phase, update `.claude/learn-progress.json`:

```json
{
    "started_at": "ISO 8601 timestamp",
    "last_updated": "ISO 8601 timestamp",
    "completed_phases": ["discovery", "settings", "claude_md_draft"],
    "current_phase": "scanning",
    "current_phase_progress": "scanned 30/127 files, built partial registry",
    "user_config": {
        "project_name": "...",
        "project_description": "...",
        "framework": "...",
        "language": "...",
        "project_type": "...",
        "permission_level": "...",
        "features": {},
        "git_platform": "...",
        "domain_rules": [],
        "notes": ""
    }
}
```

**Update rules:**
- After completing a phase: add to `completed_phases`, set `current_phase` to next, clear `current_phase_progress`
- During long phases (scanning, registry): update `current_phase_progress` periodically (e.g., "scanned 50/127 files")
- Always update `last_updated`

---

## Phase 1: Discovery (Interactive)

**Skip if `discovery` is in `completed_phases`.**

Before touching any files, detect the project stack and ask the user for preferences.

### 1.1 Auto-Detect Stack

Check for these files to identify the stack:

| File | Indicates |
|------|-----------|
| `artisan` + `composer.json` | Laravel (PHP) |
| `composer.json` (no artisan) | PHP (Symfony, generic) |
| `symfony.lock` or `bin/console` | Symfony (PHP) |
| `package.json` | JavaScript/TypeScript |
| `next.config.*` or `nuxt.config.*` | Next.js / Nuxt.js |
| `tsconfig.json` | TypeScript |
| `requirements.txt` or `pyproject.toml` | Python |
| `manage.py` | Django (Python) |
| `go.mod` | Go |
| `Cargo.toml` | Rust |
| `Gemfile` | Ruby |
| `pom.xml` or `build.gradle` | Java |
| `*.csproj` or `*.sln` | C# / .NET |
| `Dockerfile` or `docker-compose.yml` | Docker present |
| `.github/workflows/` | GitHub Actions CI |
| `.gitlab-ci.yml` | GitLab CI |

Also check: `.env.example`, `database/`, `migrations/`, `config/`, test directories.

### 1.2 Ask User (ALL Questions in ONE Message)

Present your findings and ask everything at once:

> **Let's set up project intelligence. I need your preferences before I start.**
>
> I detected: {what you found — e.g., "Laravel 11 project, PHP 8.3, MySQL, PEST testing"}
>
> **1. Project** — What does this project do? (one sentence)
>    Is my detection correct? Type: Monolith / Microservice / Package / CLI / Other?
>    Any domain rules I should know? (e.g., "prices always in cents", "all times UTC")
>
> **2. Permissions** — What can Claude do?
>    - **Strict** — Suggestions only, never writes files
>    - **Standard** — Writes code, human reviews before commit (RECOMMENDED)
>    - **Autonomous** — Can commit and push (logged)
>    - **Bypass All** — Full access, only destructive guards remain (DANGEROUS)
>
> **3. Features** — Pick by number (e.g., 1,2,3,4,5):
>    1. Project Registry — catalog all code, anti-duplication [RECOMMENDED]
>    2. Duplicate Detection — synonym-aware detection of similar code [RECOMMENDED]
>    3. Convention Learning — learn YOUR team's patterns from code [RECOMMENDED]
>    4. Safety Guards — block destructive commands via hooks [RECOMMENDED]
>    5. Auto Skill Generation — documentation for complex modules
>    6. Dependency Mapping — trace who depends on what
>    7. Git Standards — branch naming, commit format, MR templates
>    8. Plans & Tickets — development planning
>    9. Testing — test enforcement (framework-specific)
>    10. Agent Pipeline — autonomous plan/develop/review agents for tickets
>    Default: **1, 2, 3, 4, 5**
>
> **4. Git platform?** GitLab / GitHub / Both / None

Wait for their answer.

If they choose `bypass_all`, warn:
> Tier 1 safety guards (destructive DB/git/Docker commands) cannot be bypassed.
> All other restrictions are lifted. Are you sure?

### 1.3 Laravel Boost (Laravel Projects Only)

If you detected Laravel, also ask:
> I detected this is a Laravel project. **Laravel Boost** is an MCP server that gives me
> live access to your routes, database schema, models, and config in real-time.
>
> Would you like me to install it? (`composer require mixvoip/laravel-boost`)

If yes, run: `composer require mixvoip/laravel-boost`

### 1.4 Confirm Before Proceeding

Summarize in a table and ask confirmation:

> | Setting | Value |
> |---------|-------|
> | Project | {name} — {description} |
> | Stack | {language} / {framework} |
> | Type | {monolith/microservice/etc.} |
> | Permissions | {level} |
> | Features | {list} |
> | Git Platform | {platform} |
> | Domain Rules | {rules or "none specified"} |
>
> **Proceed?**

**STOP. Do NOT continue until the user confirms.**

After confirmation, save progress:
- Create `.claude/learn-progress.json` with `user_config` containing all answers
- Add `"discovery"` to `completed_phases`
- Set `current_phase` to `"settings"`

---

## Phase 2: Configure Settings

**Skip if `settings` is in `completed_phases`.**

### 2.1 Scaffold Directories

Create these directories if they don't exist:
```
.claude/
.claude/skills/
.claude/plans/
.claude/hooks/
.claude/logs/
```

Ensure `.claude/.gitignore` contains:
```
logs/
settings.local.json
learn-progress.json
```

Check if `.claude/` contains any worktree directories (e.g. `.claude/worktrees/`, or any folder matching `*worktree*`). If found, add them to `.gitignore` so they are never committed — worktrees are per-developer and must not be pushed.

### 2.2 Update `.claude/settings.json`

If the file already exists, READ IT FIRST and **merge** — don't overwrite.

Add `permission_level` and `git_platform` fields:

- `permission_level` — used by the safety hook
- `git_platform` — used by the agent pipeline (plan/develop/review agents)

- **strict**:
```json
{ "permission_level": "strict", "git_platform": "{github/gitlab/none}", "permissions": { "defaultMode": "ask", "allow": [] } }
```

- **standard**:
```json
{
  "permission_level": "standard",
  "git_platform": "{github/gitlab/none}",
  "permissions": {
    "defaultMode": "ask",
    "allow": ["Bash(git status:*)", "Bash(git diff:*)", "Bash(git log:*)", "Bash(git branch:*)"]
  }
}
```

- **autonomous**:
```json
{
  "permission_level": "autonomous",
  "git_platform": "{github/gitlab/none}",
  "permissions": {
    "defaultMode": "ask",
    "allow": [
      "Bash(git status:*)", "Bash(git diff:*)", "Bash(git log:*)",
      "Bash(git branch:*)", "Bash(git add:*)", "Bash(git commit:*)", "Bash(git push:*)"
    ]
  }
}
```

- **bypass_all**:
```json
{ "permission_level": "bypass_all", "git_platform": "{github/gitlab/none}", "permissions": { "defaultMode": "bypassPermissions" } }
```

**IMPORTANT:** Preserve existing `model` field and existing `allow` entries. MERGE, never remove.

Update progress: add `"settings"` to `completed_phases`, set `current_phase` to `"claude_md_draft"`

---

## Phase 3: Create Draft CLAUDE.md (EARLY — Safety Net)

**Skip if `claude_md_draft` is in `completed_phases`.**

Create a basic CLAUDE.md in the **PROJECT ROOT** immediately. This guarantees a CLAUDE.md exists
even if the scan fails, gets cancelled, or hits context limits.

**Path: `{project_root}/CLAUDE.md`** (same level as composer.json/package.json/go.mod — NOT inside .claude/)

```markdown
# {Project Name}

{What this project does — from user's description}

Stack: {language}/{framework} | Permissions: `{permission_level}`

## Before Writing Code
1. Check .claude/registry.json — don't duplicate existing code
2. Read .claude/architecture.md for module map and data flow
3. Read .claude/guidelines.md for project conventions
4. Read .claude/skills/{module}.md before touching a complex module

## Safety
Guard hooks active. See .claude/init/guard-rules.md

## Status
Draft — run learn.md to complete the deep scan.
```

Update progress: add `"claude_md_draft"` to `completed_phases`, set `current_phase` to `"scanning"`

---

## Phase 4: Deep Codebase Scan

**Skip if `scanning` is in `completed_phases`.**

**Resume note:** If `current_phase_progress` has info (e.g., "scanned 50/127 files"), continue from there. Don't re-scan already registered files.

### 4.1 Project Identity

Read these files (whichever exist):
- README.md, docs/
- composer.json, package.json, go.mod, Cargo.toml, pyproject.toml, Gemfile, pom.xml
- .env.example, config/
- Docker files, CI configuration

Note: project name, purpose, language, framework, key dependencies.

### 4.2 Directory Structure

Map the project structure. Exclude: vendor, node_modules, .git, dist, build, __pycache__, target, .next, .nuxt.

Understand: How is code organized? By feature? By type? Flat? Nested?

### 4.3 Key Code — Read by Stack

Read 3-5 files per module. **Read actual code — never guess from filenames.**

#### PHP / Laravel
- Entry: routes/web.php, routes/api.php
- Models: app/Models/ (relationships, casts, scopes)
- Services: app/Services/ (business logic)
- Controllers: app/Http/Controllers/ (thin or fat?)
- Migrations: database/migrations/ (schema)
- Config: config/*.php (key settings)
- Events: app/Events/, app/Listeners/ (event-driven patterns)
- Jobs: app/Jobs/ (queue patterns)
- Middleware: app/Http/Middleware/

#### JavaScript / TypeScript (React, Next.js, Vue, Node)
- Entry: src/index.*, src/App.*, pages/*, app/*
- Components: src/components/ (patterns, props, state)
- API routes: pages/api/*, app/api/*, routes/*
- State: src/store/*, src/hooks/* (Redux, Zustand, Pinia)
- Utilities: src/utils/*, src/lib/*
- Types: src/types/*, *.d.ts
- Config: next.config.*, vite.config.*, tsconfig.json

#### Python / Django / Flask
- Entry: manage.py, app.py, main.py, wsgi.py
- Models: models.py, */models.py (ORM, relationships)
- Views: views.py, */views.py (endpoints, serializers)
- URLs: urls.py, */urls.py (routing)
- Config: settings.py, config.py
- Signals: signals.py (event-driven)
- Tasks: tasks.py, celery.py (async jobs)

#### Go
- Entry: main.go, cmd/
- Handlers: handlers/, controllers/ (HTTP handlers)
- Models: models/, types/ (structs, interfaces)
- Routes: routes.go, router.go
- Config: config/, internal/config/
- Middleware: middleware/

#### Ruby / Rails
- Entry: config/routes.rb
- Models: app/models/ (ActiveRecord)
- Controllers: app/controllers/
- Services: app/services/
- Jobs: app/jobs/ (Sidekiq, ActiveJob)
- Config: config/

#### For Any Other Language
- Find entry points, main modules, data models, routing/endpoints, config
- Read the most important files to understand patterns

### 4.4 What to Note During Scanning

For each module, understand and note:
- **Pattern**: MVC, service layer, repository, hexagonal, etc.
- **Relationships**: inheritance, composition, injection, events
- **Naming conventions**: what the team ACTUALLY uses (not what's standard)
- **Data flow**: how requests move through the system
- **Dependencies**: what imports/uses what

Update `current_phase_progress` periodically (e.g., "scanned routes, models, services — now reading events").

Update progress: add `"scanning"` to `completed_phases`, set `current_phase` to `"registry"`

---

## Phase 5: Build Registry

**Skip if `registry` is in `completed_phases`.**

### Create `.claude/registry.json` (MUST be JSON, never markdown)

If a registry.json already exists (from a prior run), ENRICH it — don't replace:
- Fix purpose descriptions by reading actual code
- Add missing tags
- Add missing entries

If creating new:

```json
{
    "scanned_at": "{ISO 8601 timestamp}",
    "scanned_by": "learn",
    "stack": "{language}/{framework}",
    "stats": {
        "total": 0,
        "by_type": {}
    },
    "entries": {}
}
```

### Registry Entry Format (Universal — Any Language)

```json
"entries": {
    "InvoiceService": {
        "file": "app/Services/InvoiceService.php",
        "type": "service",
        "purpose": "Handles invoice creation, calculation, and payment status",
        "tags": ["billing", "invoices", "payments"],
        "public_methods": ["create", "calculateTotal", "markAsPaid"],
        "depends_on": ["InvoiceRepository", "TaxCalculator"],
        "language": "php"
    },
    "useAuth": {
        "file": "src/hooks/useAuth.ts",
        "type": "hook",
        "purpose": "Authentication state management and login/logout",
        "tags": ["auth", "hooks", "state"],
        "exports": ["useAuth", "AuthProvider"],
        "depends_on": ["AuthContext", "api/auth"],
        "language": "typescript"
    },
    "UserViewSet": {
        "file": "api/views/user.py",
        "type": "view",
        "purpose": "REST API endpoints for user CRUD operations",
        "tags": ["users", "api", "rest"],
        "public_methods": ["list", "create", "retrieve", "update", "destroy"],
        "depends_on": ["UserSerializer", "UserModel"],
        "language": "python"
    }
}
```

**Type values by language:**

| PHP | JS/TS | Python | Go | Ruby |
|-----|-------|--------|-----|------|
| class, service, controller, model, trait, interface, enum, middleware, job, event, listener, helper | component, hook, store, util, api-route, type, middleware, context, provider | class, model, view, serializer, signal, task, form, middleware, util | struct, handler, middleware, service, repository, util | class, model, controller, service, job, concern |

### Rules
- Only include files you actually read — don't guess from filenames
- Purpose must describe what the code DOES, not repeat the name
- Include the important 80% — skip trivial/empty files
- Update `stats` counts to match actual entries
- For `depends_on`: list only direct dependencies you confirmed by reading code

Update progress: add `"registry"` to `completed_phases`, set `current_phase` to `"duplicates"`

---

## Phase 6: Detect Duplicates (If Feature Enabled)

**Skip if `duplicates` is in `completed_phases` or duplicate_detection not enabled.**

After building the registry, compare functions/methods for potential duplicates.

### Synonym Groups (Use These for Matching)

```
get = fetch = retrieve = find = load = read = obtain = query
create = make = build = generate = produce = construct = new
send = dispatch = notify = emit = broadcast = transmit = push
delete = remove = destroy = drop = purge = clear = clean
update = modify = change = edit = patch = alter = set
validate = verify = check = assert = ensure = confirm = test
format = render = display = present = show = output
transform = convert = map = parse = translate = serialize
save = store = persist = write = put = cache
handle = process = execute = run = perform = apply
auth = authenticate = authorize = login = verify = identify
money = currency = price = cost = amount = payment = fee = charge
user = account = profile = member = customer = client
log = record = track = audit = trace = monitor
config = setting = option = preference = parameter
```

Add project-specific synonyms based on the domain (e.g., for telecom: call = ring = dial = voip).

### How to Compare

For each pair of functions/methods with similar names or purposes:

1. **Name similarity** — Do the names use synonymous words?
2. **Purpose similarity** — Do they accomplish the same thing?
3. **Signature similarity** — Similar parameters and return types?

Flag pairs where 2 or more criteria match.

### Save to Registry

Add a `duplicates` section to registry.json:

```json
"duplicates": [
    {
        "a": "App\\Helpers\\formatAmount()",
        "b": "App\\Services\\MoneyFormatter::format()",
        "similarity": "high",
        "reason": "Both format monetary values with currency symbols",
        "suggestion": "Consolidate into MoneyFormatter::format() — more complete implementation"
    }
]
```

Report findings to the user. Ask if any are intentional (not real duplicates).

Update progress: add `"duplicates"` to `completed_phases`, set `current_phase` to `"conventions"`

---

## Phase 7: Learn Conventions (If Feature Enabled)

**Skip if `conventions` is in `completed_phases` or convention_learning not enabled.**

**DO NOT impose external conventions.** Learn what THIS team actually does.

### What to Analyze

1. **Naming** — Scan 20+ files and identify:
   - Class naming (PascalCase? Suffixes like Service, Repository, Controller?)
   - Method/function naming (camelCase? snake_case?)
   - Variable naming
   - File naming (matches class? kebab-case? snake_case?)
   - Constants (UPPER_SNAKE?)

2. **Architecture** — Identify:
   - Where do database queries live? (Models? Repositories? Services? Directly in controllers?)
   - Where does business logic live? (Services? Actions? Controllers?)
   - How are API responses structured? (Resources? Transformers? Raw?)
   - How is validation handled? (Form Requests? Inline? Decorators?)
   - How is error handling done? (Try/catch? Error boundaries? Middleware?)

3. **Typing/Strictness** — Identify:
   - Type hints used consistently? (PHP: declare(strict_types=1)? TypeScript: strict mode?)
   - Return types always specified?
   - Nullability handled? (nullable types? Optional?)

4. **Patterns** — Identify dominant patterns:
   - "90% of services use constructor injection" → that's the convention
   - "All controllers return JsonResponse" → that's the convention
   - "Events used for all notifications" → that's the convention

### Save Conventions

Create `.claude/guidelines.md`:

```markdown
# Project Conventions

## Naming
- Classes: {what you found — with examples from actual code}
- Methods: {what you found}
- Files: {what you found}
- Database: {what you found}

## Architecture
- {Pattern}: {where and how it's used}
- Data flow: {how requests move through the system}

## Typing
- {Strictness level and practices found}

## Dominant Patterns
- {Pattern}: used in {X}% of cases — this IS the convention

## Outliers
- {File/class}: doesn't match dominant pattern — {what's different}
```

Only project-specific conventions. No generic advice (no "follow SOLID" — Claude knows that).

Update progress: add `"conventions"` to `completed_phases`, set `current_phase` to `"safety"`

---

## Phase 8: Install Safety Guards (If Feature Enabled)

**Skip if `safety` is in `completed_phases` or guard_hooks not enabled.**

### 8.1 Create PreToolUse Hook

Create `.claude/hooks/preToolUse.sh` with these guards. The hook must:
- Read tool input from stdin as JSON (using jq)
- Extract `tool_name`, `command`, `file_path`
- Block dangerous commands by outputting `{"decision":"block","reason":"..."}`
- Allow safe commands by outputting `{"decision":"allow"}`
- Log blocked actions to `.claude/logs/guard.log`

**Tier 1 — ALWAYS block (any stack):**

```bash
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
```

### 8.2 Make Hook Executable

```bash
chmod +x .claude/hooks/preToolUse.sh
```

### 8.3 Register Hook

Read `.claude/settings.json`, merge (don't overwrite) the hooks section:

```json
{
    "hooks": {
        "PreToolUse": [
            {
                "type": "command",
                "command": ".claude/hooks/preToolUse.sh"
            }
        ]
    }
}
```

**IMPORTANT:** If hooks already exist in settings.json, append — don't replace existing hooks.

Update progress: add `"safety"` to `completed_phases`, set `current_phase` to `"dependencies"`

---

## Phase 9: Map Dependencies (If Feature Enabled)

**Skip if `dependencies` is in `completed_phases` or dependency_mapping not enabled.**

For every class/module in the registry:

1. Trace imports/uses (what does it depend on?)
2. Trace constructor injection / dependency injection
3. Trace method calls to other services

Add to registry.json:

```json
"dependency_graph": {
    "InvoiceService": {
        "depends_on": ["InvoiceRepository", "TaxCalculator", "EventDispatcher"],
        "depended_by": ["InvoiceController", "BillingJob"],
        "import_count": 3,
        "risk": "high"
    }
}
```

Flag:
- **Circular dependencies** (A → B → C → A)
- **God classes** (>8 dependencies)
- **Orphans** (nothing depends on them, they depend on nothing)
- **High-risk files** (many dependents — changes here break things)

Report findings to user.

Update progress: add `"dependencies"` to `completed_phases`, set `current_phase` to `"skills"`

---

## Phase 10: Skills, Git Standards & Architecture (If Features Enabled)

**Skip if `skills` is in `completed_phases`.**

### 10.1 Create Skill Files (if auto_skills enabled)

For each module with 3+ related files, create `.claude/skills/{module-name}.md`:

```markdown
# Skill: {Module Name}

## What This Module Does
{One paragraph — what problem it solves}

## Key Files
| File | Purpose |
|------|---------|
| `path/to/file` | What it does |

## How It Works
{Data flow, class relationships}

## Patterns Used
- {Pattern}: {how it's applied}

## Domain Rules
- {Business rules specific to this module}

## Gotchas
- {Non-obvious behavior, common mistakes}

## Dependencies
- Depends on: {list}
- Depended by: {list}
```

### 10.2 Git Standards (if git_standards enabled)

Create `.claude/guidelines/git.md`:

```markdown
# Git Standards

## Branch Naming
- feature/{description}
- fix/{description}
- hotfix/{description}
- chore/{description}

## Commit Format (Conventional Commits)
- feat: {description} — new feature
- fix: {description} — bug fix
- docs: {description} — documentation
- refactor: {description} — code restructuring
- test: {description} — adding tests

## MR/PR Template
### Summary
{What and why — 1-3 sentences}

### Changes
{File-by-file or area-by-area}

### Test Plan
{How to verify}
```

### 10.3 Issue Templates (if git_platform is GitLab or GitHub)

Create issue templates so every ticket has structured information for developers and Claude reviewers.

**For GitLab:** create `.gitlab/issue_templates/` directory.
**For GitHub:** create `.github/ISSUE_TEMPLATE/` directory.

#### Feature Template

**GitLab:** `.gitlab/issue_templates/feature.md`
**GitHub:** `.github/ISSUE_TEMPLATE/feature.md`

```markdown
---
name: Feature Request
about: Propose a new feature
labels: feature
---

## User Story

As a {role}, I want {capability} so that {benefit}.

## Description

{What needs to be built — clear, specific, no ambiguity}

## Acceptance Criteria

- [ ] {Criterion 1 — measurable, testable}
- [ ] {Criterion 2}
- [ ] {Criterion 3}

## Technical Notes

- Affected modules: {list relevant modules/services}
- Dependencies: {any blockers or prerequisites}
- Database changes: {migrations needed? schema changes?}

## Test Plan

- [ ] Unit tests: {what to test}
- [ ] Integration tests: {what to test}
- [ ] Edge cases: {what could go wrong}

## Out of Scope

{What this ticket does NOT cover — prevents scope creep}
```

#### Bug/Fix Template

**GitLab:** `.gitlab/issue_templates/bug.md`
**GitHub:** `.github/ISSUE_TEMPLATE/bug.md`

```markdown
---
name: Bug Report
about: Report a bug or unexpected behavior
labels: bug
---

## Bug Description

{What is happening vs. what should happen}

## Steps to Reproduce

1. {Step 1}
2. {Step 2}
3. {Step 3}

## Expected Behavior

{What should happen}

## Actual Behavior

{What happens instead — include error messages, logs, screenshots if available}

## Environment

- Branch/version: {branch or tag}
- Environment: {local/staging/production}
- Relevant config: {any settings that matter}

## Acceptance Criteria

- [ ] Bug no longer reproducible following the steps above
- [ ] {Any regression tests needed}
- [ ] {Any related areas to verify}

## Technical Notes

- Suspected cause: {if known}
- Affected modules: {list relevant modules/services}
- Related tickets: {links if any}

## Test Plan

- [ ] Test that reproduces the bug (should fail before fix, pass after)
- [ ] Regression tests for related functionality
- [ ] Edge cases: {what else could break}
```

**After creating templates**, add a note in `.claude/guidelines/git.md` referencing them:
```markdown
## Issue Templates
- Feature tickets: use the feature template — must include user story, acceptance criteria, and test plan
- Bug tickets: use the bug template — must include reproduction steps and acceptance criteria
- Claude reviewers rely on acceptance criteria and test plans to validate work
```

### 10.4 Create Architecture Documentation (MANDATORY)

Create `.claude/architecture.md` — this contains the detailed project knowledge that would
otherwise bloat CLAUDE.md. Claude reads this on-demand when exploring or modifying the codebase.

```markdown
# Architecture

## Overview
{High-level description — what the system does, how it's structured}

## Tech Stack
{Language version, framework, database, testing framework, CI — from actual code}

## Module Map
| Module | Path | Purpose |
|--------|------|---------|
| {name} | `{path}` | {what it does} |

## Data Flow
{How requests/data move through the system — entry point to response}

## Key Patterns
{Significant architectural patterns discovered during scan — with examples from actual code}

## Entry Points
{Main entry points into the system}

## Database Schema (Key Tables)
{Most important tables/collections and their relationships — if applicable}
```

Fill in ALL sections using knowledge from the codebase scan. This is NOT a template to leave
blank — populate it with real information.

Update progress: add `"skills"` to `completed_phases`, set `current_phase` to `"claude_md_final"`

---

## Phase 11: Finalize CLAUDE.md (MANDATORY)

**Skip if `claude_md_final` is in `completed_phases`.**

Now that scanning is complete, **rewrite the draft CLAUDE.md** with real knowledge.
**Overwrite the same file at the PROJECT ROOT.**

The final CLAUDE.md must be **lean** — it loads into context every single message, so every
line costs tokens. Detailed information belongs in the on-demand files (.claude/architecture.md,
.claude/guidelines.md, .claude/skills/).

Final structure:

```markdown
# {Project Name}

{What this project does — 1-2 sentences}

Stack: {language}/{framework} | DB: {database} | Tests: {test framework}
Permissions: `{permission_level}`

## Domain Rules
{Business rules — things you'd get wrong without knowing. If none, omit this section.}

## Before Writing Code
1. Search .claude/registry.json — don't duplicate existing code
2. Read .claude/architecture.md for module map and data flow
3. Read .claude/guidelines.md for project conventions
4. Read .claude/skills/{module}.md before touching a complex module

## After Every Task
- Update .claude/registry.json if you created/removed/renamed classes or functions
- Update .claude/skills/ if a module changed significantly

## Safety
Guard hooks active. See .claude/init/guard-rules.md for full list.
```

**Rules:**
- Target 15-30 lines. Every line must earn its place.
- Only project-specific knowledge — never generic (no "follow SOLID" etc.)
- Reference files instead of repeating their content
- Remove the "Draft" notice — it's now complete

Update progress: add `"claude_md_final"` to `completed_phases`, set `current_phase` to `"summary"`

---

## Phase 12: Final Summary

### 12.1 Verify All Outputs

Read back these files and confirm they exist and are valid:

- [ ] `CLAUDE.md` in the **project root** (not inside .claude/)
- [ ] `.claude/settings.json` with correct permissions and permission_level
- [ ] `.claude/registry.json` as valid JSON with entries
- [ ] `.claude/architecture.md` with real project knowledge
- [ ] `.claude/guidelines.md` with conventions (if feature enabled)
- [ ] `.claude/hooks/preToolUse.sh` executable (if safety enabled)
- [ ] Hook registration in `.claude/settings.json` (if safety enabled)
- [ ] Skills created for complex modules (if feature enabled)

If any are missing, create them before reporting completion.

### 12.2 Show Summary to User

> **Setup complete! Here's what was created:**
>
> | File | Status |
> |------|--------|
> | `CLAUDE.md` | Project essentials — loaded every session (~20 lines) |
> | `.claude/registry.json` | {N} entries cataloged |
> | `.claude/architecture.md` | Module map & data flow (read on-demand) |
> | `.claude/guidelines.md` | Conventions documented (read on-demand) |
> | `.claude/hooks/` | Safety guards installed |
> | `.claude/skills/` | {N} module guides (read on-demand) |
> | Duplicates found | {N} potential (see registry.json) |
> | Dependencies mapped | mapped/skipped |
>
> **Every future Claude session will automatically know your project.**
>
> To refresh after major changes, just say: "Read .claude/init/learn.md and execute it"

### 12.3 Clean Up

Delete `.claude/learn-progress.json` — the process is complete.

---

## Ongoing Rules (ALWAYS Follow in Future Sessions)

These rules apply in **every** future session, not just this one. Claude should read these
from CLAUDE.md's "Before Writing Code" and "After Every Task" sections.

### Before Creating New Code
1. Search `.claude/registry.json` for existing similar functions/classes
2. Check the `duplicates` section — is something similar already flagged?
3. If the user asks to create something that already exists, TELL THEM

### After Writing/Editing Any File
1. Does it match conventions in `.claude/guidelines.md`?
2. If it doesn't, fix it or ask the user
3. Update `.claude/registry.json` if you added/removed/renamed classes or functions

### When Modifying High-Impact Files
1. Check dependency graph in registry.json (if mapped)
2. If a file has many dependents, warn the user
3. Suggest running tests after changes

### Periodically
1. Keep `registry.json` up to date as code changes
2. Update skill files when modules change significantly
3. Update `guidelines.md` if team patterns evolve
4. Update `CLAUDE.md` if major architecture changes happen

---

## Hard Rules

- **ASK the user and wait for answers** — never assume defaults silently
- **CLAUDE.md MUST be created in the project root** — draft in Phase 3, final in Phase 11
- **CLAUDE.md must be lean** — 15-30 lines, details go in .claude/architecture.md and .claude/guidelines.md
- **Registry is JSON** (`.claude/registry.json`) — NEVER create registry as markdown
- **Settings go in `.claude/settings.json`** — single source of truth for permissions and hooks
- **Stay on task** — do NOT suggest installing unrelated tools
- **Update progress after each phase** — this enables resume if interrupted
- **Read actual code** — don't guess from filenames
- **Don't change application code** — only `.claude/` files and root `CLAUDE.md`
- **Learn conventions, don't impose** — discover what the team does, document it
- **Universal** — these instructions work for any language and framework
