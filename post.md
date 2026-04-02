# Claude Boost Is Live — And It's Open Source

**We just shipped an open-source tool that makes AI-assisted development significantly better. And it works with any project, any language.**

---

## What Is Claude Boost?

If you've used Claude Code, you know the problem: every new session starts from scratch. Claude doesn't remember your architecture, your conventions, or the service you built last week. You end up re-explaining the same things, and Claude ends up recreating functions that already exist.

Claude Boost fixes this. Drop one folder into any project — PHP, JavaScript, Python, Go, Rust, anything — and Claude becomes a senior developer who knows your entire codebase.

It works by running a guided learning session where Claude:

- **Discovers your stack** — languages, frameworks, databases, testing tools
- **Builds a full registry** — every class, function, route, and model cataloged in JSON
- **Detects duplicates** — synonym-aware comparison so Claude never recreates what already exists
- **Learns your conventions** — from your actual code, not imposed rules
- **Installs safety hooks** — blocks destructive commands like `DROP DATABASE`, `rm -rf`, and `git push --force`
- **Writes a CLAUDE.md** — a project brain that Claude reads automatically on every session

Everything is stored as plain files — Markdown, JSON, YAML. No plugins, no background services, no extra API calls, no wasted tokens. Claude just reads the files directly.

---

## Why This Matters

Most tools that try to enhance Claude Code work by running additional AI calls to compress and summarize your codebase. That burns tokens on every session and produces lossy summaries that miss detail.

Claude Boost takes the opposite approach: it creates structured, human-readable files that Claude reads natively. Zero additional token cost. Complete context. Works across every session without re-learning.

For a team our size working across multiple projects, that difference adds up — in both cost and quality.

---

## How to Try It

**Any project (VS Code, terminal, any editor):**

```bash
# Copy the .claude/init folder into your project
cp -r .claude/init your-project/.claude/init

# Let Claude learn your codebase
claude "Read .claude/init/learn.md and execute every task in it"
```

**Laravel projects:**

```bash
composer require mixvoip/claude-boost
php artisan claude:init
claude "Read .claude/init/learn.md and execute every task in it"
```

That's it. Takes about 5-10 minutes. Claude handles everything interactively.

---

## What You Get After Setup

- **CLAUDE.md** — project brain, read every session automatically
- **registry.json** — complete catalog of your codebase
- **Safety hooks** — blocks destructive commands in real-time
- **Guidelines** — conventions learned from your actual code
- **Skills** — module documentation that persists
- **Decision logs** — architectural decisions Claude won't revisit

When your codebase changes, just re-run the learning step. Claude updates everything incrementally — nothing is lost.

---

## Built by the Voxbi Team at Mixvoip

This project was built by the Voxbi development team as part of our ongoing investment in developer tooling and open-source. We use Claude Code daily across our projects, and Claude Boost came directly from solving our own pain points.

A big thank you to Mixvoip for sponsoring this work and supporting open-source contributions from the team. Building tools that help developers everywhere — while keeping Mixvoip at the forefront of engineering innovation — is exactly the kind of initiative that makes this a great place to build software.

---

## Links

- **GitHub:** [Mixvoip/claude-boost](https://github.com/Mixvoip/claude-boost)
- **License:** MIT (use it anywhere, no restrictions)
- **Packagist (Laravel):** `mixvoip/claude-boost`

---

Give it a try on your projects and share your feedback. If you run into issues or have ideas for improvements, open an issue on GitHub or reach out to the Voxbi team directly.

Let's make our AI-assisted development workflow the best in the industry.
