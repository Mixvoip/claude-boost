# Claude Boost

[![GitHub release](https://img.shields.io/github/v/tag/Mixvoip/claude-boost?label=release)](https://github.com/Mixvoip/claude-boost/releases)
[![Sponsored by Mixvoip](https://img.shields.io/badge/sponsored%20by-Mixvoip-blue)](https://www.mixvoip.com)
[![Made in Luxembourg](https://img.shields.io/badge/made%20in-Luxembourg%20%F0%9F%87%B1%F0%9F%87%BA-red)]()
[![License: MIT](https://img.shields.io/badge/license-MIT-green)](LICENSE)

> One file. Zero commands. Makes Claude smart about your codebase.

Drop one folder into any project — PHP, JavaScript, Python, Go, Rust, Ruby, Java, anything — and Claude becomes a senior developer who knows your entire codebase. It reads your code, builds a registry, detects duplicates, learns your conventions, sets up safety guards, and writes a lean CLAUDE.md that persists across every session.

## Install (Any Project)

```bash
# Clone Claude Boost
git clone https://github.com/Mixvoip/claude-boost.git

# Copy the init folder into your project
cp -r claude-boost/.claude/init your-project/.claude/init

# Let Claude learn your codebase
cd your-project
claude "Read .claude/init/learn.md and execute every task in it"
```

That's it. Claude handles everything interactively.

### Laravel Projects

```bash
composer require mixvoip/claude-boost
php artisan claude:init
claude "Read .claude/init/learn.md and execute every task in it"
```

---

## What Claude Does

When Claude reads `learn.md`, it runs a **12-step interactive setup**:

0. **Checks for previous progress** — resumes if interrupted, or enters **Refresh Mode** if already set up
1. **Discovers your stack** — languages, frameworks, databases, testing tools
2. **Asks you questions** — permission level, features to enable
3. **Drafts CLAUDE.md early** — safety net in case of interruption
4. **Deep scans your codebase** — language-aware scanning (not just file listing)
5. **Builds a registry** — every class, function, route, model cataloged in grouped markdown
6. **Detects duplicates** — synonym-aware comparison (30+ synonym groups)
7. **Learns your conventions** — from your actual code, not imposed rules
8. **Installs safety hook** — blocks destructive commands via shell hook
9. **Maps dependencies** — traces who depends on what
10. **Creates skills, architecture docs, git standards** — module docs, architecture map, branch/commit rules
11. **Finalizes lean CLAUDE.md** — project essentials with pointers to deep docs
12. **Final summary** — reports what was created and next steps

If interrupted at any point, just say "continue" or re-paste learn.md. Claude reads `learn-progress.json` and picks up exactly where it left off.

**Already set up?** Just run the same command again. Claude enters **Refresh Mode** — no questions, no setup, just re-scans and updates.

---

## What You Get

```
your-project/
├── CLAUDE.md                        <- Lean essentials (~20 lines), loaded every session
├── .claude/
│   ├── .gitignore                   <- Ignores logs/, settings.local.json, learn-progress.json
│   ├── settings.json                <- Permissions, hooks, permission_level
│   ├── registry.md                  <- Every class, service, function cataloged
│   ├── architecture.md              <- Module map, data flow (read on-demand)
│   ├── guidelines.md                <- Conventions learned from your code
│   ├── learn-progress.json          <- Resume tracker (gitignored)
│   ├── init/                        <- The learning prompts & agents
│   │   ├── learn.md                <- Codebase learning (the core product)
│   │   ├── unload.md               <- Clean uninstall guide
│   │   ├── guard-rules.md
│   │   ├── plan.md                 <- Ticket planner agent
│   │   ├── develop.md              <- Autonomous developer agent
│   │   ├── review.md               <- Autonomous reviewer agent (lead orchestrator)
│   │   ├── AGENTS.md               <- Agent pipeline guide
│   │   ├── agents/                 <- Specialist review agents
│   │   │   ├── review-quality.md   <- Code quality & conventions
│   │   │   ├── review-performance.md <- Performance & query optimization
│   │   │   ├── review-security.md  <- Security (OWASP Top 10)
│   │   │   └── review-breakage.md  <- Breakage & regression detection
│   │   └── templates/
│   │       └── skill.md
│   ├── guidelines/                  <- Git standards and other guides
│   ├── skills/                      <- Module documentation (read on-demand)
│   ├── plans/                       <- Implementation plans
│   ├── hooks/
│   │   └── preToolUse.sh            <- Safety guard (always active)
│   └── logs/                        <- Guard logs (gitignored)
```

---

## Why This Works

| Problem | How This Solves It |
|---------|-------------------|
| Claude creates `formatCurrency()` when `convertMoney()` exists | Registry + synonym-aware duplicate detection |
| Claude forgets your architecture every session | Lean CLAUDE.md loaded every session, deep files read on-demand |
| You re-explain patterns and conventions | Guidelines and skills persist across sessions |
| Claude doesn't know your domain rules | Domain rules are in CLAUDE.md |
| Works only for one language | Works for any language — Claude reads any code |

---

## Updating

When your codebase changes significantly:

```bash
claude "Read .claude/init/learn.md and execute every task in it"
```

Claude detects your existing setup and enters **Refresh Mode** automatically — no questions, no confirmation. It skips setup, conventions, safety hooks, and dependencies (already done), and only re-runs what matters: **scan → registry → duplicates → skills/architecture → CLAUDE.md**. Fast and silent.

### Laravel: After Package Upgrade

```bash
composer update mixvoip/claude-boost
php artisan claude:update
```

This refreshes learn.md, hooks, and templates to the latest version. Then run the learn command above — Claude will use Refresh Mode automatically.

---

## Uninstalling

To cleanly remove Claude Boost from your project:

```bash
claude "Read .claude/init/unload.md and execute every task in it"
```

Claude will:
1. Inventory all Claude Boost files and classify them (ours vs yours)
2. Ask what you'd like to keep (registry, architecture, skills, etc.)
3. Back up everything before making changes
4. Restore your pre-boost CLAUDE.md and settings.json from git history
5. Remove only Claude Boost artifacts
6. Show you the full diff to review before you commit

For Laravel projects, also run `composer remove mixvoip/claude-boost` after.

---

## Laravel Commands

The Composer package provides three commands:

| Command | What It Does |
|---------|-------------|
| `claude:init` | Scaffolds `.claude/` directory, installs learn.md, hooks, and templates |
| `claude:doctor` | Health check — verifies setup, hooks, registry, learning progress |
| `claude:update` | Refreshes learn.md and hooks after a package upgrade |

Everything else is handled by Claude reading learn.md.

---

## Safety

Guard hook (`preToolUse.sh`) blocks destructive commands in real-time:

- `DROP DATABASE`, `TRUNCATE TABLE`, `DELETE` without WHERE
- `rm -rf` on critical directories
- `git push --force` to protected branches
- `chmod 777`, direct `.env` manipulation
- Production migrations, `curl | bash`

The hook is pure bash — no PHP runtime needed. Works for any language.

---

## Agent Pipeline — Plan, Develop, Review

Claude Boost includes 3 autonomous agents that turn your ticket workflow into a CI/CD-like pipeline:

```
You + Planner ──> Developer ──> Reviewer (4 specialists) ──> You merge
```

| Agent | What It Does | Mode |
|-------|-------------|------|
| **Planner** (`plan.md`) | Interviews you, scans codebase, creates structured tickets | Interactive |
| **Developer** (`develop.md`) | Picks up tickets, plans, uses parallel sub-agents, opens PRs/MRs | Autonomous |
| **Reviewer** (`review.md`) | 4 parallel specialist reviews (quality + performance + security + breakage), batch review with inline comments | Autonomous |

### Quick Start

```bash
# Tab 1 — Plan tickets with Claude
claude "Read .claude/init/plan.md and execute it"

# Tab 2 — Claude develops autonomously
claude "Read .claude/init/develop.md and execute it"

# Tab 3 — Claude reviews autonomously
claude "Read .claude/init/review.md and execute it"
```

### Connecting Your Ticket Tool

The agents need a CLI tool to create tickets and manage PRs/MRs:

| Platform | CLI | Setup |
|----------|-----|-------|
| **GitHub** | `gh` | `brew install gh && gh auth login` |
| **GitLab** | `glab` | `brew install glab && glab auth login` |
| **Linear** | Linear MCP | Add via Claude Code MCP settings |
| **Jira** | Jira MCP | Add via Claude Code MCP settings |

The agents auto-detect your platform from `.claude/settings.json` or directory structure (`.github/` vs `.gitlab-ci.yml`).

### How It Works for Teams

1. **You + Planner** discuss the feature → Planner creates a structured ticket with file paths, acceptance criteria, and test plan
2. **Developer** picks up the ticket → breaks it into sub-tasks → launches parallel agents → opens PR/MR
3. **Reviewer** picks up the PR/MR → dispatches 4 specialist reviewers → posts inline comments as a batch review → approves or returns with specific fixes
4. **You merge** — the boring part is automated

Returned tickets go back to the Developer (max 2 returns, then blocked for human intervention). See `.claude/init/AGENTS.md` for full documentation.

---

## Self-Maintaining

CLAUDE.md instructs Claude to keep everything updated:

- **New code created** -> Claude updates registry.md
- **Module changed** -> Claude updates the skill file
- **Major feature lands** -> Claude updates CLAUDE.md

You don't maintain these files manually. Claude does it during normal development.

---

## Publishing Your `.claude` Folder

Once Claude Boost has learned your project, you can publish the generated `.claude` folder so your entire team benefits — every developer gets the same registry, conventions, safety hooks, and project brain from the first session.

### Commit to Your Project

The simplest approach — just commit the `.claude` folder to your project repo:

```bash
git add .claude/
git commit -m "Add Claude Boost project context"
git push
```

Sensitive files like `learn-progress.json`, `settings.local.json`, and `logs/` are already gitignored. Everything else — registry, guidelines, skills, hooks, and CLAUDE.md — is safe and meant to be shared.

### What Gets Published

| File | Shared? | Why |
|------|---------|-----|
| `CLAUDE.md` | Yes | Project essentials — every developer's Claude reads this |
| `registry.md` | Yes | Codebase catalog — prevents duplicate code |
| `architecture.md` | Yes | Module map & data flow — shared knowledge |
| `guidelines.md` | Yes | Conventions — consistent code style |
| `skills/` | Yes | Module docs — shared knowledge |
| `hooks/preToolUse.sh` | Yes | Safety guard — team-wide guardrails |
| `settings.json` | Yes | Hook registration — auto-activates for the team |
| `learn-progress.json` | No | Gitignored — per-user tracking |
| `settings.local.json` | No | Gitignored — per-user permissions |
| `logs/` | No | Gitignored — per-user guard logs |

### Team Workflow

1. One developer runs Claude Boost to learn the project
2. Commit the `.claude` folder to the repo
3. Every team member gets the full context on `git pull`
4. Claude reads CLAUDE.md automatically — no setup needed for new developers
5. When the codebase evolves, re-run the learning step and commit the updates

This turns Claude from a generic assistant into a team-wide senior developer who knows your entire codebase — and stays in sync.

---

## Why Claude Boost?

Most approaches to enhancing Claude Code rely on plugins, background services, or additional AI calls to give Claude context about your codebase. This introduces overhead that works against you:

- **Extra token consumption** — AI-powered compression and summarization tools make additional API calls on every session, tool use, or prompt. Those tokens add up fast, especially on large projects.
- **Runtime dependencies** — background daemons, vector databases, and additional runtimes add infrastructure overhead for what should be a zero-friction experience.
- **Plugin system lock-in** — if the plugin API changes or your environment doesn't support it, the tool breaks. Your project context shouldn't depend on a third-party lifecycle.
- **Lossy context** — AI-generated summaries lose detail. A compressed memory of your codebase is never as useful as a structured, complete registry.

### The Claude Boost Approach

Claude already knows how to read files — it does it extremely well. Instead of building middleware that summarizes your code *for* Claude, Claude Boost lets Claude read structured context directly:

| Aspect | Plugin-Based Approach | Claude Boost |
|--------|----------------------|--------------|
| **Architecture** | Background services, vector DBs, AI compression | Plain files — JSON and Markdown |
| **Token cost** | Extra API calls per session/action | Zero additional tokens — Claude reads local files |
| **Dependencies** | Additional runtimes, databases, HTTP servers | None — just Claude CLI |
| **Context quality** | AI-generated summaries (lossy) | Structured registry — every class, function, route cataloged |
| **Portability** | Tied to plugin system | Drop a folder into any project, done |
| **Transparency** | Compressed context you can't easily inspect | Human-readable files you can review and version-control |

A registry, guidelines, and skills — all in plain files that cost zero extra tokens, survive across every session, and work with any language.

The best tools work *with* the system, not around it.

---

## Requirements

**Any project:** Claude CLI installed. That's it.

**Laravel package:** PHP 8.1+, Laravel 10+, `jq`, `git`

## Sponsor

Built in partnership with [Mixvoip](https://www.mixvoip.com). Thanks for supporting open-source development.

## Contributing

Found a bug or have an idea? [Open an issue](https://github.com/Mixvoip/claude-boost/issues) or submit a pull request.

## License

[MIT License](LICENSE)

---

**One file. Zero commands. Makes Claude smart about your codebase.**

[GitHub](https://github.com/Mixvoip/claude-boost) | [Packagist](https://packagist.org/packages/mixvoip/claude-boost) | [Mixvoip](https://www.mixvoip.com)
