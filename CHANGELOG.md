# Changelog

All notable changes to this project will be documented in this file.

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
- `stubs/hooks/`: preToolUse.sh, postToolUse.sh
- `tests/`: Feature tests for all commands
