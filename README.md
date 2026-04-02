# Claude Boost

[![GitHub release](https://img.shields.io/github/v/tag/ualimxvp/claude-boost?label=release)](https://github.com/ualimxvp/claude-boost/releases)
[![Sponsored by Mixvoip](https://img.shields.io/badge/sponsored%20by-Mixvoip-blue)](https://www.mixvoip.com)
[![Made in Luxembourg](https://img.shields.io/badge/made%20in-Luxembourg%20%F0%9F%87%B1%F0%9F%87%BA-red)]()
[![License: MIT](https://img.shields.io/badge/license-MIT-green)](LICENSE)

> One file. Zero commands. Makes Claude smart about your codebase.

Drop one folder into any project — PHP, JavaScript, Python, Go, Rust, Ruby, Java, anything — and Claude becomes a senior developer who knows your entire codebase. It reads your code, builds a registry, detects duplicates, learns your conventions, sets up safety guards, and writes a CLAUDE.md that persists across every session.

## Install (Any Project)

```bash
# Clone Claude Boost
git clone https://github.com/ualimxvp/claude-boost.git

# Copy the init folder into your project
cp -r claude-boost/.claude/init your-project/.claude/init

# Let Claude learn your codebase
cd your-project
claude "Read .claude/init/learn.md and execute every task in it"
```

That's it. Claude handles everything interactively.

### Laravel Projects

```bash
composer require ualimxvp/claude-boost
php artisan claude:init
claude "Read .claude/init/learn.md and execute every task in it"
```

---

## What Claude Does

When Claude reads `learn.md`, it runs a **13-step interactive setup**:

0. **Checks for previous progress** — resumes if interrupted
1. **Discovers your stack** — languages, frameworks, databases, testing tools
2. **Asks you questions** — model preference, permission level, features to enable
3. **Drafts CLAUDE.md early** — safety net in case of interruption
4. **Deep scans your codebase** — language-aware scanning (not just file listing)
5. **Builds a registry** — every class, function, route, model cataloged in JSON
6. **Detects duplicates** — synonym-aware comparison (30+ synonym groups)
7. **Learns your conventions** — from your actual code, not imposed rules
8. **Installs safety hooks** — blocks destructive commands via shell hooks
9. **Maps dependencies** — traces who depends on what
10. **Creates skills, decisions, git standards** — module docs, ADRs, branch/commit rules
11. **Finalizes CLAUDE.md** — comprehensive project brain, read every session
12. **Final summary** — reports what was created and next steps

If interrupted at any point, just say "continue" or re-paste learn.md. Claude reads `learn-progress.json` and picks up exactly where it left off.

---

## What You Get

```
your-project/
├── CLAUDE.md                        <- Claude reads this every session
├── .claude/
│   ├── .gitignore                   <- Ignores logs/, settings.local.json, learn-progress.json
│   ├── claude-boost.json            <- Your config (model, permissions, features)
│   ├── settings.json                <- Claude permission settings & hook registration
│   ├── registry.json                <- Every class, service, function cataloged
│   ├── guard-rules.yaml             <- Safety rule definitions
│   ├── guidelines.md                <- Conventions learned from your code
│   ├── learn-progress.json          <- Resume tracker (gitignored)
│   ├── init/                        <- The learning prompts
│   │   ├── learn.md
│   │   ├── guard-rules.md
│   │   └── templates/
│   │       ├── skill.md
│   │       └── decision.md
│   ├── guidelines/                  <- Git standards and other guides
│   ├── skills/                      <- Module documentation
│   ├── decisions/                   <- Architectural decision records
│   ├── plans/                       <- Implementation plans
│   ├── hooks/                       <- Safety & convention hooks
│   │   ├── preToolUse.sh
│   │   └── postToolUse.sh
│   └── logs/                        <- Guard logs (gitignored)
```

---

## Why This Works

| Problem | How This Solves It |
|---------|-------------------|
| Claude creates `formatCurrency()` when `convertMoney()` exists | Registry + synonym-aware duplicate detection |
| Claude forgets your architecture every session | CLAUDE.md is read automatically every session |
| You re-explain patterns and conventions | Guidelines and skills persist across sessions |
| Claude makes decisions that contradict settled ones | Decision log prevents revisiting |
| Claude doesn't know your domain rules | Domain rules are in CLAUDE.md |
| Works only for one language | Works for any language — Claude reads any code |

---

## Updating

When your codebase changes significantly:

```bash
claude "Read .claude/init/learn.md and execute every task in it"
```

Claude re-reads the code, updates the registry, skills, and CLAUDE.md. It incorporates existing knowledge — nothing is lost.

### Laravel: After Package Upgrade

```bash
composer update ualimxvp/claude-boost
php artisan claude:update
```

This refreshes learn.md, hooks, and templates to the latest version. Your registry, CLAUDE.md, guidelines, skills, and decisions are preserved.

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

Guard hooks (`preToolUse.sh`) block destructive commands in real-time:

- `DROP DATABASE`, `TRUNCATE TABLE`, `DELETE` without WHERE
- `rm -rf` on critical directories
- `git push --force` to protected branches
- `chmod 777`, direct `.env` manipulation
- Production migrations, `curl | bash`

The hooks are pure bash — no PHP runtime needed. They work for any language.

---

## Self-Maintaining

CLAUDE.md instructs Claude to keep everything updated:

- **New code created** -> Claude updates registry.json
- **Module changed** -> Claude updates the skill file
- **Architecture decision made** -> Claude logs it in decisions/
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

Sensitive files like `learn-progress.json`, `settings.local.json`, and `logs/` are already gitignored. Everything else — registry, guidelines, skills, hooks, decisions, and CLAUDE.md — is safe and meant to be shared.

### What Gets Published

| File | Shared? | Why |
|------|---------|-----|
| `CLAUDE.md` | Yes | Project brain — every developer's Claude reads this |
| `registry.json` | Yes | Codebase catalog — prevents duplicate code |
| `guard-rules.yaml` | Yes | Safety rules — same protection for everyone |
| `guidelines.md` | Yes | Conventions — consistent code style |
| `skills/` | Yes | Module docs — shared knowledge |
| `decisions/` | Yes | Architecture decisions — no revisiting settled choices |
| `hooks/` | Yes | Safety & convention hooks — team-wide guardrails |
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
| **Architecture** | Background services, vector DBs, AI compression | Plain files — JSON, Markdown, YAML |
| **Token cost** | Extra API calls per session/action | Zero additional tokens — Claude reads local files |
| **Dependencies** | Additional runtimes, databases, HTTP servers | None — just Claude CLI |
| **Context quality** | AI-generated summaries (lossy) | Structured registry — every class, function, route cataloged |
| **Portability** | Tied to plugin system | Drop a folder into any project, done |
| **Transparency** | Compressed context you can't easily inspect | Human-readable files you can review and version-control |

A registry, guidelines, decision logs, and skills — all in plain files that cost zero extra tokens, survive across every session, and work with any language.

The best tools work *with* the system, not around it.

---

## Requirements

**Any project:** Claude CLI installed. That's it.

**Laravel package:** PHP 8.1+, Laravel 10+, `jq`, `git`

## Sponsor

Built in partnership with [Mixvoip](https://www.mixvoip.com). Thanks for supporting open-source development.

## Contributing

Found a bug or have an idea? [Open an issue](https://github.com/ualimxvp/claude-boost/issues) or submit a pull request.

## License

[MIT License](LICENSE)

---

**One file. Zero commands. Makes Claude smart about your codebase.**

[GitHub](https://github.com/ualimxvp/claude-boost) | [Packagist](https://packagist.org/packages/ualimxvp/claude-boost) | [Mixvoip](https://www.mixvoip.com)
