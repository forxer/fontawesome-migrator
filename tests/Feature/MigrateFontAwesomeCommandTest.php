<?php

namespace FontAwesome\Migrator\Tests\Feature;

use FontAwesome\Migrator\Tests\TestCase;
use Illuminate\Support\Facades\File;

class MigrateFontAwesomeCommandTest extends TestCase
{
    private string $testViewsPath;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testViewsPath = base_path('resources/views');

        // Create test directory
        File::makeDirectory($this->testViewsPath, 0755, true, true);
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (File::exists(base_path('resources'))) {
            File::deleteDirectory(base_path('resources'));
        }

        parent::tearDown();
    }

    public function test_can_run_migration_command_in_dry_run_mode(): void
    {
        // Create test file with FA5 icons
        File::put($this->testViewsPath.'/test.blade.php', '
            <i class="fas fa-home"></i>
            <i class="far fa-user"></i>
            <i class="fas fa-times"></i>
        ');

        $this->artisan('fontawesome:migrate --dry-run')
            ->expectsOutput('🚀 Démarrage de la migration Font Awesome 5 → 6')
            ->expectsOutput('Mode DRY-RUN activé - Aucune modification ne sera appliquée')
            ->expectsOutput('🔄 Mode complet - Migration des icônes ET des assets')
            ->assertExitCode(0);

        // Verify file wasn't actually modified
        $content = File::get($this->testViewsPath.'/test.blade.php');
        $this->assertStringContainsString('fas fa-home', $content);
        $this->assertStringContainsString('fas fa-times', $content);
    }

    public function test_can_run_actual_migration(): void
    {
        // Create test file with FA5 icons
        File::put($this->testViewsPath.'/test.blade.php', '
            <i class="fas fa-home"></i>
            <i class="fas fa-times"></i>
        ');

        $this->artisan('fontawesome:migrate')
            ->expectsOutput('🚀 Démarrage de la migration Font Awesome 5 → 6')
            ->assertExitCode(0);

        // Verify file was modified
        $content = File::get($this->testViewsPath.'/test.blade.php');
        $this->assertStringContainsString('fa-solid fa-house', $content);
        $this->assertStringContainsString('fa-solid fa-xmark', $content);
    }

    public function test_can_migrate_specific_path(): void
    {
        // Create test files in different paths
        File::makeDirectory(base_path('resources/js'), 0755, true);
        File::put($this->testViewsPath.'/views-test.blade.php', '<i class="fas fa-home"></i>');
        File::put(base_path('resources/js/js-test.js'), 'icon: "fas fa-home"');

        $this->artisan('fontawesome:migrate --path=resources/views')
            ->assertExitCode(0);

        // Verify only views file was processed
        $viewsContent = File::get($this->testViewsPath.'/views-test.blade.php');
        $jsContent = File::get(base_path('resources/js/js-test.js'));

        $this->assertStringContainsString('fa-solid fa-house', $viewsContent);
        $this->assertStringContainsString('fas fa-home', $jsContent); // Should remain unchanged
    }

    public function test_shows_verbose_output_when_requested(): void
    {
        File::put($this->testViewsPath.'/test.blade.php', '<i class="fas fa-times"></i>');

        $this->artisan('fontawesome:migrate --dry-run --verbose')
            ->expectsOutput('📝 Détail des changements :')
            ->assertExitCode(0);
    }

    public function test_handles_no_files_found_gracefully(): void
    {
        // Empty directory
        $this->artisan('fontawesome:migrate')
            ->expectsOutput('Aucun fichier trouvé à analyser.')
            ->assertExitCode(0);
    }

    public function test_validates_configuration(): void
    {
        config()->set('fontawesome-migrator.license_type', 'invalid');

        $this->artisan('fontawesome:migrate')
            ->expectsOutput('Type de licence invalide. Utilisez "free" ou "pro".')
            ->assertExitCode(1);
    }

    public function test_validates_scan_paths_configuration(): void
    {
        config()->set('fontawesome-migrator.scan_paths', []);

        $this->artisan('fontawesome:migrate')
            ->expectsOutput('Aucun chemin de scan configuré.')
            ->assertExitCode(1);
    }

    public function test_can_generate_report(): void
    {
        File::put($this->testViewsPath.'/test.blade.php', '<i class="fas fa-times"></i>');

        // Mock storage directory
        File::makeDirectory(storage_path('fontawesome-migrator/reports'), 0755, true, true);

        $this->artisan('fontawesome:migrate --report --dry-run')
            ->expectsOutputToContain('📊 Rapport généré dans')
            ->assertExitCode(0);
    }

    public function test_can_migrate_assets_only(): void
    {
        // Create test CSS file with FA5 CDN
        File::makeDirectory(base_path('resources/css'), 0755, true);
        File::put(base_path('resources/css/app.css'), '
            @import url("https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css");
            .icon { font-family: "Font Awesome 5 Free"; }
        ');

        // Create test JS file with FA5 imports
        File::makeDirectory(base_path('resources/js'), 0755, true);
        File::put(base_path('resources/js/app.js'), '
            import { faHome } from "@fortawesome/fontawesome-free-solid";
            const icons = require("@fortawesome/fontawesome-free-regular");
        ');

        $this->artisan('fontawesome:migrate --assets-only')
            ->expectsOutput('🚀 Démarrage de la migration Font Awesome 5 → 6')
            ->expectsOutput('🎨 Mode assets uniquement - Migration des références CSS/JS/CDN')
            ->assertExitCode(0);

        // Verify CSS was migrated
        $cssContent = File::get(base_path('resources/css/app.css'));
        $this->assertStringContainsString('font-awesome/6.15.4', $cssContent);

        // Verify JS was migrated
        $jsContent = File::get(base_path('resources/js/app.js'));
        $this->assertStringContainsString('@fortawesome/free-solid-svg-icons', $jsContent);
        $this->assertStringContainsString('@fortawesome/free-regular-svg-icons', $jsContent);
    }

    public function test_can_migrate_icons_only(): void
    {
        // Create test file with both icons and assets
        File::put($this->testViewsPath.'/mixed.blade.php', '
            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
            <i class="fas fa-home"></i>
            <i class="fas fa-times"></i>
        ');

        $this->artisan('fontawesome:migrate --icons-only')
            ->expectsOutput('🚀 Démarrage de la migration Font Awesome 5 → 6')
            ->expectsOutput('🎯 Mode icônes uniquement - Migration des classes d\'icônes')
            ->assertExitCode(0);

        $content = File::get($this->testViewsPath.'/mixed.blade.php');

        // Icons should be migrated
        $this->assertStringContainsString('fa-solid fa-house', $content);
        $this->assertStringContainsString('fa-solid fa-xmark', $content);

        // CDN link should NOT be migrated
        $this->assertStringContainsString('font-awesome/5.15.4', $content);
    }

    public function test_complete_migration_with_assets_and_icons(): void
    {
        // Create test file with both
        File::put($this->testViewsPath.'/complete.blade.php', '
            <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
            <i class="fas fa-home"></i>
            <i class="fas fa-times"></i>
        ');

        $this->artisan('fontawesome:migrate')
            ->expectsOutput('🚀 Démarrage de la migration Font Awesome 5 → 6')
            ->expectsOutput('🔄 Mode complet - Migration des icônes ET des assets')
            ->assertExitCode(0);

        $content = File::get($this->testViewsPath.'/complete.blade.php');

        // Both should be migrated
        $this->assertStringContainsString('fa-solid fa-house', $content);
        $this->assertStringContainsString('fa-solid fa-xmark', $content);
        $this->assertStringContainsString('releases/v6.15.4', $content);
    }

    public function test_pro_assets_migration(): void
    {
        // Set Pro license
        config(['fontawesome-migrator.license_type' => 'pro']);

        // Create test file with Pro assets
        File::makeDirectory(base_path('resources/js'), 0755, true);
        File::put(base_path('resources/js/pro.js'), '
            import { faHome } from "@fortawesome/fontawesome-pro-solid";
            const lightIcons = require("@fortawesome/fontawesome-pro-light");
        ');

        $this->artisan('fontawesome:migrate --assets-only')
            ->expectsOutput('🚀 Démarrage de la migration Font Awesome 5 → 6')
            ->expectsOutput('🎨 Mode assets uniquement - Migration des références CSS/JS/CDN')
            ->assertExitCode(0);

        $content = File::get(base_path('resources/js/pro.js'));
        $this->assertStringContainsString('@fortawesome/pro-solid-svg-icons', $content);
        $this->assertStringContainsString('@fortawesome/pro-light-svg-icons', $content);
    }

    public function test_validates_mutually_exclusive_options(): void
    {
        $this->artisan('fontawesome:migrate --icons-only --assets-only')
            ->expectsOutput('Les options --icons-only et --assets-only sont mutuellement exclusives')
            ->assertExitCode(1);
    }
}
