<?php

declare(strict_types=1);

namespace ClaudeBoost\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClaudeDoctorCommand extends Command
{
    protected $signature = 'claude:doctor';

    protected $description = 'Check that Claude Boost is set up correctly and everything works';

    private int $passes = 0;
    private int $warnings = 0;
    private int $failures = 0;

    public function handle(): void
    {
        $this->newLine();
        $this->components->info('Claude Boost — Health Check');
        $this->newLine();

        // ── Core files ──────────────────────────────────────────────────
        $this->section('Core Files');
        $this->checkFile('.claude/init/learn.md', 'Learning guide (learn.md)');
        $this->checkFile('.claude/init/guard-rules.md', 'Guard rules reference');
        $this->checkFile('CLAUDE.md', 'Claude instructions (project root)');
        $this->checkFile('.claude/registry.json', 'Codebase registry');

        // ── Settings ────────────────────────────────────────────────────
        $this->section('Settings');
        $this->checkFile('.claude/claude-boost.json', 'Package settings');
        $this->checkSettings();

        // ── Hooks ───────────────────────────────────────────────────────
        $this->section('Hooks');
        $this->checkFile('.claude/hooks/preToolUse.sh', 'Safety guard hook');
        $this->checkFile('.claude/hooks/postToolUse.sh', 'Convention checker hook');
        $this->checkExecutable('.claude/hooks/preToolUse.sh', 'preToolUse.sh executable');
        $this->checkExecutable('.claude/hooks/postToolUse.sh', 'postToolUse.sh executable');
        $this->checkHookRegistered('PreToolUse', '.claude/hooks/preToolUse.sh');
        $this->checkHookRegistered('PostToolUse', '.claude/hooks/postToolUse.sh');

        // ── System dependencies ─────────────────────────────────────────
        $this->section('System Dependencies');
        $this->checkSystemCommand('jq', 'jq (required for guard hooks)');
        $this->checkSystemCommand('git', 'git');

        // ── Registry health ─────────────────────────────────────────────
        $this->section('Registry');
        $this->checkRegistryHealth();

        // ── Learning progress ───────────────────────────────────────────
        $this->section('Learning Progress');
        $this->checkLearningProgress();

        // ── Generated directories ──────────────────────────────────────
        $this->section('Generated Content');
        $this->checkDirectory('.claude/guidelines', 'Guidelines directory');
        $this->checkDirectory('.claude/skills', 'Skills directory');
        $this->checkDirectory('.claude/decisions', 'Decisions directory');

        // ── Framework & Boost ───────────────────────────────────────────
        $this->section('Framework & Integrations');
        $this->checkBoost();

        // ── CLAUDE.md content ──────────────────────────────────────────
        $this->section('CLAUDE.md Content');
        $this->checkClaudeMdContent();

        // ── Summary ─────────────────────────────────────────────────────
        $this->newLine();
        $this->line('  ─────────────────────────────────────');
        $total = $this->passes + $this->warnings + $this->failures;
        $this->line("  <fg=green>{$this->passes} passed</>  <fg=yellow>{$this->warnings} warnings</>  <fg=red>{$this->failures} failed</>  ({$total} checks)");
        $this->newLine();

        if ($this->failures === 0 && $this->warnings === 0) {
            $this->components->info('Everything looks great! Claude is fully project-aware.');
        } elseif ($this->failures === 0) {
            $this->components->warn('Setup works but has minor issues. See warnings above.');
        } else {
            $this->components->error('Run `php artisan claude:init` then prompt Claude with learn.md.');
        }
    }

    private function section(string $name): void
    {
        $this->newLine();
        $this->line("  <fg=cyan>{$name}</>");
    }

    private function pass(string $label): void
    {
        $this->passes++;
        $this->line("    <fg=green>PASS</>  {$label}");
    }

    private function checkWarn(string $label, string $hint = ''): void
    {
        $this->warnings++;
        $this->line("    <fg=yellow>WARN</>  {$label}");
        if ($hint) {
            $this->line("          <fg=gray>{$hint}</>");
        }
    }

    private function checkFail(string $label, string $hint = ''): void
    {
        $this->failures++;
        $this->line("    <fg=red>FAIL</>  {$label}");
        if ($hint) {
            $this->line("          <fg=gray>{$hint}</>");
        }
    }

    private function checkFile(string $relativePath, string $label): void
    {
        if (File::exists(base_path($relativePath))) {
            $this->pass($label);
        } else {
            $this->checkFail("{$label} — missing: {$relativePath}");
        }
    }

    private function checkDirectory(string $relativePath, string $label): void
    {
        $path = base_path($relativePath);
        if (!File::isDirectory($path)) {
            $this->checkWarn("{$label} — not created yet", 'Claude creates this during learn.md execution.');
            return;
        }

        $count = count(File::files($path));
        if ($count === 0) {
            $this->checkWarn("{$label} — empty ({$relativePath})", 'Claude will populate this during learn.md execution.');
        } else {
            $this->pass("{$label} ({$count} files)");
        }
    }

    private function checkExecutable(string $relativePath, string $label): void
    {
        $path = base_path($relativePath);
        if (!File::exists($path)) {
            return; // Already reported by checkFile
        }

        if (is_executable($path)) {
            $this->pass($label);
        } else {
            $this->checkFail("{$label} — not executable", "Fix: chmod +x {$relativePath}");
        }
    }

