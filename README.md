# Claude Boost

> One file. Zero commands. Makes Claude smart about your codebase.

Drop one folder into any project — PHP, JavaScript, Python, Go, Rust, Ruby, Java, anything — and Claude becomes a senior developer who knows your entire codebase. It reads your code, builds a registry, detects duplicates, learns your conventions, sets up safety guards, and writes a CLAUDE.md that persists across every session.

## Install (Any Project)

```bash
# Copy the init folder into your project
cp -r .claude/init your-project/.claude/init

# Let Claude learn your codebase
claude "Read .claude/init/learn.md and execute every task in it"
```

That's it. Claude handles everything interactively.

### Laravel Projects

```bash
composer require codewithali/claude-boost
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
│   ├── hooks/                       <- Safety, convention & model routing hooks
│   │   ├── preToolUse.sh
│   │   ├── postToolUse.sh
│   │   └── modelRouter.sh
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
composer update codewithali/claude-boost
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

## Requirements

**Any project:** Claude CLI installed. That's it.

**Laravel package:** PHP 8.1+, Laravel 10+, `jq`, `git`

## Sponsor

Built in partnership with [Mixvoip](https://www.mixvoip.com). Thanks for supporting open-source development.

## License

[MIT License](LICENSE)

---

**One file. Zero commands. Makes Claude smart about your codebase.**
