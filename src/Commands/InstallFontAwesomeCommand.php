<?php

namespace FontAwesome\Migrator\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class InstallFontAwesomeCommand extends Command
{
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
        $this->newLine();
        $this->line('┌─────────────────────────────────────────────────────────────┐');
        $this->line('│                                                             │');
        $this->line('│  🚀 <fg=cyan;options=bold>FontAwesome Migrator - Installation Interactive</fg=cyan;options=bold>  │');
        $this->line('│                                                             │');
        $this->line('│  Migration automatique Font Awesome 5 → 6                  │');
        $this->line('│  Support Free & Pro • Assets & Icônes • Interface Web      │');
        $this->line('│                                                             │');
        $this->line('└─────────────────────────────────────────────────────────────┘');
        $this->newLine();
    }

    /**
     * Publier la configuration
     */
    protected function publishConfiguration(): void
    {
        $configExists = File::exists(config_path('fontawesome-migrator.php'));

        if ($configExists && ! $this->option('force') && ! $this->option('non-interactive') && ! $this->confirm('Le fichier de configuration existe déjà. Le remplacer ?', false)) {
            $this->info('   Configuration existante conservée');

            return;
        }

        // Copier le fichier stub au lieu du fichier complet
        $stubPath = __DIR__.'/../../config/fontawesome-migrator.stub';
        $configPath = config_path('fontawesome-migrator.php');

        if (File::exists($stubPath)) {
            File::copy($stubPath, $configPath);
            $this->info('   ✅ Configuration initialisée dans config/fontawesome-migrator.php');
        } else {
            // Fallback vers la méthode classique si le stub n'existe pas
            Artisan::call('vendor:publish', [
                '--tag' => 'fontawesome-migrator-config',
                '--force' => $this->option('force') || $configExists,
            ]);
            $this->info('   ✅ Configuration publiée dans config/fontawesome-migrator.php');
        }
    }

    /**
     * Configurer le package de manière interactive
     */
    protected function configurePackage(): void
    {
        $this->line('   📝 Configuration du package...');

        // Mode non-interactif pour les tests
        if ($this->option('non-interactive')) {
            $licenseType = 'free';
            $customPaths = [];
            $generateReports = true;
            $enableBackups = true;

            $this->info('   ✅ Configuration par défaut appliquée (mode non-interactif)');
            $this->writeConfiguration($licenseType, array_merge($this->getDefaultPaths(), $customPaths), $generateReports, $enableBackups);

            return;
        }

        // Type de licence
        $licenseType = $this->choice(
            '   Quel type de licence FontAwesome utilisez-vous ?',
            ['free' => 'Free (gratuite)', 'pro' => 'Pro (payante)'],
            'free'
        );

        // Chemins de scan personnalisés
        $this->info('   📂 Chemins de scan par défaut :');
        $defaultPaths = $this->getDefaultPaths();

        foreach ($defaultPaths as $path) {
            $this->line('      • '.$path);
        }

        $customPaths = [];

        if ($this->confirm('   Voulez-vous ajouter des chemins personnalisés ?', false)) {
            do {
                $path = $this->ask('   Chemin supplémentaire (ex: app/Views)');

                if ($path) {
                    $customPaths[] = $path;
                    $this->info('      ✅ Ajouté: '.$path);
                }
            } while ($path && $this->confirm('   Ajouter un autre chemin ?', false));
        }

        // Génération de rapports
        $generateReports = $this->confirm('   Générer automatiquement des rapports ?', true);

        // Sauvegardes
        $enableBackups = $this->confirm('   Créer des sauvegardes avant modification ?', true);

        // Écrire la configuration
        $this->writeConfiguration($licenseType, array_merge($defaultPaths, $customPaths), $generateReports, $enableBackups);

        $this->info('   ✅ Configuration personnalisée sauvegardée');
    }

    /**
     * Configurer le stockage pour les rapports web
     */
    protected function setupStorage(): void
    {
        $this->line('   🔗 Configuration du stockage pour l\'interface web...');

        // Vérifier si le lien symbolique existe
        $storageLink = public_path('storage');

        if (! File::exists($storageLink)) {
            if ($this->option('non-interactive') || $this->confirm('   Créer le lien symbolique storage pour l\'accès web ?', true)) {
                Artisan::call('storage:link');
                $this->info('   ✅ Lien symbolique storage créé');
            } else {
                $this->warn('   ⚠️  Sans le lien storage, les rapports ne seront pas accessibles via le web');
            }
        } else {
            $this->info('   ✅ Lien symbolique storage déjà configuré');
        }

        // Créer le répertoire des rapports
        $reportPath = storage_path('app/public/fontawesome-migrator/reports');

        if (! File::exists($reportPath)) {
            File::makeDirectory($reportPath, 0755, true);
            $this->info('   ✅ Répertoire des rapports créé');
        } else {
            $this->info('   ✅ Répertoire des rapports existe déjà');
        }
    }

    /**
     * Vérifier l'installation
     */
    protected function verifyInstallation(): void
    {
        $this->line('   🔍 Vérification de l\'installation...');

        $checks = [
            'Configuration' => File::exists(config_path('fontawesome-migrator.php')),
            'Lien storage' => File::exists(public_path('storage')),
            'Répertoire rapports' => File::exists(storage_path('app/public/fontawesome-migrator/reports')),
        ];

        foreach ($checks as $check => $passed) {
            $status = $passed ? '✅' : '❌';
            $this->line(\sprintf('      %s %s', $status, $check));
        }

        if (\in_array(false, $checks, true)) {
            $this->warn('   ⚠️  Certaines vérifications ont échoué');
        } else {
            $this->info('   ✅ Installation vérifiée avec succès');
        }
    }

    /**
     * Écrire la configuration personnalisée
     */
    protected function writeConfiguration(string $licenseType, array $scanPaths, bool $generateReports, bool $enableBackups): void
    {
        $configPath = config_path('fontawesome-migrator.php');

        // Charger la configuration par défaut depuis le package
        $defaultConfigPath = __DIR__.'/../../config/fontawesome-migrator.php';

        if (! file_exists($defaultConfigPath)) {
            throw new Exception('Configuration par défaut introuvable : '.$defaultConfigPath);
        }

        $defaultConfig = include $defaultConfigPath;

        // Créer seulement les valeurs modifiées
        $customConfig = [];

        // Vérifier et ajouter seulement les valeurs différentes des défauts
        if ($licenseType !== $defaultConfig['license_type']) {
            $customConfig['license_type'] = $licenseType;
        }

        if ($scanPaths !== $defaultConfig['scan_paths']) {
            $customConfig['scan_paths'] = $scanPaths;
        }

        if ($generateReports !== $defaultConfig['generate_report']) {
            $customConfig['generate_report'] = $generateReports;
        }

        if ($enableBackups !== $defaultConfig['backup_files']) {
            $customConfig['backup_files'] = $enableBackups;
        }

        // Si Pro, activer tous les styles seulement si différent du défaut
        if ($licenseType === 'pro') {
            $proStyles = [
                'light' => true,
                'duotone' => true,
                'thin' => true,
                'sharp' => true,
            ];

            if ($proStyles !== $defaultConfig['pro_styles']) {
                $customConfig['pro_styles'] = $proStyles;
            }
        }

        // Générer le contenu du fichier avec seulement les valeurs personnalisées
        $this->writeCustomConfigFile($configPath, $customConfig);
    }

    /**
     * Écrire le fichier de configuration personnalisé
     */
    protected function writeCustomConfigFile(string $configPath, array $customConfig): void
    {
        // Template de base
        $template = "<?php\n\nreturn [\n    /*\n    |--------------------------------------------------------------------------\n    | Configuration FontAwesome Migrator\n    |--------------------------------------------------------------------------\n    |\n    | Ce fichier contient uniquement les paramètres personnalisés.\n    | Les valeurs par défaut sont définies dans le package.\n    | \n    | Pour voir toutes les options disponibles :\n    | php artisan vendor:publish --tag=fontawesome-migrator-config --force\n    |\n    */\n\n";

        // Si aucune configuration personnalisée, créer un fichier vide
        if ($customConfig === []) {
            $content = $template."    // Aucune configuration personnalisée\n    // Toutes les valeurs par défaut sont utilisées\n];\n";
        } else {
            $content = $template.$this->arrayToString($customConfig, 1)."\n];\n";
        }

        File::put($configPath, $content);
    }

    /**
     * Convertir un tableau en chaîne PHP formatée
     */
    protected function arrayToString(array $array, int $indent = 0): string
    {
        $spaces = str_repeat('    ', $indent);
        $result = '';

        foreach ($array as $key => $value) {
            $result .= $spaces;

            if (\is_string($key)) {
                $result .= \sprintf("'%s' => ", $key);
            }

            if (\is_array($value)) {
                $result .= "[\n".$this->arrayToString($value, $indent + 1)."\n".$spaces.']';
            } elseif (\is_bool($value)) {
                $result .= $value ? 'true' : 'false';
            } elseif (\is_string($value)) {
                if (str_contains($value, '(')) {
                    // Fonctions Laravel comme storage_path()
                    $result .= $value;
                } else {
                    $result .= \sprintf("'%s'", $value);
                }
            } else {
                $result .= $value;
            }

            $result .= ",\n";
        }

        return rtrim($result, ",\n");
    }

    /**
     * Afficher l'écran de fin
     */
    protected function displayCompletion(): void
    {
        $this->newLine();
        $this->line('┌─────────────────────────────────────────────────────────────┐');
        $this->line('│                                                             │');
        $this->line('│  🎉 <fg=green;options=bold>Installation terminée avec succès !</fg=green;options=bold>             │');
        $this->line('│                                                             │');
        $this->line('└─────────────────────────────────────────────────────────────┘');
        $this->newLine();

        $this->info('📋 <options=bold>Prochaines étapes :</options=bold>');
        $this->newLine();

        $this->line('   1️⃣  <fg=cyan>Tester la migration :</fg=cyan>');
        $this->line('       php artisan fontawesome:migrate --dry-run');
        $this->newLine();

        $this->line('   2️⃣  <fg=cyan>Effectuer la migration :</fg=cyan>');
        $this->line('       php artisan fontawesome:migrate');
        $this->newLine();

        $this->line('   3️⃣  <fg=cyan>Accéder aux rapports :</fg=cyan>');
        $this->line('       '.url('/fontawesome-migrator/reports'));
        $this->newLine();

        $this->line('📖 <fg=yellow>Documentation complète :</fg=yellow>');
        $this->line('   • README.md du package');
        $this->line('   • config/fontawesome-migrator.php');
        $this->newLine();

        $this->line('🆘 <fg=magenta>Support :</fg=magenta>');
        $this->line('   • php artisan fontawesome:migrate --help');
        $this->line('   • GitHub Issues pour les problèmes');
        $this->newLine();
    }

    /**
     * Exécuter une étape avec gestion d'erreur
     */
    protected function step(string $title, callable $callback): void
    {
        $this->info('🔧 '.$title);

        try {
            $callback();
        } catch (Exception $exception) {
            $this->error('   ❌ Erreur: '.$exception->getMessage());
            $this->warn('   Vous pouvez réessayer avec --force si nécessaire');
        }

        $this->newLine();
    }

    /**
     * Obtenir les chemins de scan par défaut
     */
    protected function getDefaultPaths(): array
    {
        return [
            'resources/views',
            'resources/js',
            'resources/css',
            'public/css',
            'public/js',
        ];
    }
}
