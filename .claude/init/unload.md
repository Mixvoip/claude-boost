# Claude Boost — Clean Uninstall

You are removing Claude Boost from this project. Follow these steps **exactly**.

## ABSOLUTE RULES — READ BEFORE DOING ANYTHING

1. **You may ONLY touch files listed in the ALLOWED FILES section below. NOTHING ELSE.**
2. **You may NOT run `rm -rf`, `git checkout`, `git restore`, `git reset`, or any destructive git command.**
3. **You may NOT delete, edit, or move ANY file outside of `.claude/` and the root `CLAUDE.md`.**
4. **You may NOT use glob patterns or wildcards in delete operations.**
5. **Delete files ONE AT A TIME using the Write/Edit tools or `rm` (single file only, no -r flag).**
6. **ALWAYS ask for confirmation before EACH delete operation.**
7. **NEVER commit. The user commits when they are ready.**
8. **If unsure whether a file is yours to delete — STOP AND ASK THE USER.**

---

## ALLOWED FILES — The ONLY files you may touch

These are the ONLY files Claude Boost creates. You may delete ONLY these exact paths.
If a file is not on this list, **do not touch it**.

### Files always created by Claude Boost:
```
.claude/init/learn.md
.claude/init/unload.md
.claude/init/guard-rules.md
.claude/init/develop.md
.claude/init/review.md
.claude/init/plan.md
.claude/init/AGENTS.md
.claude/init/templates/skill.md
.claude/hooks/preToolUse.sh
.claude/registry.json
.claude/architecture.md
.claude/guidelines.md
.claude/learn-progress.json
.claude/.gitignore
```

### Directories created by Claude Boost (remove ONLY if empty after file deletion):
```
.claude/init/templates/
.claude/init/
.claude/hooks/
.claude/logs/
.claude/skills/
.claude/guidelines/
.claude/plans/
```

### Files that MAY have been modified by Claude Boost:
```
CLAUDE.md              (project root — may have existed before)
.claude/settings.json  (may have existed before — clean, don't delete)
```

### Files Claude Boost NEVER touches (DO NOT DELETE):
```
.claude/settings.local.json
Any file not listed above
Any file outside .claude/ (except root CLAUDE.md)
ALL application code, configs, source files, tests, etc.
```

---

## Step 1: Pre-Flight

### 1.1 Verify Installation
Check that `.claude/init/learn.md` exists. If not, stop — this is not a Claude Boost project.

### 1.2 Check for Uncommitted Changes
Run `git status`. If there are uncommitted changes, tell the user:
> You have uncommitted changes. I recommend committing them first so the
> uninstall is in its own commit. Continue anyway?

Wait for answer.

---

## Step 2: Ask What to Keep

Ask the user:

> **Before I remove Claude Boost, would you like to keep any of these files?**
>
> 1. **CLAUDE.md** — Keep the current version
> 2. **Registry** (.claude/registry.json)
> 3. **Architecture** (.claude/architecture.md)
> 4. **Guidelines** (.claude/guidelines.md)
> 5. **Skills** (.claude/skills/ contents)
> 6. **None** — Remove everything Claude Boost created
>
> Enter numbers to keep (e.g., "1,3") or "none":

Wait for answer. Remember their choices for Step 5.

---

## Step 3: Backup

### 3.1 Create backup directory
Create: `.claude-boost-backup/`

### 3.2 Copy files into backup
Copy each file individually (no recursive copy of unknown directories):

```
cp CLAUDE.md .claude-boost-backup/CLAUDE.md
cp .claude/settings.json .claude-boost-backup/settings.json
cp .claude/registry.json .claude-boost-backup/registry.json
cp .claude/architecture.md .claude-boost-backup/architecture.md
cp .claude/guidelines.md .claude-boost-backup/guidelines.md
cp .claude/hooks/preToolUse.sh .claude-boost-backup/preToolUse.sh
cp .claude/init/learn.md .claude-boost-backup/learn.md
cp .claude/.gitignore .claude-boost-backup/gitignore
```

Only copy files that exist. Skip missing files.

Tell the user:
> Backup created at `.claude-boost-backup/`. You can restore from here if needed.

---

## Step 4: Handle CLAUDE.md

**If user chose to keep CLAUDE.md** — skip this step.

**Otherwise**, check if CLAUDE.md existed before Claude Boost:

Run: `git log --oneline --diff-filter=A -- CLAUDE.md`

This shows the commit that FIRST added CLAUDE.md.

Then run: `git log --oneline --diff-filter=A -- .claude/init/learn.md`

