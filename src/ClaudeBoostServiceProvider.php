<?php

declare(strict_types=1);

namespace ClaudeBoost;

use Illuminate\Support\ServiceProvider;
use ClaudeBoost\Commands\ClaudeInitCommand;
use ClaudeBoost\Commands\ClaudeDoctorCommand;
use ClaudeBoost\Commands\ClaudeUpdateCommand;

class ClaudeBoostServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/claude-boost.php',
            'claude-boost'
        );
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/claude-boost.php' => config_path('claude-boost.php'),
            ], 'claude-boost-config');

            $this->commands([
                ClaudeInitCommand::class,
                ClaudeDoctorCommand::class,
                ClaudeUpdateCommand::class,
            ]);
        }
    }
}
