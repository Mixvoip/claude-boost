<?php

declare(strict_types=1);

namespace ClaudeBoost\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClaudeInitCommand extends Command
{
    protected $signature = 'claude:init
        {--force : Overwrite existing .claude directory}';

    protected $description = 'Initialize Claude Boost — sets up learn.md for Claude to scan and learn your project';

    public function handle(): void
    {
        $this->newLine();
        $this->components->info('Claude Boost — Setup');
        $this->line('  One file. Zero commands. Claude does the rest.');
        $this->newLine();

        // ── Check existing installation ─────────────────────────────────
        $claudeDir = base_path('.claude');
        $freshInstall = true;

        if (File::isDirectory($claudeDir) && !$this->option('force')) {
            $this->line('  <fg=yellow>Existing .claude/ directory detected.</>');
            $this->newLine();

            $choice = $this->choice(
                'How should we handle your existing setup?',
                [
                    'Fresh install — replace with latest package files',
                    'Merge — keep your customizations, update hooks and learn.md only',
                ],
                1
            );

            $freshInstall = str_starts_with($choice, 'Fresh');

            if ($freshInstall) {
                $this->components->warn('Fresh install — .claude/init/ and hooks will be replaced.');
                $this->line('  <fg=gray>Your CLAUDE.md, registry.json, skills, and architecture are preserved.</>');
                if (!$this->confirm('Continue?', false)) {
                    $this->components->warn('Aborted.');
                    return;
                }
            }
        }

        // ── Scaffold directories ────────────────────────────────────────
        $this->components->task('Creating .claude directory structure', function () {
            $dirs = [
                '.claude',
                '.claude/init',
                '.claude/init/templates',
                '.claude/skills',
                '.claude/plans',
                '.claude/hooks',
                '.claude/logs',
            ];

            foreach ($dirs as $dir) {
                File::ensureDirectoryExists(base_path($dir));
            }

            // .gitignore
            $gitignorePath = base_path('.claude/.gitignore');
            File::put($gitignorePath, "logs/\nsettings.local.json\nlearn-progress.json\n");
        });

        // ── Copy learn.md (THE product) ─────────────────────────────────
        $this->components->task('Installing learn.md', function () {
            $source = __DIR__ . '/../../.claude/init/learn.md';
            $target = base_path('.claude/init/learn.md');

            if (File::exists($source)) {
                File::copy($source, $target);
            }
        });

        // ── Copy guard rules ────────────────────────────────────────────
        $this->components->task('Installing guard rules', function () {
            $source = __DIR__ . '/../../.claude/init/guard-rules.md';
            $target = base_path('.claude/init/guard-rules.md');

            if (File::exists($source)) {
                File::copy($source, $target);
            }
        });

        // ── Copy templates ──────────────────────────────────────────────
        $this->components->task('Installing templates', function () {
            $templates = ['skill.md'];
            foreach ($templates as $template) {
                $source = __DIR__ . "/../../.claude/init/templates/{$template}";
                $target = base_path(".claude/init/templates/{$template}");
                if (File::exists($source)) {
                    File::copy($source, $target);
                }
            }
        });

        // ── Install safety hooks ────────────────────────────────────────
        $this->components->task('Installing safety hooks', function () use ($freshInstall) {
            // Copy hook files
            $hooks = ['preToolUse.sh'];
            foreach ($hooks as $hook) {
                $source = __DIR__ . "/../../stubs/hooks/{$hook}";
                $target = base_path(".claude/hooks/{$hook}");
                if (File::exists($source)) {
                    File::copy($source, $target);
                    chmod($target, 0755);
                }
            }

            // Register hooks in .claude/settings.json
            $this->registerHooks($freshInstall);
        });

        // ── Laravel Boost ───────────────────────────────────────────────
        $this->offerBoost();

        // ── Summary ─────────────────────────────────────────────────────
        $this->newLine();
        $this->components->info('Setup complete!');
        $this->newLine();

        $this->line('  <fg=gray>Files installed:</>');
        $this->line('    .claude/init/learn.md          — The learning guide (Claude reads this)');
        $this->line('    .claude/init/guard-rules.md    — Safety rules reference');
        $this->line('    .claude/init/templates/        — Skill template');
        $this->line('    .claude/hooks/preToolUse.sh    — Safety guard (always active)');
        $this->newLine();

        $this->components->warn('Next step — paste this into Claude:');
        $this->newLine();
        $this->line('  <fg=cyan>claude "Read .claude/init/learn.md and execute every task in it"</>');
        $this->newLine();
        $this->line('  Claude will interactively:');
        $this->line('    1. Detect your stack and ask your preferences');
        $this->line('    2. Scan your entire codebase');
        $this->line('    3. Build a registry (anti-duplication)');
        $this->line('    4. Detect duplicates');
        $this->line('    5. Learn your conventions');
        $this->line('    6. Write CLAUDE.md (loaded every session)');
        $this->line('    7. Create skill files for complex modules');
        $this->newLine();
        $this->line('  <fg=gray>If interrupted, just run the same command again — Claude resumes where it left off.</>');
        $this->newLine();
        $this->components->warn('Add .claude/ to Git so the whole team benefits.');
        $this->line('  <fg=gray>Exception: .claude/logs/ and learn-progress.json are gitignored.</>');
        $this->newLine();
    }

