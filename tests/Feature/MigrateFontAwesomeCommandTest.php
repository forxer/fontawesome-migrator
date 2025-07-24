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
        File::put($this->testViewsPath . '/test.blade.php', '
            <i class="fas fa-home"></i>
            <i class="far fa-user"></i>
            <i class="fas fa-times"></i>
        ');

        $this->artisan('fontawesome:migrate --dry-run')
            ->expectsOutput('🚀 Démarrage de la migration Font Awesome 5 → 6')
            ->expectsOutput('Mode DRY-RUN activé - Aucune modification ne sera appliquée')
            ->assertExitCode(0);

        // Verify file wasn't actually modified
        $content = File::get($this->testViewsPath . '/test.blade.php');
        $this->assertStringContains('fas fa-home', $content);
        $this->assertStringContains('fas fa-times', $content);
    }

    public function test_can_run_actual_migration(): void
    {
        // Create test file with FA5 icons
        File::put($this->testViewsPath . '/test.blade.php', '
            <i class="fas fa-home"></i>
            <i class="fas fa-times"></i>
        ');

        $this->artisan('fontawesome:migrate')
            ->expectsOutput('🚀 Démarrage de la migration Font Awesome 5 → 6')
            ->assertExitCode(0);

        // Verify file was modified
        $content = File::get($this->testViewsPath . '/test.blade.php');
        $this->assertStringContains('fa-solid fa-house', $content);
        $this->assertStringContains('fa-solid fa-xmark', $content);
    }

    public function test_can_migrate_specific_path(): void
    {
        // Create test files in different paths
        File::makeDirectory(base_path('resources/js'), 0755, true);
        File::put($this->testViewsPath . '/views-test.blade.php', '<i class="fas fa-home"></i>');
        File::put(base_path('resources/js/js-test.js'), 'icon: "fas fa-home"');

        $this->artisan('fontawesome:migrate --path=resources/views')
            ->assertExitCode(0);

        // Verify only views file was processed
        $viewsContent = File::get($this->testViewsPath . '/views-test.blade.php');
        $jsContent = File::get(base_path('resources/js/js-test.js'));
        
        $this->assertStringContains('fa-solid fa-house', $viewsContent);
        $this->assertStringContains('fas fa-home', $jsContent); // Should remain unchanged
    }

    public function test_shows_verbose_output_when_requested(): void
    {
        File::put($this->testViewsPath . '/test.blade.php', '<i class="fas fa-times"></i>');

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
        File::put($this->testViewsPath . '/test.blade.php', '<i class="fas fa-times"></i>');
        
        // Mock storage directory
        File::makeDirectory(storage_path('fontawesome-migrator/reports'), 0755, true, true);

        $this->artisan('fontawesome:migrate --report --dry-run')
            ->expectsOutput('📊 Rapport généré dans')
            ->assertExitCode(0);
    }
}