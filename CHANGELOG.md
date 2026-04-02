# Changelog

All notable changes to this project will be documented in this file.

## [2.0.0] - 2026-04-02

### Simplification Release

Removed overhead components to focus on real value. CLAUDE.md is now lean (~20 lines)
with pointers to deeper files read on-demand, reducing token cost per message by ~60%.

### Removed
- `claude-boost.json` — duplicated CLAUDE.md + settings.json
- `postToolUse.sh` — reminder messages were noise
- `guard-rules.yaml` — the bash hook is the actual guard
- `decisions/TEMPLATE.md` — code is source of truth for architecture
- Model routing hook — `/model sonnet` already exists, regex matching misfired
- Token strategy question

### Added
- `.claude/architecture.md` — module map, data flow (split from bloated CLAUDE.md)
- `permission_level` field in settings.json (replaces claude-boost.json)
- Token-efficient CLAUDE.md structure (~20 lines vs ~80)

### Changed
- `preToolUse.sh` reads permission_level from settings.json
- learn.md streamlined (removed 5 phases worth of overhead)
- Features list reduced from 10 to 9 options
- CLAUDE.md uses pointers to .claude/architecture.md and .claude/guidelines.md

## [1.0.0] - 2026-04-01

### Initial Public Release

**One file. Zero commands. Makes Claude smart about your codebase.**

- `learn.md` — 12-phase interactive guide that Claude reads and executes
- Works for **any language, any framework** — PHP, JavaScript, Python, Go, Rust, Ruby, Java, C#
- Resume support via `learn-progress.json` — interruptions are seamless
- Pure bash hooks — no PHP runtime dependency for safety guards
- Synonym-aware duplicate detection (30+ synonym groups)
- Convention LEARNING — Claude discovers patterns from your actual code
- 3 Laravel commands: `claude:init`, `claude:doctor`, `claude:update`

### Package Contents

- `src/`: 4 PHP files (3 commands + ServiceProvider)
- `.claude/init/`: learn.md, guard-rules.md, templates
- `stubs/hooks/`: preToolUse.sh
- `tests/`: Feature tests for all commands
