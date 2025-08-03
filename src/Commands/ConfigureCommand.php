<?php

namespace FontAwesome\Migrator\Commands;

use Exception;
use FontAwesome\Migrator\Commands\Traits\ConfigurationHelpers;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class ConfigureCommand extends Command
{
    use ConfigurationHelpers;

    /**
     * Configuration temporaire en cours de modification
     */
    private array $tempConfig = [];

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fontawesome:config
                            {--show : Afficher la configuration actuelle}
                            {--reset : Réinitialiser la configuration aux valeurs par défaut}
                            {--no-interactive : Désactiver le mode interactif}';

    /**
     * The console command description.
     */
    protected $description = 'Gérer la configuration du package FontAwesome Migrator';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Mode non-interactif
        if ($this->option('no-interactive')) {
            return $this->handleNonInteractive();
        }

        // Mode interactif par défaut
        return $this->handleInteractive();
    }

    /**
     * Mode interactif avec Laravel Prompts
     */
    protected function handleInteractive(): int
    {
        intro('⚙️ FontAwesome Migrator - Configuration');

        // Vérifier si le fichier de configuration existe
        $configPath = config_path('fontawesome-migrator.php');

        if (! File::exists($configPath)) {
            warning('Fichier de configuration non trouvé. Exécutez d\'abord: php artisan fontawesome:install');

            return Command::FAILURE;
        }

        // Initialiser la configuration temporaire
        $this->tempConfig = config('fontawesome-migrator');

        // Boucle du menu principal
        do {
            $action = select(
                'Que souhaitez-vous faire ?',
                [
                    'show' => '👁️ Afficher la configuration actuelle',
                    'edit' => '✏️ Modifier la configuration',
                    'reset' => '🔄 Réinitialiser aux valeurs par défaut',
                    'validate' => '🔍 Valider la configuration',
                    'backup' => '💾 Sauvegarder la configuration',
                    'exit' => '🚪 Quitter',
                ]
            );

            $result = match ($action) {
                'show' => $this->showConfiguration(),
                'edit' => $this->editConfiguration(),
                'reset' => $this->resetConfiguration(),
                'validate' => $this->validateConfiguration(),
                'backup' => $this->backupConfiguration(),
                'exit' => Command::SUCCESS,
                default => Command::SUCCESS
            };

            // Si une action a échoué, on peut continuer quand même
            if ($result === Command::FAILURE && $action !== 'reset') {
                warning('Action échouée, mais vous pouvez continuer.');
            }

        } while ($action !== 'exit');

        outro('Configuration terminée');

        return Command::SUCCESS;
    }

    /**
     * Mode non-interactif
     */
    protected function handleNonInteractive(): int
    {
        if ($this->option('show')) {
            return $this->showConfiguration();
        }

        if ($this->option('reset')) {
            return $this->resetConfiguration();
        }

        $this->info('Utilisez --show pour afficher la configuration ou --reset pour la réinitialiser');

        return Command::SUCCESS;
    }

    /**
     * Afficher la configuration actuelle
     */
    protected function showConfiguration(): int
    {
        $configPath = config_path('fontawesome-migrator.php');

        if ($this->option('no-interactive')) {
            $this->info('Configuration FontAwesome Migrator:');
            $this->newLine();
        } else {
            note('📋 Configuration actuelle');
        }

        // Informations générales
        $this->displayConfigSection('📝 Informations générales', [
            'Fichier de configuration' => $configPath,
            'Type de licence' => $this->tempConfig['license_type'] ?? 'free',
            'Sauvegardes automatiques' => ($this->tempConfig['backup_files'] ?? true) ? '✅ Activées' : '❌ Désactivées',
        ]);

        // Chemins de scan
        $this->displayConfigSection('📂 Chemins de scan', [
            'Chemins configurés' => collect($this->tempConfig['scan_paths'] ?? [])->join(', ') ?: 'Aucun',
            'Nombre de chemins' => \count($this->tempConfig['scan_paths'] ?? []),
        ]);

        // Extensions de fichiers
        $this->displayConfigSection('📄 Extensions de fichiers', [
            'Extensions supportées' => collect($this->tempConfig['file_extensions'] ?? [])->join(', ') ?: 'Aucune',
            "Nombre d'extensions" => \count($this->tempConfig['file_extensions'] ?? []),
        ]);

        // Patterns d'exclusion
        $this->displayConfigSection('🚫 Patterns d\'exclusion', [
            'Patterns configurés' => collect($this->tempConfig['exclude_patterns'] ?? [])->join(', ') ?: 'Aucun',
            'Nombre de patterns' => \count($this->tempConfig['exclude_patterns'] ?? []),
        ]);

        // Styles Pro (si applicable)
        if (($this->tempConfig['license_type'] ?? 'free') === 'pro') {
            $proStyles = $this->tempConfig['pro_styles'] ?? [];
            $enabledStyles = collect($proStyles)->filter()->keys()->join(', ');

            $this->displayConfigSection('⭐ Styles Pro', [
                'Styles activés' => $enabledStyles ?: 'Aucun',
                'Light' => ($proStyles['light'] ?? false) ? '✅' : '❌',
                'Duotone' => ($proStyles['duotone'] ?? false) ? '✅' : '❌',
                'Thin' => ($proStyles['thin'] ?? false) ? '✅' : '❌',
                'Sharp' => ($proStyles['sharp'] ?? false) ? '✅' : '❌',
            ]);
        }

        if (! $this->option('no-interactive')) {
            outro('Configuration affichée avec succès');
        }

        return Command::SUCCESS;
    }

    /**
     * Modifier la configuration
     */
    protected function editConfiguration(): int
    {
        $section = select(
            'Quelle section souhaitez-vous modifier ?',
            [
                'license' => '📝 Type de licence',
                'paths' => '📂 Chemins de scan',
                'extensions' => '📄 Extensions de fichiers',
                'exclusions' => '🚫 Patterns d\'exclusion',
                'options' => '⚙️ Options générales',
                'pro_styles' => '⭐ Styles Pro (licence Pro uniquement)',
            ]
        );

        return match ($section) {
            'license' => $this->editLicenseType(),
            'paths' => $this->editScanPaths(),
            'extensions' => $this->editFileExtensions(),
            'exclusions' => $this->editExcludePatterns(),
            'options' => $this->editGeneralOptions(),
            'pro_styles' => $this->editProStyles(),
            default => Command::SUCCESS
        };
    }

    /**
     * Modifier le type de licence
     */
    protected function editLicenseType(): int
    {
        $currentLicense = $this->tempConfig['license_type'] ?? 'free';

        note('Type de licence actuel: '.($currentLicense === 'pro' ? '⭐ Pro' : '🆓 Free'));

        $newLicense = select(
            'Nouveau type de licence',
            [
                'free' => '🆓 Free (gratuite)',
                'pro' => '⭐ Pro (payante)',
            ],
            default: $currentLicense
        );

        if ($newLicense !== $currentLicense) {
            $this->updateTempConfigValue('license_type', $newLicense);

            if ($newLicense === 'pro') {
                info('✅ Licence Pro configurée. Vous pouvez maintenant configurer les styles Pro.');

                if (confirm('Configurer les styles Pro maintenant ?', true)) {
                    return $this->editProStyles();
                }
            } else {
                info('✅ Licence Free configurée.');
            }
        } else {
            info('Aucun changement effectué.');
        }

        return Command::SUCCESS;
    }

    /**
     * Modifier les chemins de scan
     */
    protected function editScanPaths(): int
    {
        $currentPaths = $this->tempConfig['scan_paths'] ?? [];

        note("Chemins actuels:\n".collect($currentPaths)->map(fn ($path): string => '  • '.$path)->join("\n"));

        $action = select(
            'Action à effectuer',
            [
                'add' => '➕ Ajouter des chemins',
                'remove' => '➖ Supprimer des chemins',
                'replace' => '🔄 Remplacer tous les chemins',
                'reset' => '🔄 Réinitialiser aux valeurs par défaut',
            ]
        );

        return match ($action) {
            'add' => $this->addScanPaths($currentPaths),
            'remove' => $this->removeScanPaths($currentPaths),
            'replace' => $this->replaceScanPaths(),
            'reset' => $this->resetScanPaths(),
            default => Command::SUCCESS,
        };
    }

    /**
     * Ajouter des chemins de scan
     */
    protected function addScanPaths(array $currentPaths): int
    {
        $newPaths = [];

        note(
            "💡 Exemples de chemins :\n".
            "  • app/Views (dossier Views custom)\n".
            "  • resources/components (composants)\n".
            "  • public/assets/css (assets publics)\n".
            '  • package.json (fichier spécifique)'
        );

        do {
            $path = text(
                'Nouveau chemin à ajouter',
                placeholder: 'ex: app/Views, resources/components'
            );

            if ($path && ! \in_array($path, $currentPaths) && ! \in_array($path, $newPaths)) {
                $newPaths[] = $path;
                info('✅ Ajouté: '.$path);
            } elseif (\in_array($path, $currentPaths)) {
                warning('⚠️ Chemin déjà présent: '.$path);
            }

            $continue = $path && confirm('Ajouter un autre chemin ?', false);
        } while ($continue);

        if ($newPaths !== []) {
            $updatedPaths = array_merge($currentPaths, $newPaths);
            $this->updateTempConfigValue('scan_paths', $updatedPaths);
            info('✅ '.\count($newPaths).' chemin(s) ajouté(s) avec succès.');

            // Afficher la configuration mise à jour
            note(
                "📂 Chemins de scan actuels :\n".
                collect($updatedPaths)->map(fn ($path): string => '  • '.$path)->join("\n")
            );
        } else {
            info('Aucun nouveau chemin ajouté.');
        }

        return Command::SUCCESS;
    }

    /**
     * Supprimer des chemins de scan
     */
    protected function removeScanPaths(array $currentPaths): int
    {
        if ($currentPaths === []) {
            warning('Aucun chemin configuré à supprimer.');

            return Command::SUCCESS;
        }

        $this->showMultiselectInstructions();

        $pathsToRemove = multiselect(
            'Chemins à supprimer',
            collect($currentPaths)->mapWithKeys(fn ($path) => [$path => $path])->toArray()
        );

        if ($pathsToRemove !== []) {
            $updatedPaths = array_diff($currentPaths, $pathsToRemove);
            $this->updateTempConfigValue('scan_paths', array_values($updatedPaths));
            info('✅ '.\count($pathsToRemove).' chemin(s) supprimé(s) avec succès.');

            // Afficher la configuration mise à jour
            note(
                "📂 Chemins de scan actuels :\n".
                ($updatedPaths === [] ? '  • Aucun chemin personnalisé (valeurs par défaut utilisées)' :
                collect($updatedPaths)->map(fn ($path): string => '  • '.$path)->join("\n"))
            );
        } else {
            info('Aucun chemin sélectionné pour suppression.');
        }

        return Command::SUCCESS;
    }

    /**
     * Remplacer tous les chemins de scan
     */
    protected function replaceScanPaths(): int
    {
        warning('Cette action remplacera TOUS les chemins existants.');

        if (! confirm('Continuer ?', false)) {
            info('Opération annulée.');

            return Command::SUCCESS;
        }

        $newPaths = [];

        do {
            $path = text(
                'Nouveau chemin',
                placeholder: 'ex: resources/views'
            );

            if ($path && ! \in_array($path, $newPaths)) {
                $newPaths[] = $path;
                info('✅ Ajouté: '.$path);
            }

            $continue = $path && confirm('Ajouter un autre chemin ?', true);
        } while ($continue);

        if ($newPaths !== []) {
            $this->updateTempConfigValue('scan_paths', $newPaths);
            info('✅ Chemins remplacés avec succès.');
        } else {
            warning('Aucun chemin configuré. Configuration inchangée.');
        }

        return Command::SUCCESS;
    }

    /**
     * Réinitialiser les chemins de scan
     */
    protected function resetScanPaths(): int
    {
        $defaultPaths = $this->getDefaultScanPaths();

        note("Chemins par défaut:\n".collect($defaultPaths)->map(fn ($path): string => '  • '.$path)->join("\n"));

        if (confirm('Réinitialiser aux chemins par défaut ?', true)) {
            $this->updateTempConfigValue('scan_paths', $defaultPaths);
            info('✅ Chemins réinitialisés aux valeurs par défaut.');
        }

        return Command::SUCCESS;
    }

    /**
     * Modifier les extensions de fichiers
     */
    protected function editFileExtensions(): int
    {
        $currentExtensions = $this->tempConfig['file_extensions'] ?? [];

        note("Extensions actuelles:\n".collect($currentExtensions)->map(fn ($ext): string => '  • '.$ext)->join("\n"));

        $action = select(
            'Action à effectuer',
            [
                'add' => '➕ Ajouter des extensions',
                'remove' => '➖ Supprimer des extensions',
                'reset' => '🔄 Réinitialiser aux valeurs par défaut',
            ]
        );

        return match ($action) {
            'add' => $this->addFileExtensions($currentExtensions),
            'remove' => $this->removeFileExtensions($currentExtensions),
            'reset' => $this->resetFileExtensions(),
            default => Command::SUCCESS,
        };
    }

    /**
     * Ajouter des extensions de fichiers
     */
    protected function addFileExtensions(array $currentExtensions): int
    {
        note(
            "💡 Exemples d'extensions :\n".
            "  • tsx (fichiers TypeScript React)\n".
            "  • svelte (fichiers Svelte)\n".
            "  • twig (templates Twig)\n".
            '  • php (fichiers PHP purs)'
        );

        $newExtensions = [];

        do {
            $extension = text(
                'Nouvelle extension (sans le point)',
                placeholder: 'ex: tsx, svelte, twig'
            );

            if ($extension !== '' && $extension !== '0') {
                // Nettoyer l'extension (enlever le point s'il y en a un)
                $extension = ltrim($extension, '.');

                if (! \in_array($extension, $currentExtensions) && ! \in_array($extension, $newExtensions)) {
                    $newExtensions[] = $extension;
                    info('✅ Ajoutée: .'.$extension);
                } else {
                    warning('⚠️ Extension déjà présente: .'.$extension);
                }
            }

            $continue = $extension && confirm('Ajouter une autre extension ?', false);
        } while ($continue);

        if ($newExtensions !== []) {
            $updatedExtensions = array_merge($currentExtensions, $newExtensions);
            sort($updatedExtensions); // Tri alphabétique
            $this->updateTempConfigValue('file_extensions', $updatedExtensions);
            info('✅ '.\count($newExtensions).' extension(s) ajoutée(s) avec succès.');

            // Afficher la configuration mise à jour
            note(
                "📄 Extensions de fichiers actuelles :\n".
                collect($updatedExtensions)->map(fn ($ext): string => '  • .'.$ext)->join("\n")
            );
        } else {
            info('Aucune nouvelle extension ajoutée.');
        }

        return Command::SUCCESS;
    }

    /**
     * Supprimer des extensions de fichiers
     */
    protected function removeFileExtensions(array $currentExtensions): int
    {
        if ($currentExtensions === []) {
            warning('Aucune extension configurée à supprimer.');

            return Command::SUCCESS;
        }

        $this->showMultiselectInstructions();

        $extensionsToRemove = multiselect(
            'Extensions à supprimer',
            collect($currentExtensions)->mapWithKeys(fn ($ext) => [$ext => '.'.$ext])->toArray()
        );

        if ($extensionsToRemove !== []) {
            $updatedExtensions = array_diff($currentExtensions, $extensionsToRemove);
            $this->updateTempConfigValue('file_extensions', array_values($updatedExtensions));
            info('✅ '.\count($extensionsToRemove).' extension(s) supprimée(s) avec succès.');

            // Afficher la configuration mise à jour
            note(
                "📄 Extensions de fichiers actuelles :\n".
                ($updatedExtensions === [] ? '  • Aucune extension personnalisée (valeurs par défaut utilisées)' :
                collect($updatedExtensions)->map(fn ($ext): string => '  • .'.$ext)->join("\n"))
            );
        } else {
            info('Aucune extension sélectionnée pour suppression.');
        }

        return Command::SUCCESS;
    }

    /**
     * Réinitialiser les extensions de fichiers
     */
    protected function resetFileExtensions(): int
    {
        $defaultExtensions = $this->getDefaultFileExtensions();

        note("Extensions par défaut:\n".collect($defaultExtensions)->map(fn ($ext): string => '  • .'.$ext)->join("\n"));

        if (confirm('Réinitialiser aux extensions par défaut ?', true)) {
            $this->updateTempConfigValue('file_extensions', $defaultExtensions);
            info('✅ Extensions réinitialisées aux valeurs par défaut.');
        }

        return Command::SUCCESS;
    }

    /**
     * Modifier les patterns d'exclusion
     */
    protected function editExcludePatterns(): int
    {
        $currentPatterns = $this->tempConfig['exclude_patterns'] ?? [];

        note("Patterns actuels:\n".collect($currentPatterns)->map(fn ($pattern): string => '  • '.$pattern)->join("\n"));

        $action = select(
            'Action à effectuer',
            [
                'add' => '➕ Ajouter des patterns',
                'remove' => '➖ Supprimer des patterns',
                'reset' => '🔄 Réinitialiser aux valeurs par défaut',
            ]
        );

        return match ($action) {
            'add' => $this->addExcludePatterns($currentPatterns),
            'remove' => $this->removeExcludePatterns($currentPatterns),
            'reset' => $this->resetExcludePatterns(),
            default => Command::SUCCESS,
        };
    }

    /**
     * Ajouter des patterns d'exclusion
     */
    protected function addExcludePatterns(array $currentPatterns): int
    {
        note(
            "💡 Exemples de patterns :\n".
            "  • *.backup (fichiers de sauvegarde)\n".
            "  • tests/ (dossier de tests)\n".
            "  • legacy-* (fichiers legacy)\n".
            '  • temp (dossiers temporaires)'
        );

        $newPatterns = [];

        do {
            $pattern = text(
                "Nouveau pattern d'exclusion",
                placeholder: 'ex: *.backup, tests/, legacy-*'
            );

            if ($pattern && ! \in_array($pattern, $currentPatterns) && ! \in_array($pattern, $newPatterns)) {
                $newPatterns[] = $pattern;
                info('✅ Ajouté: '.$pattern);
            } elseif (\in_array($pattern, $currentPatterns)) {
                warning('⚠️ Pattern déjà présent: '.$pattern);
            }

            $continue = $pattern && confirm('Ajouter un autre pattern ?', false);
        } while ($continue);

        if ($newPatterns !== []) {
            $updatedPatterns = array_merge($currentPatterns, $newPatterns);
            $this->updateTempConfigValue('exclude_patterns', $updatedPatterns);
            info('✅ '.\count($newPatterns).' pattern(s) ajouté(s) avec succès.');

            // Afficher la configuration mise à jour
            note(
                "🚫 Patterns d'exclusion actuels :\n".
                collect($updatedPatterns)->map(fn ($pattern): string => '  • '.$pattern)->join("\n")
            );
        } else {
            info('Aucun nouveau pattern ajouté.');
        }

        return Command::SUCCESS;
    }

    /**
     * Supprimer des patterns d'exclusion
     */
    protected function removeExcludePatterns(array $currentPatterns): int
    {
        if ($currentPatterns === []) {
            warning('Aucun pattern configuré à supprimer.');

            return Command::SUCCESS;
        }

        $this->showMultiselectInstructions();

        $patternsToRemove = multiselect(
            'Patterns à supprimer',
            collect($currentPatterns)->mapWithKeys(fn ($pattern) => [$pattern => $pattern])->toArray()
        );

        if ($patternsToRemove !== []) {
            $updatedPatterns = array_diff($currentPatterns, $patternsToRemove);
            $this->updateTempConfigValue('exclude_patterns', array_values($updatedPatterns));
            info('✅ '.\count($patternsToRemove).' pattern(s) supprimé(s) avec succès.');
        } else {
            info('Aucun pattern supprimé.');
        }

        return Command::SUCCESS;
    }

    /**
     * Réinitialiser les patterns d'exclusion
     */
    protected function resetExcludePatterns(): int
    {
        $defaultPatterns = $this->getDefaultExcludePatterns();

        note("Patterns par défaut:\n".collect($defaultPatterns)->map(fn ($pattern): string => '  • '.$pattern)->join("\n"));

        if (confirm('Réinitialiser aux patterns par défaut ?', true)) {
            $this->updateTempConfigValue('exclude_patterns', $defaultPatterns);
            info('✅ Patterns réinitialisés aux valeurs par défaut.');
        }

        return Command::SUCCESS;
    }

    /**
     * Modifier les options générales
     */
    protected function editGeneralOptions(): int
    {
        $backupFiles = $this->tempConfig['backup_files'] ?? true;

        note("Options actuelles:\n  • Sauvegardes automatiques: ".($backupFiles ? '✅' : '❌'));

        $newBackupFiles = confirm('Créer des sauvegardes avant modification ?', $backupFiles);

        $changes = [];

        if ($newBackupFiles !== $backupFiles) {
            $this->updateTempConfigValue('backup_files', $newBackupFiles);
            $changes[] = 'backup_files';
        }

        if ($changes !== []) {
            info('✅ Options mises à jour: '.implode(', ', $changes));
        } else {
            info('Aucun changement effectué.');
        }

        return Command::SUCCESS;
    }

    /**
     * Modifier les styles Pro
     */
    protected function editProStyles(): int
    {
        $licenseType = $this->tempConfig['license_type'] ?? 'free';

        if ($licenseType !== 'pro') {
            warning("Les styles Pro ne sont disponibles qu'avec une licence Pro.");

            if (confirm('Configurer une licence Pro maintenant ?', false)) {
                return $this->editLicenseType();
            }

            return Command::SUCCESS;
        }

        $currentStyles = $this->tempConfig['pro_styles'] ?? [];

        note("Styles Pro actuels:\n".
             '  • Light: '.(($currentStyles['light'] ?? false) ? '✅' : '❌')."\n".
             '  • Duotone: '.(($currentStyles['duotone'] ?? false) ? '✅' : '❌')."\n".
             '  • Thin: '.(($currentStyles['thin'] ?? false) ? '✅' : '❌')."\n".
             '  • Sharp: '.(($currentStyles['sharp'] ?? false) ? '✅' : '❌'));

        $this->showMultiselectInstructions();

        $stylesToEnable = multiselect(
            'Styles Pro à activer',
            [
                'light' => 'Light (fa-light)',
                'duotone' => 'Duotone (fa-duotone)',
                'thin' => 'Thin (fa-thin)',
                'sharp' => 'Sharp (fa-sharp)',
            ],
            default: collect($currentStyles)->filter()->keys()->toArray()
        );

        $newStyles = [
            'light' => \in_array('light', $stylesToEnable),
            'duotone' => \in_array('duotone', $stylesToEnable),
            'thin' => \in_array('thin', $stylesToEnable),
            'sharp' => \in_array('sharp', $stylesToEnable),
        ];

        $this->updateTempConfigValue('pro_styles', $newStyles);

        $enabledCount = \count(array_filter($newStyles));
        info(\sprintf('✅ Styles Pro configurés: %d/4 activés.', $enabledCount));

        return Command::SUCCESS;
    }

    /**
     * Réinitialiser la configuration
     */
    protected function resetConfiguration(): int
    {
        if (! $this->option('no-interactive')) {
            warning('Cette action supprimera TOUTE la configuration personnalisée.');

            if (! confirm('Êtes-vous sûr de vouloir réinitialiser la configuration ?', false)) {
                info('Réinitialisation annulée.');

                return Command::SUCCESS;
            }
        }

        $configPath = config_path('fontawesome-migrator.php');

        try {
            // Supprimer le fichier existant
            if (File::exists($configPath)) {
                File::delete($configPath);
            }

            // Copier le fichier par défaut
            $defaultConfigPath = __DIR__.'/../../config/fontawesome-migrator.php';

            if (File::exists($defaultConfigPath)) {
                File::copy($defaultConfigPath, $configPath);
                info('✅ Configuration réinitialisée aux valeurs par défaut.');
            } else {
                error('❌ Fichier de configuration par défaut introuvable.');

                return Command::FAILURE;
            }

        } catch (Exception $exception) {
            error('❌ Erreur lors de la réinitialisation: '.$exception->getMessage());

            return Command::FAILURE;
        }

        if (! $this->option('no-interactive')) {
            outro('Configuration réinitialisée avec succès');
        }

        return Command::SUCCESS;
    }

    /**
     * Valider la configuration
     */
    protected function validateConfiguration(): int
    {
        config('fontawesome-migrator');
        $errors = [];
        $warnings = [];

        // Validation du type de licence
        $licenseType = $this->tempConfig['license_type'] ?? 'free';

        if (! \in_array($licenseType, ['free', 'pro'])) {
            $errors[] = 'Type de licence invalide: '.$licenseType;
        }

        // Validation des chemins de scan
        $scanPaths = $this->tempConfig['scan_paths'] ?? [];

        if (empty($scanPaths)) {
            $warnings[] = 'Aucun chemin de scan configuré';
        } else {
            foreach ($scanPaths as $path) {
                if (! File::exists(base_path($path))) {
                    $warnings[] = 'Chemin introuvable: '.$path;
                }
            }
        }

        // Validation des extensions
        $extensions = $this->tempConfig['file_extensions'] ?? [];

        if (empty($extensions)) {
            $warnings[] = 'Aucune extension de fichier configurée';
        }

        // Validation des styles Pro
        if ($licenseType === 'pro') {
            $proStyles = $this->tempConfig['pro_styles'] ?? [];
            $enabledStyles = array_filter($proStyles);

            if ($enabledStyles === []) {
                $warnings[] = 'Licence Pro configurée mais aucun style Pro activé';
            }
        }

        // Affichage des résultats
        if ($errors === [] && $warnings === []) {
            info('✅ Configuration valide - Aucun problème détecté.');
        } else {
            if ($errors !== []) {
                error('❌ Erreurs détectées:');

                foreach ($errors as $error) {
                    $this->line('   • '.$error);
                }
            }

            if ($warnings !== []) {
                warning('⚠️ Avertissements:');

                foreach ($warnings as $warning) {
                    $this->line('   • '.$warning);
                }
            }
        }

        return $errors === [] ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Sauvegarder la configuration
     */
    protected function backupConfiguration(): int
    {
        $configPath = config_path('fontawesome-migrator.php');

        if (! File::exists($configPath)) {
            error('❌ Fichier de configuration introuvable.');

            return Command::FAILURE;
        }

        $timestamp = date('Y-m-d_H-i-s');
        $backupPath = config_path(\sprintf('fontawesome-migrator.backup.%s.php', $timestamp));

        try {
            File::copy($configPath, $backupPath);
            info('✅ Configuration sauvegardée: '.$backupPath);
        } catch (Exception $exception) {
            error('❌ Erreur lors de la sauvegarde: '.$exception->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Mettre à jour une valeur dans la configuration temporaire
     */
    protected function updateTempConfigValue(string $key, mixed $value): void
    {
        $this->tempConfig[$key] = $value;

        // Sauvegarder automatiquement après chaque modification
        $this->saveConfigurationSilent();
    }

    /**
     * Afficher les instructions pour multiselect
     */
    protected function showMultiselectInstructions(): void
    {
        note('💡 Utilisez les flèches ↑/↓ pour naviguer, ESPACE pour sélectionner/désélectionner (✓), puis ENTRÉE pour valider');
    }

    /**
     * Sauvegarder la configuration temporaire sur disque
     */
    protected function saveConfiguration(): int
    {
        $this->saveConfigurationSilent();
        info('✅ Configuration sauvegardée avec succès !');

        return Command::SUCCESS;
    }

    /**
     * Sauvegarder la configuration temporaire sur disque (sans message)
     */
    protected function saveConfigurationSilent(): void
    {
        $configPath = config_path('fontawesome-migrator.php');

        // Charger la configuration par défaut pour comparer
        $defaultConfigPath = __DIR__.'/../../config/fontawesome-migrator.php';

        if (! file_exists($defaultConfigPath)) {
            // Fallback si le fichier par défaut n'existe pas
            $content = "<?php\n\nreturn [\n    /*\n    | Configuration FontAwesome Migrator\n    | Valeurs personnalisées\n    */\n\n";
            $content .= $this->arrayToString($this->tempConfig, 1)."\n];\n";
            File::put($configPath, $content);
            config(['fontawesome-migrator' => $this->tempConfig]);

            return;
        }

        $defaultConfig = include $defaultConfigPath;

        // Créer seulement les valeurs modifiées
        $customConfig = [];

        foreach ($this->tempConfig as $key => $value) {
            if (! isset($defaultConfig[$key]) || $defaultConfig[$key] !== $value) {
                $customConfig[$key] = $value;
            }
        }

        // Utiliser la méthode optimisée pour écrire seulement les différences
        $this->writeCustomConfigFile($configPath, $customConfig);

        // Recharger la configuration dans Laravel
        config(['fontawesome-migrator' => $this->tempConfig]);
    }

    // Configuration helper methods moved to ConfigurationHelpers trait
}