This shows the commit that first added learn.md (the boost install).

**Compare the two commit dates:**

- If CLAUDE.md was added in the SAME commit as learn.md, or AFTER it → CLAUDE.md is ours.
  Ask the user: "CLAUDE.md was created by Claude Boost. Delete it? (yes/no)"
  If yes, delete it.

- If CLAUDE.md existed BEFORE the learn.md commit → CLAUDE.md was modified by us.
  Show the user: "CLAUDE.md existed before Claude Boost. I'll restore the pre-boost version."
  Run: `git log --oneline -- CLAUDE.md` to find commits.
  Find the last commit BEFORE the boost install commit.
  Read the old version using `git show {that-commit}:CLAUDE.md`.
  Write that content to CLAUDE.md using the Write tool.
  Show the user the restored content.

- If no git history available → ask the user:
  "Did you have a CLAUDE.md before installing Claude Boost? Should I delete it or leave it?"

---

## Step 5: Clean settings.json

Read `.claude/settings.json`.

Remove ONLY these specific things:
1. The `"permission_level"` key (top-level)
2. Any entry inside `"hooks"` where the `"command"` value contains `.claude/hooks/`

**Do NOT remove:** `model`, `permissions`, `plansDirectory`, or any other keys.

If after cleaning, the `hooks` object is empty → remove the `hooks` key.
If the entire file is effectively `{}` → delete the file.
Otherwise, write back the cleaned version.

Show the user the before/after.

---

## Step 6: Delete Claude Boost Files

Delete files ONE AT A TIME. For each file, tell the user what you're deleting.

**Ask for confirmation once before starting:**
> I'm going to delete the following Claude Boost files:
> {list only the files that exist AND are not marked "keep"}
>
> Proceed? (yes/no)

Wait for "yes". Then delete each file individually.

**Deletion order:**

1. `.claude/learn-progress.json` (if exists)
2. Each file inside `.claude/logs/` individually, then remove `.claude/logs/` if empty
3. `.claude/hooks/preToolUse.sh`
4. Remove `.claude/hooks/` if empty
5. If skills NOT marked "keep": each `.md` file inside `.claude/skills/` individually
6. Remove `.claude/skills/` if empty
7. If guidelines NOT marked "keep": each file inside `.claude/guidelines/` individually
8. Remove `.claude/guidelines/` if empty
9. If guidelines NOT marked "keep": `.claude/guidelines.md`
10. If architecture NOT marked "keep": `.claude/architecture.md`
11. If registry NOT marked "keep": `.claude/registry.json`
12. `.claude/.gitignore`
13. `.claude/init/templates/skill.md`
14. Remove `.claude/init/templates/` if empty
15. `.claude/init/guard-rules.md`
16. `.claude/init/develop.md`
17. `.claude/init/review.md`
18. `.claude/init/plan.md`
19. `.claude/init/AGENTS.md`
20. `.claude/init/learn.md`
21. `.claude/init/unload.md`
22. Remove `.claude/init/` if empty
23. Remove `.claude/plans/` ONLY if empty
24. Remove `.claude/` ONLY if completely empty

**For "remove directory if empty":** First list the directory contents. If ANY files remain, do NOT remove it.

---

## Step 7: Verify

### 7.1 Show what remains
If `.claude/` still exists, list its contents.
Check if `CLAUDE.md` exists.

### 7.2 Show the diff
Run `git diff --stat` to show a summary of all changes.

### 7.3 Summary
Show the user:

> **Claude Boost has been removed.**
>
> | File | Action |
> |------|--------|
> | {file} | Deleted / Restored / Kept |
> | ... | ... |
>
> Backup at: `.claude-boost-backup/`
>
> **If this is a Laravel project**, also run:
> ```
> composer remove mixvoip/claude-boost
> ```
>
> **Review the changes.** When satisfied:
> ```
> git add -A
> git commit -m "chore: remove Claude Boost"
> ```
>
> **To undo everything:**
> ```
> git checkout -- .
> ```
>
> **Delete backup when verified:**
> ```
> rm -rf .claude-boost-backup/
> ```

---

## FORBIDDEN — Things you must NEVER do during uninstall

- `rm -rf` anything
- `rm -r` anything
- `git checkout -- .` or `git checkout -- {any non-CLAUDE.md file}`
- `git restore` anything
- `git reset` anything
- `git clean` anything
- Delete any file not listed in the ALLOWED FILES section
- Touch any file outside `.claude/` and root `CLAUDE.md`
- Run any command that modifies files outside `.claude/` and root `CLAUDE.md`
- Commit changes
- Push changes
