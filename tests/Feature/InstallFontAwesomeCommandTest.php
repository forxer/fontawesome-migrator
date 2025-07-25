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
        $this->artisan('fontawesome:install')
            ->expectsOutput('🚀 FontAwesome Migrator - Installation Interactive')
            ->expectsOutput('🎉 Installation terminée avec succès !')
            ->assertExitCode(0);
    }

    public function test_displays_welcome_screen(): void
    {
        $this->artisan('fontawesome:install')
            ->expectsOutput('🚀 FontAwesome Migrator - Installation Interactive')
            ->expectsOutput('Migration automatique Font Awesome 5 → 6')
            ->expectsOutput('Support Free & Pro • Assets & Icônes • Interface Web')
            ->assertExitCode(0);
    }

    public function test_publishes_configuration(): void
    {
        $this->assertFalse(File::exists(config_path('fontawesome-migrator.php')));

        $this->artisan('fontawesome:install')
            ->assertExitCode(0);

        $this->assertTrue(File::exists(config_path('fontawesome-migrator.php')));
    }

    public function test_creates_reports_directory(): void
    {
        $reportPath = storage_path('app/public/fontawesome-migrator/reports');

        $this->assertFalse(File::exists($reportPath));

        $this->artisan('fontawesome:install')
            ->assertExitCode(0);

        $this->assertTrue(File::exists($reportPath));
    }

    public function test_displays_next_steps(): void
    {
        $this->artisan('fontawesome:install')
            ->expectsOutput('📋 Prochaines étapes :')
            ->expectsOutput('php artisan fontawesome:migrate --dry-run')
            ->expectsOutput('php artisan fontawesome:migrate')
            ->expectsOutputToContain('/fontawesome-migrator/reports')
            ->assertExitCode(0);
    }

    public function test_displays_completion_info(): void
    {
        $this->artisan('fontawesome:install')
            ->expectsOutput('📖 Documentation complète :')
            ->expectsOutput('🆘 Support :')
            ->expectsOutput('php artisan fontawesome:migrate --help')
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
        $this->assertStringContainsString('Ce fichier contient uniquement les paramètres personnalisés', $configContent);
        $this->assertStringNotContainsString('test', $configContent);
    }

    public function test_handles_existing_storage_link(): void
    {
        // Mock storage link already existing
        $this->artisan('fontawesome:install')
            ->expectsOutputToContain('Configuration du stockage')
            ->assertExitCode(0);
    }

    public function test_verification_step_runs(): void
    {
        $this->artisan('fontawesome:install')
            ->expectsOutput('🔍 Vérification de l\'installation...')
            ->expectsOutputToContain('✅ Configuration')
            ->expectsOutputToContain('Répertoire rapports')
            ->assertExitCode(0);
    }

    public function test_shows_installation_steps(): void
    {
        $this->artisan('fontawesome:install')
            ->expectsOutput('🔧 Publication de la configuration')
            ->expectsOutput('🔧 Configuration du package')
            ->expectsOutput('🔧 Configuration des rapports web')
            ->expectsOutput('🔧 Vérification de l\'installation')
            ->assertExitCode(0);
    }

    public function test_can_handle_interactive_responses(): void
    {
        // Test with manual responses simulation
        $this->artisan('fontawesome:install')
            ->expectsQuestion('Quel type de licence FontAwesome utilisez-vous ?', 'free')
            ->expectsQuestion('Voulez-vous ajouter des chemins personnalisés ?', false)
            ->expectsQuestion('Générer automatiquement des rapports ?', true)
            ->expectsQuestion('Créer des sauvegardes avant modification ?', true)
            ->expectsQuestion('Créer le lien symbolique storage pour l\'accès web ?', true)
            ->assertExitCode(0);
    }

    public function test_handles_pro_license_configuration(): void
    {
        $this->artisan('fontawesome:install')
            ->expectsQuestion('Quel type de licence FontAwesome utilisez-vous ?', 'pro')
            ->expectsQuestion('Voulez-vous ajouter des chemins personnalisés ?', false)
            ->expectsQuestion('Générer automatiquement des rapports ?', true)
            ->expectsQuestion('Créer des sauvegardes avant modification ?', true)
            ->expectsQuestion('Créer le lien symbolique storage pour l\'accès web ?', true)
            ->assertExitCode(0);

        // Verify Pro configuration dans le fichier généré
        $configContent = File::get(config_path('fontawesome-migrator.php'));
        $this->assertStringContainsString("'license_type' => 'pro'", $configContent);
        $this->assertStringContainsString("'thin' => true", $configContent);
        $this->assertStringContainsString("'sharp' => true", $configContent);
    }

    public function test_only_writes_modified_values(): void
    {
        $this->artisan('fontawesome:install')
            ->expectsQuestion('Quel type de licence FontAwesome utilisez-vous ?', 'free')
            ->expectsQuestion('Voulez-vous ajouter des chemins personnalisés ?', false)
            ->expectsQuestion('Générer automatiquement des rapports ?', true)
            ->expectsQuestion('Créer des sauvegardes avant modification ?', true)
            ->expectsQuestion('Créer le lien symbolique storage pour l\'accès web ?', true)
            ->assertExitCode(0);

        // Si toutes les valeurs sont par défaut, le fichier ne devrait contenir que le template
        $configContent = File::get(config_path('fontawesome-migrator.php'));
        $this->assertStringContainsString('Aucune configuration personnalisée', $configContent);
        $this->assertStringNotContainsString("'license_type'", $configContent);
    }

    public function test_writes_custom_values_only(): void
    {
        $this->artisan('fontawesome:install')
            ->expectsQuestion('Quel type de licence FontAwesome utilisez-vous ?', 'pro')
            ->expectsQuestion('Voulez-vous ajouter des chemins personnalisés ?', true)
            ->expectsQuestion('Chemin supplémentaire (ex: app/Views)', 'custom/path')
            ->expectsQuestion('Ajouter un autre chemin ?', false)
            ->expectsQuestion('Générer automatiquement des rapports ?', false)
            ->expectsQuestion('Créer des sauvegardes avant modification ?', true)
            ->expectsQuestion('Créer le lien symbolique storage pour l\'accès web ?', true)
            ->assertExitCode(0);

        $configContent = File::get(config_path('fontawesome-migrator.php'));
        // Devrait contenir les valeurs modifiées
        $this->assertStringContainsString("'license_type' => 'pro'", $configContent);
        $this->assertStringContainsString("'generate_report' => false", $configContent);
        $this->assertStringContainsString("'custom/path'", $configContent);

        // Ne devrait PAS contenir backup_files car c'est la valeur par défaut
        $this->assertStringNotContainsString("'backup_files'", $configContent);
    }
}
