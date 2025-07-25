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
                            {--force : Forcer la réécriture des fichiers existants}';

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

        if ($configExists && ! $this->option('force') && ! $this->confirm('Le fichier de configuration existe déjà. Le remplacer ?', false)) {
            $this->info('   Configuration existante conservée');

            return;
        }

        Artisan::call('vendor:publish', [
            '--tag' => 'fontawesome-migrator-config',
            '--force' => $this->option('force') || $configExists,
        ]);

        $this->info('   ✅ Configuration publiée dans config/fontawesome-migrator.php');
    }

    /**
     * Configurer le package de manière interactive
     */
    protected function configurePackage(): void
    {
        $this->line('   📝 Configuration du package...');

        // Type de licence
        $licenseType = $this->choice(
            '   Quel type de licence FontAwesome utilisez-vous ?',
            ['free' => 'Free (gratuite)', 'pro' => 'Pro (payante)'],
            'free'
        );

        // Chemins de scan personnalisés
        $this->info('   📂 Chemins de scan par défaut :');
        $defaultPaths = [
            'resources/views',
            'resources/js',
            'resources/css',
            'public/css',
            'public/js',
        ];

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
            if ($this->confirm('   Créer le lien symbolique storage pour l\'accès web ?', true)) {
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
        $config = include $configPath;

        // Mettre à jour la configuration
        $config['license_type'] = $licenseType;
        $config['scan_paths'] = $scanPaths;
        $config['generate_report'] = $generateReports;
        $config['backup_files'] = $enableBackups;

        // Si Pro, activer tous les styles
        if ($licenseType === 'pro') {
            $config['pro_styles'] = [
                'light' => true,
                'duotone' => true,
                'thin' => true,
                'sharp' => true,
            ];
        }

        // Générer le nouveau fichier de configuration
        $configContent = "<?php\n\nreturn ".$this->arrayToString($config).";\n";
        File::put($configPath, $configContent);
    }

    /**
     * Convertir un tableau en chaîne PHP formatée
     */
    protected function arrayToString(array $array, int $indent = 0): string
    {
        $spaces = str_repeat('    ', $indent);
        $result = "[\n";

        foreach ($array as $key => $value) {
            $result .= $spaces.'    ';

            if (\is_string($key)) {
                $result .= \sprintf("'%s' => ", $key);
            }

            if (\is_array($value)) {
                $result .= $this->arrayToString($value, $indent + 1);
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

        return $result.($spaces.']');
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
}