    private function checkHookRegistered(string $hookType, string $command): void
    {
        $settingsPath = base_path('.claude/settings.json');
        if (!File::exists($settingsPath)) {
            $this->checkFail("{$hookType} hook — .claude/settings.json missing", 'Run: php artisan claude:init');
            return;
        }

        $json = json_decode(File::get($settingsPath), true) ?? [];
        $hooks = $json['hooks'][$hookType] ?? [];

        $found = false;
        foreach ($hooks as $hook) {
            if (($hook['command'] ?? '') === $command) {
                $found = true;
                break;
            }
        }

        if ($found) {
            $this->pass("{$hookType} hook registered");
        } else {
            $this->checkFail("{$hookType} hook — not registered in .claude/settings.json", 'Run: php artisan claude:init');
        }
    }

    private function checkSystemCommand(string $command, string $label): void
    {
        $result = shell_exec("which {$command} 2>/dev/null");
        if (!empty(trim($result ?? ''))) {
            $this->pass($label);
        } else {
            if ($command === 'jq') {
                $this->checkFail("{$label} — NOT INSTALLED", 'Install: brew install jq (macOS) or apt install jq (Linux)');
            } else {
                $this->checkWarn("{$label} — not found in PATH");
            }
        }
    }

    private function checkSettings(): void
    {
        $path = base_path('.claude/claude-boost.json');
        if (!File::exists($path)) {
            return; // Already reported by checkFile
        }

        $settings = json_decode(File::get($path), true);
        if ($settings === null) {
            $this->checkFail('Settings file is corrupt — invalid JSON');
            return;
        }

        $model = $settings['model'] ?? $settings['settings']['model'] ?? null;
        if ($model) {
            $validModels = ['sonnet', 'opus', 'haiku'];
            if (in_array($model, $validModels)) {
                $this->pass("Model: {$model}");
            } else {
                $this->checkWarn("Model '{$model}' is not a recognized family name", 'Valid: sonnet, opus, haiku');
            }
        }

        $level = $settings['permission_level'] ?? $settings['settings']['permission'] ?? null;
        if ($level) {
            $this->pass("Permission level: {$level}");
        }
    }

    private function checkRegistryHealth(): void
    {
        $path = base_path('.claude/registry.json');
        if (!File::exists($path)) {
            $this->checkWarn('Registry not built yet', 'Claude builds this during learn.md execution.');
            return;
        }

        $data = json_decode(File::get($path), true);
        if ($data === null) {
            $this->checkFail('Registry file is corrupt — invalid JSON');
            return;
        }

        $this->pass('Registry file is valid JSON');

        // Count entries across any structure (V2 registry is flexible)
        $total = 0;
        foreach ($data as $key => $value) {
            if ($key === 'meta' || $key === 'duplicates' || $key === 'dependencies') {
                continue;
            }
            if (is_array($value)) {
                $total += count($value);
            }
        }

        if ($total === 0) {
            $this->checkWarn('Registry is empty — no code indexed');
        } else {
            $this->pass("Registry has {$total} entries");
        }

        // Check staleness
        $lastUpdated = $data['meta']['last_updated'] ?? null;
        if ($lastUpdated) {
            $daysSince = (int) ((time() - strtotime($lastUpdated)) / 86400);
            if ($daysSince > 14) {
                $this->checkWarn("Registry last updated {$daysSince} days ago", 'Re-run learn.md to refresh.');
            } else {
                $this->pass("Registry is fresh (updated {$daysSince}d ago)");
            }
        }
    }

    private function checkLearningProgress(): void
    {
        $path = base_path('.claude/learn-progress.json');
        if (!File::exists($path)) {
            $this->checkWarn('No learning progress found', 'Claude creates this when running learn.md.');
            return;
        }

        $progress = json_decode(File::get($path), true);
        if ($progress === null) {
            $this->checkFail('Progress file is corrupt — invalid JSON');
            return;
        }

        $completed = $progress['completed_phases'] ?? [];
        $current = $progress['current_phase'] ?? null;

        if (empty($completed) && $current === null) {
            $this->checkWarn('Learning not started yet');
        } elseif ($current === 'complete' || $current === null && !empty($completed)) {
            $this->pass('Learning complete (' . count($completed) . ' phases done)');
        } else {
            $this->checkWarn("Learning incomplete — stopped at: {$current}", 'Prompt Claude to continue learn.md.');
        }
    }

    private function checkBoost(): void
    {
        if (!File::exists(base_path('artisan'))) {
            $this->pass('Non-Laravel project — Boost not applicable');
            return;
        }

        if (File::isDirectory(base_path('vendor/laravel/boost'))) {
            $this->pass('Laravel Boost installed (MCP tools active)');
        } else {
            $this->checkWarn('Laravel Boost not installed', 'Recommended: composer require laravel/boost --dev');
        }
    }

    private function checkClaudeMdContent(): void
    {
        $path = base_path('CLAUDE.md');
        if (!File::exists($path)) {
            return; // Already reported
        }

        $content = File::get($path);
        $size = strlen($content);

        if ($size < 100) {
            $this->checkWarn('CLAUDE.md seems too short — may not have full project context');
        } else {
            $this->pass("CLAUDE.md has content ({$size} bytes)");
        }

        if (str_contains($content, 'registry.json') || str_contains($content, 'registry')) {
            $this->pass('CLAUDE.md references registry');
        } else {
            $this->checkWarn('CLAUDE.md does not reference registry', 'Claude may not check for duplicates');
        }
    }
}
