<?php

declare(strict_types=1);

namespace ClaudeBoost\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use ClaudeBoost\ClaudeBoostServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            ClaudeBoostServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('claude-boost.package_name', 'mixvoip/claude-boost');
    }
}
