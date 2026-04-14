# Claude Agent Pipeline

Three autonomous agents that plan, develop, and review tickets in parallel sessions.

## Prerequisites

You need a ticket tool CLI connected to Claude:
- **GitHub:** `gh` CLI (install: `brew install gh`, then `gh auth login`)
- **GitLab:** `glab` CLI (install: `brew install glab`, then `glab auth login`)
- **Linear:** Linear MCP server (add via Claude Code settings)
- **Jira:** Jira MCP server (add via Claude Code settings)

The agents use `gh` or `glab` commands to create tickets, open PRs/MRs, and manage labels. Make sure your CLI is authenticated before starting.

### Connecting Other Ticket Tools

If your team uses Linear, Jira, Notion, or another tool, you can connect it to Claude via MCP servers:

1. Open Claude Code settings: `claude /settings`
2. Add the MCP server for your tool (e.g., Linear MCP, Jira MCP)
3. The plan agent will use whatever tool is available to create and manage tickets

The agent prompts use generic terms (ticket, PR/MR) — they work with any tool that Claude can access.

## Quick Start

Open 3 terminal tabs and run one agent per tab:

```bash
# Tab 1 — Planner (interactive, works WITH you)
claude "Read .claude/init/plan.md and execute it"

# Tab 2 — Developer (autonomous, picks up ClaudeWillCode tickets)
claude "Read .claude/init/develop.md and execute it"

# Tab 3 — Reviewer (autonomous, reviews Ready for Review tickets)
claude "Read .claude/init/review.md and execute it"
```

## How They Connect

```
You + Planner          Developer             Reviewer
    |                     |                     |
    |- Discuss idea       |                     |
    |- Scan codebase      |                     |
    |- Draft ticket       |                     |
    |- Create ticket      |                     |
    |  [ClaudeWillCode]   |                     |
    |  [Open]             |                     |
    |                     |- Picks up ticket    |
    |                     |- Plans sub-tasks    |
    |                     |- Parallel agents    |
    |                     |- Tests & commits    |
    |                     |- Opens PR/MR        |
    |                     |  [Ready for Review] |
    |                     |                     |- Picks up ticket
    |                     |                     |- 4 parallel reviewers
    |                     |                     |- Quality + Perf + Security + Breakage
    |                     |                     |- Posts inline comments
    |                     |                     |- Approve or Return
    |                     |                     |
    |                     |- Fixes if returned <-
    |                     |                     |
    |                     |              [Ready for Developer Feedback]
    '- You merge & deploy <---------------------'
```

## Ticket Lifecycle

```
Open -> Work In Progress -> Ready for Review -> Ready for Developer Feedback -> Merged
 ^         (develop)          (review)              (you)
 '--- returned by reviewer (max 2 times, then Blocked)
```

## Labels

| Label | Set By | Meaning |
|-------|--------|---------|
| `ClaudeWillCode` | Planner | Developer agent should pick this up |
| `ClaudeWillReview` | Planner | Reviewer agent should review after development |
| `Blocked` | Reviewer | 3rd review failure — needs human intervention |
| `Ready for Developer Feedback` | Reviewer | Approved — ready for human to merge |

## Agent Modes

| Agent | Mode | Interaction |
|-------|------|-------------|
| **Planner** (`plan.md`) | Interactive | Works with you — asks questions, shows drafts, waits for approval |
| **Developer** (`develop.md`) | Autonomous | Runs continuously — scans for tickets, develops, opens PRs/MRs |
| **Reviewer** (`review.md`) | Autonomous | Runs continuously — scans for PRs/MRs, reviews, approves or returns |

## Review Agent Architecture

The reviewer dispatches **4 specialist agents** in parallel, each with its own prompt file:

```
.claude/init/agents/
├── review-quality.md      — Code quality, conventions, architecture
├── review-performance.md  — Query optimization, N+1, caching, memory
├── review-security.md     — OWASP Top 10, injection, auth, data exposure
└── review-breakage.md     — Stale references, semantic mismatches, orphans
```

### Review Workflow

1. **Intake** — Fetch PR/MR diff and metadata
2. **Dispatch** — Launch 4 specialist agents in parallel
3. **Consolidate** — Merge findings, deduplicate, assign severity
4. **Post** — Create inline comments on the diff (GitLab: Draft Notes API, GitHub: Review API)
5. **Submit** — Bulk publish all comments as a single batch review
6. **Verdict** — Approve, approve with comments, or request changes
7. **Re-review** — On subsequent reviews, resolve previously fixed threads

### Bot Identity (Optional)

To have review comments appear as a bot account instead of your personal account, add a bot token to your `.env`:

```
GITLAB_BOT_TOKEN="glpat-xxxxxxxxxxxxxxxxxxxx"
```

The reviewer auto-detects the token. If not found, it uses the standard CLI (comments appear as your account).

## Tips

- **Start Planner first** — create tickets before starting Developer
- **Developer and Reviewer can run simultaneously** — Developer works on `Open` tickets, Reviewer works on `Ready for Review` tickets, they don't conflict
- **One Developer per ticket** — Developer processes one ticket at a time (but uses parallel sub-agents within a ticket)
- **You only need to**: plan with the Planner, then merge approved PRs/MRs
- **To pause an agent**: just close the terminal tab or Ctrl+C
- **To restart**: re-run the same command, agents are stateless and will re-scan tickets

## Running Specific Agents Only

```bash
# Only plan tickets (interactive)
claude "Read .claude/init/plan.md and execute it"

# Only develop (if you have tickets ready)
claude "Read .claude/init/develop.md and execute it"

# Only review (if you have PRs/MRs ready)
claude "Read .claude/init/review.md and execute it"

# Develop a specific ticket
claude "Read .claude/init/develop.md, then process only ticket #83"

# Review a specific PR/MR
claude "Read .claude/init/review.md, then review only PR #84"
```