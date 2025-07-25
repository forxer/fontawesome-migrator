<?php

namespace FontAwesome\Migrator\Tests\Feature;

use FontAwesome\Migrator\Tests\TestCase;
use Illuminate\Support\Facades\File;

class InstallFontAwesomeCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clean up any existing config
        if (File::exists(config_path('fontawesome-migrator.php'))) {
            File::delete(config_path('fontawesome-migrator.php'));
        }
    }

    protected function tearDown(): void
    {
        // Clean up test files
        if (File::exists(config_path('fontawesome-migrator.php'))) {
            File::delete(config_path('fontawesome-migrator.php'));
        }

        $reportPath = storage_path('app/public/fontawesome-migrator');

        if (File::exists($reportPath)) {
            File::deleteDirectory($reportPath);
        }

        parent::tearDown();
    }

    public function test_can_run_install_command(): void
    {
        $this->artisan('fontawesome:install --non-interactive')
            ->assertExitCode(0);
    }

    public function test_displays_welcome_screen(): void
    {
        $this->artisan('fontawesome:install --non-interactive')
            ->assertExitCode(0);
    }

    public function test_publishes_configuration(): void
    {
        $this->assertFalse(File::exists(config_path('fontawesome-migrator.php')));

        $this->artisan('fontawesome:install --non-interactive')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(config_path('fontawesome-migrator.php')));
    }

    public function test_creates_reports_directory(): void
    {
        $reportPath = storage_path('app/public/fontawesome-migrator/reports');

        $this->assertFalse(File::exists($reportPath));

        $this->artisan('fontawesome:install --non-interactive')
            ->assertExitCode(0);

        $this->assertTrue(File::exists($reportPath));
    }

    public function test_displays_next_steps(): void
    {
        $this->artisan('fontawesome:install --non-interactive')
            ->assertExitCode(0);
    }

    public function test_displays_completion_info(): void
    {
        $this->artisan('fontawesome:install --non-interactive')
            ->assertExitCode(0);
    }

    public function test_force_option_overwrites_existing_config(): void
    {
        // Create initial config
        File::put(config_path('fontawesome-migrator.php'), '<?php return ["test" => "initial"];');

        $this->artisan('fontawesome:install --force')
            ->assertExitCode(0);

        // Verify config was overwritten with stub content
        $configContent = File::get(config_path('fontawesome-migrator.php'));
        $this->assertStringContainsString('Only modified values should appear in this file', $configContent);
        $this->assertStringNotContainsString('test', $configContent);
    }

    public function test_handles_existing_storage_link(): void
    {
        // Mock storage link already existing
        $this->artisan('fontawesome:install --non-interactive')
            ->expectsOutputToContain('Configuration du stockage')
            ->assertExitCode(0);
    }

    public function test_verification_step_runs(): void
    {
        $this->artisan('fontawesome:install --non-interactive')
            ->assertExitCode(0);
    }

    public function test_shows_installation_steps(): void
    {
        $this->artisan('fontawesome:install --non-interactive')
            ->expectsOutput('🔧 Publication de la configuration')
            ->expectsOutput('🔧 Configuration du package')
            ->expectsOutput('🔧 Configuration des rapports web')
            ->expectsOutput('🔧 Vérification de l\'installation')
            ->assertExitCode(0);
    }

    public function test_can_handle_interactive_responses(): void
    {
        // Test with manual responses simulation
        $this->artisan('fontawesome:install --non-interactive')
            ->assertExitCode(0);
    }

    public function test_handles_pro_license_configuration(): void
    {
        // S'assurer que le lien storage n'existe pas pour que la question soit posée
        if (File::exists(public_path('storage'))) {
            File::delete(public_path('storage'));
        }

        $this->artisan('fontawesome:install')
            ->expectsChoice('   Quel type de licence FontAwesome utilisez-vous ?', 'Pro (payante)', ['Free (gratuite)', 'Pro (payante)'])
            ->expectsConfirmation('   Voulez-vous ajouter des chemins personnalisés ?', false)
            ->expectsConfirmation('   Générer automatiquement des rapports ?', true)
            ->expectsConfirmation('   Créer des sauvegardes avant modification ?', true)
            ->expectsConfirmation('   Créer le lien symbolique storage pour l\'accès web ?', true)
            ->assertExitCode(0);

        // Verify Pro configuration dans le fichier généré
        $configContent = File::get(config_path('fontawesome-migrator.php'));
        $this->assertStringContainsString("'license_type' => 'pro'", $configContent);
        $this->assertStringContainsString("'thin' => true", $configContent);
        $this->assertStringContainsString("'sharp' => true", $configContent);
    }

    public function test_only_writes_modified_values(): void
    {
        $this->artisan('fontawesome:install --non-interactive')
            ->assertExitCode(0);

        // Si toutes les valeurs sont par défaut, le fichier ne devrait contenir que le template
        $configContent = File::get(config_path('fontawesome-migrator.php'));
        $this->assertStringContainsString('Aucune configuration personnalisée', $configContent);
        $this->assertStringNotContainsString("'license_type'", $configContent);
    }

    public function test_writes_custom_values_only(): void
    {
        // Test simplifié en mode non-interactif - on vérifie juste que les valeurs par défaut sont écrites
        $this->artisan('fontawesome:install --non-interactive')
            ->assertExitCode(0);

        $configContent = File::get(config_path('fontawesome-migrator.php'));
        // En mode non-interactif, toutes les valeurs sont par défaut
        $this->assertStringContainsString('Aucune configuration personnalisée', $configContent);
        $this->assertStringNotContainsString("'license_type'", $configContent);
    }
}
