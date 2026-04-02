<?php

declare(strict_types=1);

namespace ClaudeBoost\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClaudeUpdateCommand extends Command
{
    protected $signature = 'claude:update';

    protected $description = 'Update Claude Boost after a package upgrade — refreshes hooks and learn.md';

    public function handle(): void
    {
        $this->newLine();
        $this->components->info('Updating Claude Boost...');
        $this->newLine();

        if (!File::isDirectory(base_path('.claude'))) {
            $this->components->error('Package not initialized. Run `php artisan claude:init` first.');
            return;
        }

        // ── Update learn.md to latest ───────────────────────────────────
        $this->components->task('Updating learn.md', function () {
            $source = __DIR__ . '/../../.claude/init/learn.md';
            $target = base_path('.claude/init/learn.md');

            if (File::exists($source)) {
                File::ensureDirectoryExists(dirname($target));
                File::copy($source, $target);
            }
        });

        // ── Update guard rules reference ────────────────────────────────
        $this->components->task('Updating guard rules reference', function () {
            $source = __DIR__ . '/../../.claude/init/guard-rules.md';
            $target = base_path('.claude/init/guard-rules.md');

            if (File::exists($source)) {
                File::copy($source, $target);
            }
        });

        // ── Update templates ────────────────────────────────────────────
        $this->components->task('Updating templates', function () {
            $templates = ['skill.md', 'decision.md'];
            foreach ($templates as $template) {
                $source = __DIR__ . "/../../.claude/init/templates/{$template}";
                $target = base_path(".claude/init/templates/{$template}");
                if (File::exists($source)) {
                    File::ensureDirectoryExists(dirname($target));
                    File::copy($source, $target);
                }
            }
        });

        // ── Update guard hooks to latest ────────────────────────────────
        $this->components->task('Updating safety guard hooks', function () {
            $hooks = ['preToolUse.sh', 'postToolUse.sh'];
            foreach ($hooks as $hook) {
                $source = __DIR__ . "/../../stubs/hooks/{$hook}";
                $target = base_path(".claude/hooks/{$hook}");
                if (File::exists($source)) {
                    File::ensureDirectoryExists(dirname($target));
                    File::copy($source, $target);
                    chmod($target, 0755);
                }
            }
        });

        // ── Update guard-rules.yaml ─────────────────────────────────────
        $guardYamlSource = __DIR__ . '/../../stubs/.claude/guard-rules.yaml';
        $guardYamlTarget = base_path('.claude/guard-rules.yaml');
        if (File::exists($guardYamlSource)) {
            if (!File::exists($guardYamlTarget)) {
                File::copy($guardYamlSource, $guardYamlTarget);
            } else {
                $this->line('  <fg=gray>guard-rules.yaml preserved (your customizations kept).</>');
            }
        }

        // ── Update package version ──────────────────────────────────────
        $newVersion = $this->getPackageVersion();
        $settingsPath = base_path('.claude/claude-boost.json');
        if (File::exists($settingsPath)) {
            $settings = json_decode(File::get($settingsPath), true) ?? [];
            $oldVersion = $settings['version'] ?? 'unknown';
            $settings['version'] = $newVersion;
            $settings['last_updated'] = now()->toIso8601String();
            File::put($settingsPath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->newLine();
            $this->components->info("Package updated: {$oldVersion} → {$newVersion}");
        }

        // ── Summary ─────────────────────────────────────────────────────
        $this->newLine();
        $this->components->info('Update complete!');
        $this->newLine();
        $this->line('  <fg=gray>Updated: learn.md, guard rules, templates, hooks.</>');
        $this->line('  <fg=gray>Your registry, CLAUDE.md, guidelines, skills, and decisions are preserved.</>');
        $this->newLine();
        $this->components->warn('To refresh project knowledge, prompt Claude:');
        $this->newLine();
        $this->line('  <fg=cyan>claude "Read .claude/init/learn.md and execute every task in it"</>');
        $this->newLine();
        $this->line('  <fg=gray>Claude will resume from where it left off if progress exists.</>');
        $this->newLine();
    }

    private function getPackageVersion(): string
    {
        $composerLock = base_path('composer.lock');
        if (File::exists($composerLock)) {
            $lock = json_decode(File::get($composerLock), true);
            $packages = array_merge($lock['packages'] ?? [], $lock['packages-dev'] ?? []);
            foreach ($packages as $package) {
                if (($package['name'] ?? '') === 'ualimxvp/claude-boost') {
                    return $package['version'] ?? 'dev';
                }
            }
        }
        return 'dev';
    }
}
