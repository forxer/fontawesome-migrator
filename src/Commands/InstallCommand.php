<?php

namespace FontAwesome\Migrator\Commands;

use Exception;
use FontAwesome\Migrator\Commands\Traits\ConfigurationHelpers;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;

use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\warning;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    use ConfigurationHelpers;

    protected $signature = 'fontawesome:install
                            {--force : Forcer la réécriture des fichiers existants}';

    protected $description = 'Installation du package FontAwesome Migrator';

    public function handle(): int
    {
        $this->displayWelcome();

        // Configuration du package (sans spin pour les interactions)
        info('📋 Configuration du package...');
        $this->setupConfiguration();

        $this->displayCompletion();

        return Command::SUCCESS;
    }

    /**
     * Affichage de bienvenue simple
     */
    protected function displayWelcome(): void
    {
        intro('🚀 FontAwesome Migrator - Installation');

        note(
            "Migration automatique FontAwesome\n".
            'Interface web • Configuration • Sauvegardes'
        );
    }

    /**
     * Configuration de base
     */
    protected function setupConfiguration(): void
    {
        $configPath = config_path('fontawesome-migrator.php');
        $configExists = File::exists($configPath);

        // 1. Gérer la publication du fichier
        if (! $configExists) {
            // Première installation : publier directement
            info('📄 Publication du fichier de configuration...');
            $this->publishConfiguration();
        } elseif ($this->option('force')) {
            // Force : écraser sans demander
            info('🔄 Remplacement forcé de la configuration...');
            $this->publishConfiguration();
        } else {
            // Config existe : demander confirmation
            $replace = confirm('Configuration existante trouvée. Remplacer ?', false);

            if ($replace) {
                info('🔄 Remplacement de la configuration...');
                $this->publishConfiguration();
            } else {
                info('✅ Configuration existante conservée');
                // On continue quand même pour configurer les paramètres
            }
        }

        // 2. Configurer les paramètres (toujours exécuté)
        $this->configureSettings();
    }

    /**
     * Publier le fichier de configuration
     */
    protected function publishConfiguration(): void
    {
        try {
            $configPath = config_path('fontawesome-migrator.php');
            $stubPath = __DIR__.'/../../config/fontawesome-migrator.stub';

            if (! File::exists($stubPath)) {
                throw new Exception('Fichier stub introuvable : '.$stubPath);
            }

            File::copy($stubPath, $configPath);
            info('✅ Configuration publiée');
        } catch (Exception $e) {
            error('❌ Erreur lors de la publication : '.$e->getMessage());
            warning('Vérifiez que le package est correctement installé');
        }
    }

    /**
     * Configuration des paramètres de base
     */
    protected function configureSettings(): void
    {
        note('⚙️ Configuration du package');

        // 1. Type de licence
        $licenseType = select(
            'Type de licence FontAwesome ?',
            [
                'free' => 'Free (gratuite)',
                'pro' => 'Pro (payante)',
            ],
            default: 'free'
        );

        // 2. Sauvegardes automatiques
        $enableBackups = confirm('Activer les sauvegardes automatiques ?', true);

        // 4. Chemins de scan personnalisés
        $scanPaths = $this->configureScanPaths();

        // 5. Patterns d'exclusion personnalisés
        $excludePatterns = $this->configureExcludePatterns();

        // 6. Extensions de fichiers
        $fileExtensions = $this->configureFileExtensions();

        // Écrire la configuration
        $this->writeConfiguration($licenseType, $enableBackups, $scanPaths, $excludePatterns, $fileExtensions);

        info('✅ Configuration appliquée avec succès');
    }

    /**
     * Affichage de fin
     */
    protected function displayCompletion(): void
    {
        outro('✅ Installation terminée !');

        note(
            "Prochaines étapes :\n\n".
            "• Test : php artisan fontawesome:migrate --dry-run\n".
            "• Migration : php artisan fontawesome:migrate\n".
            '• Interface : '.url('/fontawesome-migrator')
        );
    }
}
