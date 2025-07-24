<?php

namespace FontAwesome\Migrator\Tests;

use FontAwesome\Migrator\ServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('fontawesome-migrator.license_type', 'free');
        config()->set('fontawesome-migrator.scan_paths', [
            'resources/views',
            'resources/js',
        ]);
        config()->set('fontawesome-migrator.file_extensions', [
            'blade.php',
            'php',
            'html',
            'vue',
            'js',
        ]);
        config()->set('fontawesome-migrator.backup_files', false);
        config()->set('fontawesome-migrator.generate_report', false);
    }
}