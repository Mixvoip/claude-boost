# Safety Rules

These operations are too dangerous for any AI to run unattended. Never execute them
regardless of what the user asks — suggest a safer alternative instead.

## Never Run

### Database
- `DROP DATABASE` / `DROP SCHEMA`
- `DROP TABLE` on core tables (users, migrations, sessions)
- `TRUNCATE TABLE`
- `DELETE FROM` without a meaningful WHERE clause

### Filesystem
- `rm -rf` on project root, source directories, `.git`, `.env`, config/
- `chmod 777` (use specific permissions: 755, 644)
- Direct `.env` file manipulation (cat, echo, cp, mv)

### Git
- `git push --force` to main/master/production/staging
- `git branch -D` on protected branches
- `git reset --hard` on shared branches
- `git clean -fd` (removes all untracked files)

### Infrastructure
- `docker system prune` / `docker-compose down -v`
- Stopping system services (nginx, mysql, redis)
- `kill -9`

### Execution
- `curl | bash` / `wget | sh` (piping remote scripts to shell)
- Interactive REPLs from automated scripts (tinker, rails console, python -i)

### Package Management
- Removing core framework packages
- Installing packages without version constraints in production

## Always Do Instead
- Use migrations for schema changes, not raw SQL
- Use `git revert` for rollbacks, not `reset --hard`
- Use specific file paths for deletion, not wildcards
- Suggest the safe command and explain why