    private function registerHooks(bool $freshInstall): void
    {
        $settingsPath = base_path('.claude/settings.json');
        $settings = [];

        if (File::exists($settingsPath)) {
            $settings = json_decode(File::get($settingsPath), true) ?? [];
        }

        $settings['hooks'] = $settings['hooks'] ?? [];

        $hookMap = [
            'PreToolUse' => '.claude/hooks/preToolUse.sh',
        ];

        foreach ($hookMap as $hookType => $command) {
            $newHook = ['type' => 'command', 'command' => $command];

            if ($freshInstall) {
                // Replace existing hooks of this type
                $existing = $settings['hooks'][$hookType] ?? [];
                // Filter out our hooks, keep third-party hooks
                $filtered = array_filter($existing, fn($h) => !str_contains($h['command'] ?? '', 'claude-boost') && !str_contains($h['command'] ?? '', '.claude/hooks/'));
                $filtered[] = $newHook;
                $settings['hooks'][$hookType] = array_values($filtered);
            } else {
                // Merge — add only if not already registered
                $existing = $settings['hooks'][$hookType] ?? [];
                $alreadyRegistered = false;
                foreach ($existing as $hook) {
                    if (($hook['command'] ?? '') === $command) {
                        $alreadyRegistered = true;
                        break;
                    }
                }
                if (!$alreadyRegistered) {
                    $existing[] = $newHook;
                    $settings['hooks'][$hookType] = $existing;
                }
            }
        }

        File::put($settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function offerBoost(): void
    {
        // Only for Laravel projects
        if (!File::exists(base_path('artisan'))) {
            return;
        }

        if (File::isDirectory(base_path('vendor/laravel/boost'))) {
            $this->newLine();
            $this->components->info('Laravel Boost detected — Claude will have live MCP access.');
            return;
        }

        $this->newLine();
        $this->line('  <fg=cyan>Laravel Boost</> gives Claude live access to routes, schema, models, and config.');

        if ($this->confirm('Install Laravel Boost? (recommended for Laravel projects)', true)) {
            $this->components->task('Installing Laravel Boost', function () {
                $process = new \Symfony\Component\Process\Process(
                    ['composer', 'require', 'laravel/boost', '--dev'],
                );
                $process->setWorkingDirectory(base_path());
                $process->setTimeout(120);
                $process->run();

                return $process->isSuccessful();
            });

            if (File::isDirectory(base_path('vendor/laravel/boost'))) {
                $this->newLine();
                $this->components->info('Running boost:install...');

                $boostInstall = new \Symfony\Component\Process\Process(
                    ['php', 'artisan', 'boost:install'],
                );
                $boostInstall->setWorkingDirectory(base_path());
                $boostInstall->setTimeout(120);
                $boostInstall->setTty(\Symfony\Component\Process\Process::isTtySupported());
                $boostInstall->run(function ($type, $buffer) {
                    $this->output->write($buffer);
                });

                if (!$boostInstall->isSuccessful()) {
                    $this->newLine();
                    $this->components->warn('boost:install did not complete. Run manually: php artisan boost:install');
                }
            }
        }
    }
}
