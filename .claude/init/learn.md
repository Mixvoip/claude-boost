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

## Context Management

This is a long process. If context fills up during scanning:
- Progress is saved in `.claude/learn-progress.json` — nothing is lost
- The user can say **"continue"** or re-paste learn.md to resume from the exact point
- Use `/compact` to compress context if you need more room mid-phase

## CRITICAL — Stay On Task

Do NOT suggest installing tools, packages, extensions, Slack, or anything the user didn't ask for.
Do NOT deviate from these instructions. Your ONLY job is: ask questions, configure settings,
scan code, and create the intelligence files listed above.

---

## Step 0: Determine Mode (ALWAYS DO THIS FIRST)

**You MUST execute one of the three modes below. You may NEVER say "everything exists, nothing to do" or skip execution. The user ran this file because they WANT a scan. Always proceed.**

Check if `.claude/learn-progress.json` exists.

### Mode A: RESUME (learn-progress.json EXISTS)
1. Read the file
2. Check `completed_phases` array — skip all completed phases
3. Check `current_phase` and `current_phase_progress` — resume from that exact point
4. Read `user_config` — DO NOT re-ask questions, the answers are saved
5. Tell the user: "Resuming from {current_phase}..."
6. Jump directly to the incomplete phase

### Mode B: REFRESH (no learn-progress.json, BUT `.claude/settings.json` has `permission_level` AND `CLAUDE.md` exists)

**This is the most common re-run scenario. The user already set up the project and wants to update after code changes. DO NOT skip this. DO NOT say "files already exist". EXECUTE THE SCAN.**

1. Read `.claude/settings.json` to get `permission_level`, `git_platform`, and `permissions`
2. Read `CLAUDE.md` to extract the project name, description, and stack
4. Tell the user:

> **Refresh mode — re-scanning your codebase for changes.**
> Settings preserved. Starting scan now.

5. Create `learn-progress.json` with config reconstructed from existing files
6. Mark these phases as already done (SKIP them — do NOT re-run):
   - `discovery` — already configured
   - `settings` — already in settings.json
   - `claude_md_draft` — already exists
   - `conventions` — preserved from existing guidelines.md
   - `safety` — hooks already installed
   - `dependencies` — skip for speed
7. **Now execute these phases in order (DO NOT SKIP THESE):**
   - **Phase 4** — Deep Codebase Scan (re-scan all code)
   - **Phase 5** — Build Registry (rebuild from scan results)
   - **Phase 6** — Detect Duplicates (re-check with new registry)
   - **Phase 10** — Skills & Architecture (update module docs)
   - **Phase 11** — Finalize CLAUDE.md (rewrite with fresh data)
   - **Phase 12** — Summary (report what changed)

### Mode C: FRESH INSTALL (none of the above)
Begin from Phase 1.

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

## Context Files (read on-demand, not every task)
- `.claude/registry.json` — search here ONLY when creating new code, to avoid duplicates
- `.claude/architecture.md` — read ONLY when you need to understand module boundaries or data flow
- `.claude/guidelines.md` — read ONLY when unsure about project conventions
- `.claude/skills/{module}.md` — read ONLY when modifying that specific module

## Safety
Guard hooks active — destructive commands are blocked automatically.

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

### 4.3 Key Code

Read 3-5 files per module. **Read actual code — never guess from filenames.**

Focus on: entry points, models/data layer, services/business logic, controllers/handlers, routes, config, and middleware. You already know where these live for each framework — find them and read them.

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

### How to Compare

Compare functions/methods using semantic similarity — functions that accomplish the same thing with different names (e.g., `formatAmount` vs `MoneyFormatter::format`, `getUser` vs `fetchAccount`). Flag pairs where name, purpose, or signature overlap significantly.

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

Copy the template and make it executable:

```bash
cp .claude/init/templates/preToolUse.sh .claude/hooks/preToolUse.sh
chmod +x .claude/hooks/preToolUse.sh
```

The template blocks: destructive DB commands, dangerous filesystem ops, force-push to protected branches, docker prune, piping remote scripts to shell, and stack-specific dangers. It also enforces permission levels from settings.json and logs all blocks to `.claude/logs/guard.log`.

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

Create `.claude/guidelines/git.md` with:
- Branch naming: `feature/`, `fix/`, `hotfix/`, `chore/`
- Conventional commits format: `feat:`, `fix:`, `docs:`, `refactor:`, `test:`
- MR/PR template with Summary, Changes, and Test Plan sections

### 10.3 Issue Templates (if git_platform is GitLab or GitHub)

Create issue templates in the appropriate directory:
- **GitLab:** `.gitlab/issue_templates/feature.md` and `.gitlab/issue_templates/bug.md`
- **GitHub:** `.github/ISSUE_TEMPLATE/feature.md` and `.github/ISSUE_TEMPLATE/bug.md`

Feature template must include: user story, description, acceptance criteria, technical notes, test plan, out of scope.
Bug template must include: description, steps to reproduce, expected vs actual behavior, environment, acceptance criteria, test plan.

Add a note in `.claude/guidelines/git.md` referencing the templates.

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

## Context Files (read on-demand, not every task)
- `.claude/registry.json` — search ONLY when creating new code, to avoid duplicates
- `.claude/architecture.md` — read ONLY when you need to understand module boundaries or data flow
- `.claude/guidelines.md` — read ONLY when unsure about conventions or patterns
- `.claude/skills/{module}.md` — read ONLY when modifying that specific module

## Maintenance (only after adding/removing top-level classes, services, or modules)
- Update .claude/registry.json if a top-level class/service/module was added or removed
- Update .claude/skills/{module}.md if a module's public API or structure changed

## Safety
Guard hooks active — destructive commands are blocked automatically.
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

These rules apply in **every** future session, not just this one. Claude follows these
from CLAUDE.md's "Context Files" and "Maintenance" sections.

### When Creating New Code
1. Search `.claude/registry.json` for existing similar functions/classes — **only when creating, not every task**
2. If the user asks to create something that already exists, TELL THEM

### When Modifying Code
1. Read `.claude/skills/{module}.md` only if you're modifying that specific module
2. Check conventions in `.claude/guidelines.md` only if unsure about patterns

### Maintenance (only after adding/removing top-level classes, services, or modules — not methods, helpers, or small files)
1. Update `.claude/registry.json` only if a top-level class/service/module was added or removed
2. Update skill files only if a module's public API or structure changed
3. Update `CLAUDE.md` only if major architecture changes happen

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