<?php

declare(strict_types=1);

namespace ClaudeBoost\Tests\Feature;

use Illuminate\Support\Facades\File;
use ClaudeBoost\Tests\TestCase;

class CommandsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->ensureClaudeDirectory();
    }

    protected function tearDown(): void
    {
        $this->cleanupClaudeDirectory();
        parent::tearDown();
    }

    private function ensureClaudeDirectory(): void
    {
        File::ensureDirectoryExists(base_path('.claude'));
        File::ensureDirectoryExists(base_path('.claude/init'));
        File::ensureDirectoryExists(base_path('.claude/hooks'));
        File::ensureDirectoryExists(base_path('.claude/skills'));
        File::ensureDirectoryExists(base_path('.claude/decisions'));
        File::ensureDirectoryExists(base_path('.claude/plans'));
        File::ensureDirectoryExists(base_path('.claude/logs'));
    }

    private function cleanupClaudeDirectory(): void
    {
        $paths = [
            base_path('.claude/claude-boost.json'),
            base_path('.claude/registry.json'),
            base_path('.claude/CLAUDE.md'),
            base_path('.claude/guard-rules.yaml'),
            base_path('.claude/.gitignore'),
            base_path('.claude/settings.json'),
            base_path('.claude/learn-progress.json'),
        ];

        foreach ($paths as $path) {
            if (File::exists($path)) {
                File::delete($path);
            }
        }
    }

    // ── claude:init ─────────────────────────────────────────────────────

    public function test_init_creates_directory_structure(): void
    {
        $this->artisan('claude:init', ['--force' => true])
            ->assertSuccessful();

        $this->assertDirectoryExists(base_path('.claude'));
        $this->assertDirectoryExists(base_path('.claude/init'));
        $this->assertDirectoryExists(base_path('.claude/hooks'));
        $this->assertDirectoryExists(base_path('.claude/skills'));
        $this->assertDirectoryExists(base_path('.claude/decisions'));
    }

    public function test_init_installs_learn_md(): void
    {
        $this->artisan('claude:init', ['--force' => true])
            ->assertSuccessful();

        $this->assertFileExists(base_path('.claude/init/learn.md'));
    }

    public function test_init_installs_hooks(): void
    {
        $this->artisan('claude:init', ['--force' => true])
            ->assertSuccessful();

        $this->assertFileExists(base_path('.claude/hooks/preToolUse.sh'));
        $this->assertFileExists(base_path('.claude/hooks/postToolUse.sh'));
        $this->assertTrue(is_executable(base_path('.claude/hooks/preToolUse.sh')));
        $this->assertTrue(is_executable(base_path('.claude/hooks/postToolUse.sh')));
    }

    public function test_init_registers_hooks_in_settings(): void
    {
        $this->artisan('claude:init', ['--force' => true])
            ->assertSuccessful();

        $settingsPath = base_path('.claude/settings.json');
        $this->assertFileExists($settingsPath);

        $settings = json_decode(File::get($settingsPath), true);
        $this->assertArrayHasKey('hooks', $settings);
        $this->assertArrayHasKey('PreToolUse', $settings['hooks']);
        $this->assertArrayHasKey('PostToolUse', $settings['hooks']);
    }

    // ── claude:doctor ───────────────────────────────────────────────────

    public function test_doctor_runs_successfully(): void
    {
        $this->artisan('claude:doctor')
            ->expectsOutputToContain('Health Check')
            ->assertSuccessful();
    }

    // ── claude:update ───────────────────────────────────────────────────

    public function test_update_requires_init(): void
    {
        // Remove .claude directory to simulate uninitialized state
        File::deleteDirectory(base_path('.claude'));

        $this->artisan('claude:update')
            ->expectsOutputToContain('not initialized')
            ->assertFailed();

        // Recreate for tearDown
        $this->ensureClaudeDirectory();
    }

    public function test_update_refreshes_hooks(): void
    {
        // Create initial state
        File::ensureDirectoryExists(base_path('.claude/hooks'));

        $this->artisan('claude:update')
            ->assertSuccessful();

        $this->assertFileExists(base_path('.claude/hooks/preToolUse.sh'));
        $this->assertFileExists(base_path('.claude/hooks/postToolUse.sh'));
    }
}
