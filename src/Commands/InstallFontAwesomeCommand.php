<?php

namespace FontAwesome\Migrator\Commands;

use Exception;
use FontAwesome\Migrator\Commands\Traits\ConfigurationHelpers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\warning;

class InstallFontAwesomeCommand extends Command
{
    use ConfigurationHelpers;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fontawesome:install
                            {--force : Forcer la réécriture des fichiers existants}
                            {--non-interactive : Mode non-interactif pour les tests}';

    /**
     * The console command description.
     */
    protected $description = 'Installation interactive du package FontAwesome Migrator';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->displayWelcome();

        // Étape 1: Publier la configuration
        $this->step('Publication de la configuration', function (): void {
            $this->publishConfiguration();
        });

        // Étape 2: Configurer le package
        $this->step('Configuration du package', function (): void {
            $this->configurePackage();
        });

        // Étape 3: Créer le lien symbolique storage
        $this->step('Configuration des rapports web', function (): void {
            $this->setupStorage();
        });

        // Étape 4: Vérifier l'installation
        $this->step('Vérification de l\'installation', function (): void {
            $this->verifyInstallation();
        });

        $this->displayCompletion();

        return Command::SUCCESS;
    }

    /**
     * Afficher l'écran de bienvenue
     */
    protected function displayWelcome(): void
    {
        intro('🚀 FontAwesome Migrator - Installation Interactive');

        note(
            "Migration automatique Font Awesome 5 → 6\n".
            'Support Free & Pro • Assets & Icônes • Interface Web'
        );
    }

    /**
     * Publier la configuration
     */
    protected function publishConfiguration(): void
    {
        $configExists = File::exists(config_path('fontawesome-migrator.php'));

        if ($configExists && ! $this->option('force') && ! $this->option('non-interactive')) {
            $replace = confirm('Le fichier de configuration existe déjà. Le remplacer ?', false);

            if (! $replace) {
                info('Configuration existante conservée');

                return;
            }
        }

        // Copier le fichier stub au lieu du fichier complet
        $stubPath = __DIR__.'/../../config/fontawesome-migrator.stub';
        $configPath = config_path('fontawesome-migrator.php');

        if (File::exists($stubPath)) {
            File::copy($stubPath, $configPath);
            info('✅ Configuration initialisée dans config/fontawesome-migrator.php');
        } else {
            // Fallback vers la méthode classique si le stub n'existe pas
            Artisan::call('vendor:publish', [
                '--tag' => 'fontawesome-migrator-config',
                '--force' => $this->option('force') || $configExists,
            ]);
            info('✅ Configuration publiée dans config/fontawesome-migrator.php');
        }
    }

    /**
     * Configurer le package de manière interactive
     */
    protected function configurePackage(): void
    {
        info('📝 Configuration du package...');

        // Mode non-interactif pour les tests
        if ($this->option('non-interactive')) {
            $licenseType = 'free';
            $customPaths = [];
            $generateReports = true;
            $enableBackups = true;

            info('✅ Configuration par défaut appliquée (mode non-interactif)');

            // En mode non-interactif, utiliser seulement les chemins personnalisés (vides = valeurs par défaut du package)
            $this->writeConfiguration($licenseType, $customPaths, $generateReports, $enableBackups, []);

            return;
        }

        // Type de licence
        $licenseType = select(
            'Quel type de licence FontAwesome utilisez-vous ?',
            [
                'free' => 'Free (gratuite)',
                'pro' => 'Pro (payante)',
            ],
            default: 'free'
        );

        // Chemins de scan personnalisés (utilise le trait)
        $customPaths = $this->configureScanPaths();

        // Fichiers à exclure (utilise le trait)
        $excludePatterns = $this->configureExcludePatterns();

        // Génération de rapports
        $generateReports = confirm('Générer automatiquement des rapports ?', true);

        // Sauvegardes
        $enableBackups = confirm('Créer des sauvegardes avant modification ?', true);

        // Écrire la configuration
        $this->writeConfiguration(
            $licenseType,
            $customPaths,
            $generateReports,
            $enableBackups,
            $excludePatterns
        );

        info('✅ Configuration personnalisée sauvegardée');
    }

    /**
     * Configurer le stockage pour les rapports web
     */
    protected function setupStorage(): void
    {
        info('🔗 Configuration du stockage pour l\'interface web...');

        // Vérifier si le lien symbolique existe
        $storageLink = public_path('storage');

        if (! File::exists($storageLink)) {
            if ($this->option('non-interactive') || confirm('Créer le lien symbolique storage pour l\'accès web ?', true)) {
                spin(
                    fn () => Artisan::call('storage:link'),
                    'Création du lien symbolique...'
                );
                info('✅ Lien symbolique storage créé');
            } else {
                warning('⚠️  Sans le lien storage, les rapports ne seront pas accessibles via le web');
            }
        } else {
            info('✅ Lien symbolique storage déjà configuré');
        }

        // Créer le répertoire des rapports
        $reportPath = storage_path('app/public/fontawesome-migrator/reports');

        if (! File::exists($reportPath)) {
            spin(
                fn () => File::makeDirectory($reportPath, 0755, true),
                'Création du répertoire des rapports...'
            );
            info('✅ Répertoire des rapports créé');
        } else {
            info('✅ Répertoire des rapports existe déjà');
        }
    }

    /**
     * Vérifier l'installation
     */
    protected function verifyInstallation(): void
    {
        info('🔍 Vérification de l\'installation...');

        $checks = [
            'Configuration' => File::exists(config_path('fontawesome-migrator.php')),
            'Lien storage' => File::exists(public_path('storage')),
            'Répertoire rapports' => File::exists(storage_path('app/public/fontawesome-migrator/reports')),
        ];

        $results = [];

        foreach ($checks as $check => $passed) {
            $results[] = ($passed ? '✅' : '❌').' '.$check;
        }

        note(implode("\n", $results));

        if (\in_array(false, $checks, true)) {
            warning('Certaines vérifications ont échoué');
        } else {
            info('✅ Installation vérifiée avec succès');
        }
    }

    // Configuration methods moved to ConfigurationHelpers trait

    /**
     * Afficher l'écran de fin
     */
    protected function displayCompletion(): void
    {
        outro('🎉 Installation terminée avec succès !');

        note(
            "📋 Prochaines étapes :\n\n".
            "1️⃣  Tester la migration :\n".
            "    php artisan fontawesome:migrate --dry-run\n\n".
            "2️⃣  Effectuer la migration :\n".
            "    php artisan fontawesome:migrate\n\n".
            "3️⃣  Accéder aux rapports :\n".
            '    '.url('/fontawesome-migrator/reports')
        );

        note(
            "📖 Documentation complète :\n".
            "  • README.md du package\n".
            "  • config/fontawesome-migrator.php\n\n".
            "🆘 Support :\n".
            "  • php artisan fontawesome:migrate --help\n".
            '  • GitHub Issues pour les problèmes'
        );
    }

    /**
     * Exécuter une étape avec gestion d'erreur
     */
    protected function step(string $title, callable $callback): void
    {
        try {
            spin($callback, '🔧 '.$title);
        } catch (Exception $exception) {
            error('❌ Erreur: '.$exception->getMessage());
            warning('Vous pouvez réessayer avec --force si nécessaire');
        }
    }

    // Scan paths and exclusion patterns methods moved to ConfigurationHelpers trait
}
