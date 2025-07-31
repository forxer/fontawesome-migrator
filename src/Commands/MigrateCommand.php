<?php

namespace FontAwesome\Migrator\Commands;

use FontAwesome\Migrator\Services\AssetMigrator;
use FontAwesome\Migrator\Services\FileScanner;
use FontAwesome\Migrator\Services\IconReplacer;
use FontAwesome\Migrator\Services\MetadataManager;
use FontAwesome\Migrator\Services\MigrationReporter;
use FontAwesome\Migrator\Support\DirectoryHelper;
use Illuminate\Console\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class MigrateCommand extends Command
{
    /**
     * Liste des sauvegardes créées pendant la migration
     */
    protected array $createdBackups = [];

    protected FileScanner $scanner;

    protected IconReplacer $replacer;

    protected MigrationReporter $reporter;

    protected AssetMigrator $assetMigrator;

    protected MetadataManager $metadata;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fontawesome:migrate
                            {--dry-run : Prévisualiser les changements sans les appliquer}
                            {--path= : Chemin spécifique à analyser}
                            {--backup : Forcer la création de sauvegardes}
                            {--no-backup : Désactiver les sauvegardes}
                            {--report : Générer un rapport détaillé}
                            {--icons-only : Migrer uniquement les classes d\'icônes}
                            {--assets-only : Migrer uniquement les assets (CSS, JS, CDN)}
                            {--no-interactive : Désactiver le mode interactif}
                            {--debug : Afficher les informations de debug de l\'environnement}';

    /**
     * The console command description.
     */
    protected $description = 'Migrer Font Awesome 5 vers Font Awesome 6 (icônes et assets) dans votre application Laravel';

    /**
     * Execute the console command.
     */
    public function handle(
        FileScanner $scanner,
        IconReplacer $replacer,
        MigrationReporter $reporter,
        AssetMigrator $assetMigrator,
        MetadataManager $metadata
    ): int {
        // Assigner les services aux propriétés de classe
        $this->scanner = $scanner;
        $this->replacer = $replacer;
        $this->reporter = $reporter;
        $this->assetMigrator = $assetMigrator;
        $this->metadata = $metadata;

        // Initialiser les métadonnées
        $this->metadata->initialize();

        // Mode interactif par défaut, sauf si --no-interactive est spécifié
        if (! $this->option('no-interactive')) {
            return $this->handleInteractive();
        }

        // Mode classique avec options de ligne de commande
        return $this->handleClassic();
    }

    /**
     * Mode interactif avec Laravel Prompts
     */
    protected function handleInteractive(): int
    {
        intro('🚀 FontAwesome Migrator - Mode Interactif');

        // Validation de la configuration
        if (! $this->validateConfiguration()) {
            return Command::FAILURE;
        }

        // Sélection du mode de migration
        $migrationMode = select(
            'Quel type de migration souhaitez-vous effectuer ?',
            [
                'complete' => '🔄 Complète (icônes + assets)',
                'icons' => '🎯 Icônes uniquement',
                'assets' => '🎨 Assets uniquement (CSS, JS, CDN)',
            ],
            default: 'complete'
        );

        // Mode dry-run
        $isDryRun = confirm('Mode prévisualisation (dry-run) ?', false);

        if ($isDryRun) {
            warning('Mode DRY-RUN activé - Aucune modification ne sera appliquée');
        }

        // Chemin personnalisé
        $useCustomPath = confirm('Analyser un chemin spécifique ?', false);
        $customPath = null;

        if ($useCustomPath) {
            $customPath = text(
                'Chemin à analyser',
                placeholder: 'ex: resources/views, public/css/app.css'
            );
        }

        // Génération de rapport
        $generateReport = confirm('Générer un rapport détaillé ?', true);

        // Configuration des sauvegardes
        $backupOption = $this->configureBackups();

        // Résumé de la configuration
        $this->displayMigrationSummary($migrationMode, $isDryRun, $customPath, $generateReport, $backupOption);

        if (! confirm('Confirmer la migration avec ces paramètres ?', true)) {
            outro('❌ Migration annulée par l\'utilisateur');

            return Command::SUCCESS;
        }

        // Exécution de la migration
        return $this->executeMigration([
            'dry-run' => $isDryRun,
            'path' => $customPath,
            'icons-only' => $migrationMode === 'icons',
            'assets-only' => $migrationMode === 'assets',
            'report' => $generateReport,
            'backup' => $backupOption === 'force',
            'no-backup' => $backupOption === 'disable',
        ]);
    }

    /**
     * Mode classique avec options de ligne de commande
     */
    protected function handleClassic(): int
    {
        $this->info('🚀 Démarrage de la migration Font Awesome 5 → 6');

        // Afficher les informations de debug si demandé
        if ($this->option('debug')) {
            $this->displayDebugInfo();
        }

        // Validation de la configuration
        if (! $this->validateConfiguration()) {
            return Command::FAILURE;
        }

        $isDryRun = $this->option('dry-run');
        $customPath = $this->option('path');
        $iconsOnly = $this->option('icons-only');
        $assetsOnly = $this->option('assets-only');

        // Validation des options
        if ($iconsOnly && $assetsOnly) {
            $this->error('Les options --icons-only et --assets-only sont mutuellement exclusives');

            return Command::FAILURE;
        }

        return $this->executeMigration([
            'dry-run' => $isDryRun,
            'path' => $customPath,
            'icons-only' => $iconsOnly,
            'assets-only' => $assetsOnly,
            'report' => $this->option('report'),
            'backup' => $this->option('backup'),
            'no-backup' => $this->option('no-backup'),
        ]);
    }

    /**
     * Exécuter la migration avec les options données
     */
    protected function executeMigration(array $options): int
    {
        $isDryRun = $options['dry-run'] ?? false;
        $customPath = $options['path'] ?? null;
        $iconsOnly = $options['icons-only'] ?? false;
        $assetsOnly = $options['assets-only'] ?? false;

        // Par défaut : migration complète (icônes + assets)
        $migrateIcons = ! $assetsOnly;
        $migrateAssets = ! $iconsOnly;

        // Configurer les métadonnées avec les options analysées
        $this->metadata
            ->setMigrationOptions($options)
            ->setDryRun($isDryRun)
            ->addCustomData('migration_scope', [
                'migrate_icons' => $migrateIcons,
                'migrate_assets' => $migrateAssets,
                'custom_path' => $customPath,
            ])
            ->startMigration();

        if ($isDryRun) {
            $this->warn('Mode DRY-RUN activé - Aucune modification ne sera appliquée');
        }

        if ($assetsOnly) {
            $this->info('🎨 Mode assets uniquement - Migration des références CSS/JS/CDN');
        } elseif ($iconsOnly) {
            $this->info('🎯 Mode icônes uniquement - Migration des classes d\'icônes');
        } else {
            $this->info('🔄 Mode complet - Migration des icônes ET des assets');
        }

        // Configuration du scanner
        $paths = $customPath ? [$customPath] : config('fontawesome-migrator.scan_paths');

        $this->info('📂 Analyse des fichiers...');
        $progressBar = $this->output->createProgressBar();

        // Scanner les fichiers
        $files = $this->scanner->scanPaths($paths, function ($current, $total) use ($progressBar): void {
            $progressBar->setMaxSteps($total);
            $progressBar->setProgress($current);
        });

        $progressBar->finish();
        $this->newLine();

        if ($files === []) {
            $this->warn('Aucun fichier trouvé à analyser.');

            return Command::SUCCESS;
        }

        $this->info(\sprintf('📋 %d fichiers trouvés', \count($files)));

        // Traitement selon le mode sélectionné
        $results = [];

        if ($migrateIcons) {
            $this->info('🔍 Recherche des icônes Font Awesome 5...');
            $iconResults = $this->replacer->processFiles($files, $isDryRun);
            $results = $iconResults;

            // Collecter les sauvegardes créées par IconReplacer
            $this->collectBackupsFromResults($iconResults);
        }

        if ($migrateAssets) {
            $this->info('🎨 Migration des assets Font Awesome 5...');
            $assetResults = $this->processAssets($files, $isDryRun);

            if ($migrateIcons) {
                // Fusionner les résultats
                $results = $this->mergeResults($results, $assetResults);
            } else {
                $results = $assetResults;
            }
        }

        // Afficher les résultats
        $this->displayResults($results, $isDryRun);

        // Finaliser les métadonnées avec les statistiques
        $totalFiles = \count($results);
        $modifiedFiles = collect($results)->filter(fn ($result): bool => ! empty($result['changes']))->count();
        $totalChanges = collect($results)->sum(fn ($result): int => \count($result['changes']));
        $totalWarnings = collect($results)->sum(fn ($result): int => \count($result['warnings'] ?? []));

        $this->metadata
            ->updateStatistics([
                'files_scanned' => $totalFiles,
                'files_modified' => $modifiedFiles,
                'changes_made' => $totalChanges,
                'warnings_generated' => $totalWarnings,
            ])
            ->completeMigration();

        // Sauvegarder les métadonnées dans le répertoire de session
        $this->metadata->saveToFile();
        $sessionDir = $this->metadata->getSessionDirectory();
        $this->line('📋 Session sauvegardée : '.basename($sessionDir));

        // Générer le rapport si demandé
        if ($this->option('report') || config('fontawesome-migrator.generate_report')) {
            // Créer le reporter avec les bonnes métadonnées
            $reporterWithMetadata = new MigrationReporter($this->metadata);
            $reportInfo = $reporterWithMetadata->generateReport($results);

            // Sauvegarder les métadonnées mises à jour avec les chemins des rapports
            $this->metadata->saveToFile();

            $this->info('📊 Rapport généré :');
            $this->line('   • Fichier : '.$reportInfo['filename']);
            $this->line('   • HTML : '.$reportInfo['html_url']);
            $this->line('   • JSON : '.$reportInfo['json_url']);
            $this->line('   • Menu : '.url('/fontawesome-migrator/reports'));
        }

        if ($isDryRun) {
            $this->info('✨ Prévisualisation terminée. Utilisez la commande sans --dry-run pour appliquer les changements.');
        } else {
            $this->info('✅ Migration terminée avec succès !');
        }

        return Command::SUCCESS;
    }

    /**
     * Configurer les options de sauvegarde
     */
    protected function configureBackups(): string
    {
        $backupDefault = config('fontawesome-migrator.backup_files', true);

        $backupChoice = select(
            'Configuration des sauvegardes',
            [
                'default' => $backupDefault ? '📦 Par défaut (activées)' : '📦 Par défaut (désactivées)',
                'force' => '✅ Forcer les sauvegardes',
                'disable' => '❌ Désactiver les sauvegardes',
            ],
            default: 'default'
        );

        if ($backupChoice === 'force') {
            info('✅ Sauvegardes forcées - Les fichiers seront sauvegardés avant modification');
        } elseif ($backupChoice === 'disable') {
            warning('⚠️ Sauvegardes désactivées - Aucune sauvegarde ne sera créée');
        }

        return $backupChoice;
    }

    /**
     * Afficher le résumé de la migration
     */
    protected function displayMigrationSummary(string $mode, bool $isDryRun, ?string $customPath, bool $generateReport, string $backupOption): void
    {
        $modeLabels = [
            'complete' => '🔄 Migration complète (icônes + assets)',
            'icons' => '🎯 Migration des icônes uniquement',
            'assets' => '🎨 Migration des assets uniquement',
        ];

        $summary = [
            '📋 Résumé de la configuration :',
            '',
            '• Mode : '.$modeLabels[$mode],
            '• Prévisualisation : '.($isDryRun ? '✅ Activée (dry-run)' : '❌ Désactivée'),
            '• Chemin : '.($customPath !== null && $customPath !== '' && $customPath !== '0' ? $customPath : '📂 Chemins par défaut'),
            '• Rapport : '.($generateReport ? '✅ Généré' : '❌ Non généré'),
            '• Sauvegardes : '.match ($backupOption) {
                'force' => '✅ Forcées',
                'disable' => '❌ Désactivées',
                default => '📦 Par défaut'
            },
        ];

        note(implode("\n", $summary));
    }

    protected function validateConfiguration(): bool
    {
        $licenseType = config('fontawesome-migrator.license_type');

        if (! \in_array($licenseType, ['free', 'pro'])) {
            $this->error('Type de licence invalide. Utilisez "free" ou "pro".');

            return false;
        }

        $scanPaths = config('fontawesome-migrator.scan_paths');

        if (empty($scanPaths)) {
            $this->error('Aucun chemin de scan configuré.');

            return false;
        }

        return true;
    }

    protected function displayResults(array $results, bool $isDryRun): void
    {
        $totalFiles = \count($results);
        $modifiedFiles = collect($results)->filter(fn ($result): bool => ! empty($result['changes']))->count();
        $totalChanges = collect($results)->sum(fn ($result): int => \count($result['changes']));

        $this->newLine();
        $this->info('📊 Résultats de la migration :');
        $this->line('   • Fichiers analysés : '.$totalFiles);
        $this->line('   • Fichiers modifiés : '.$modifiedFiles);
        $this->line('   • Total des changements : '.$totalChanges);

        if ($this->getOutput()->isVerbose() || $totalChanges < 20) {
            $this->newLine();
            $this->info('📝 Détail des changements :');

            foreach ($results as $result) {
                if (! empty($result['changes'])) {
                    $this->line(\sprintf('   📄 %s:', $result['file']));

                    foreach ($result['changes'] as $change) {
                        $status = $isDryRun ? '(DRY-RUN)' : '✓';
                        $this->line(\sprintf('      %s %s → %s', $status, $change['from'], $change['to']));
                    }
                }
            }
        }

        if (! empty($results[0]['warnings'] ?? [])) {
            $this->newLine();
            $this->warn('⚠️  Avertissements :');

            foreach ($results as $result) {
                foreach ($result['warnings'] ?? [] as $warning) {
                    $this->line('   • '.$warning);
                }
            }
        }
    }

    /**
     * Traiter les assets FontAwesome dans les fichiers
     */
    protected function processAssets(array $files, bool $isDryRun): array
    {
        $results = [];
        $progressBar = $this->output->createProgressBar(\count($files));

        foreach ($files as $file) {
            $progressBar->advance();

            $filePath = $file['path'];
            $result = [
                'file' => $filePath,
                'changes' => [],
                'warnings' => [],
                'assets' => [],
            ];

            // Toujours essayer la migration des assets, même si analyzeAssets ne trouve rien
            $assetAnalysis = $this->assetMigrator->analyzeAssets($filePath);
            $result['assets'] = $assetAnalysis['assets'] ?? [];

            if (! $isDryRun) {
                // Lire le contenu du fichier
                $content = file_get_contents($filePath);
                $originalContent = $content;

                // Appliquer la migration des assets
                $migratedContent = $this->assetMigrator->migrateAssets($filePath, $content);

                if ($migratedContent !== $originalContent) {
                    // Créer une sauvegarde si configuré
                    if ($this->shouldCreateBackup()) {
                        $this->createBackup($filePath);
                    }

                    // Écrire le contenu migré
                    file_put_contents($filePath, $migratedContent);

                    // Enregistrer les changements détectés
                    $this->detectAssetChanges($originalContent, $migratedContent, $result);
                }
            } else {
                // Mode dry-run : simuler les changements
                $content = file_get_contents($filePath);
                $migratedContent = $this->assetMigrator->migrateAssets($filePath, $content);

                if ($migratedContent !== $content) {
                    $this->detectAssetChanges($content, $migratedContent, $result);
                }
            }

            $results[] = $result;
        }

        $progressBar->finish();
        $this->newLine();

        return $results;
    }

    /**
     * Détecter les changements d'assets entre l'original et le migré
     */
    protected function detectAssetChanges(string $original, string $migrated, array &$result): void
    {
        $originalLines = explode("\n", $original);
        $migratedLines = explode("\n", $migrated);

        for ($i = 0; $i < min(\count($originalLines), \count($migratedLines)); $i++) {
            if ($originalLines[$i] !== $migratedLines[$i]) {
                // Simplifier l'affichage pour les changements d'assets
                $from = trim($originalLines[$i]);
                $to = trim($migratedLines[$i]);

                if ($from !== $to && ($from !== '' && $from !== '0') && ($to !== '' && $to !== '0')) {
                    $result['changes'][] = [
                        'type' => 'asset',
                        'line' => $i + 1,
                        'from' => \strlen($from) > 80 ? substr($from, 0, 77).'...' : $from,
                        'to' => \strlen($to) > 80 ? substr($to, 0, 77).'...' : $to,
                    ];
                }
            }
        }
    }

    /**
     * Fusionner les résultats de migration des icônes et des assets
     */
    protected function mergeResults(array $iconResults, array $assetResults): array
    {
        $fileMap = [];

        // Indexer les résultats par fichier
        foreach ($iconResults as $result) {
            $fileMap[$result['file']] = $result;
        }

        // Fusionner avec les résultats d'assets
        foreach ($assetResults as $assetResult) {
            $filePath = $assetResult['file'];

            if (isset($fileMap[$filePath])) {
                // Fusionner les changements
                $fileMap[$filePath]['changes'] = array_merge(
                    $fileMap[$filePath]['changes'] ?? [],
                    $assetResult['changes'] ?? []
                );

                // Fusionner les avertissements
                $fileMap[$filePath]['warnings'] = array_merge(
                    $fileMap[$filePath]['warnings'] ?? [],
                    $assetResult['warnings'] ?? []
                );

                // Ajouter les assets
                $fileMap[$filePath]['assets'] = $assetResult['assets'] ?? [];
            } else {
                // Nouveau fichier avec seulement des changements d'assets
                $fileMap[$filePath] = $assetResult;
            }
        }

        return array_values($fileMap);
    }

    /**
     * Déterminer si une sauvegarde doit être créée
     */
    protected function shouldCreateBackup(): bool
    {
        $result = false;

        if ($this->option('backup')) {
            $result = true;
        } elseif ($this->option('no-backup')) {
            $result = false;
        } else {
            $result = config('fontawesome-migrator.backup.enabled', true);
        }

        return $result;
    }

    /**
     * Créer une sauvegarde d'un fichier dans le répertoire de session
     */
    protected function createBackup(string $filePath): void
    {
        $baseBackupDir = config('fontawesome-migrator.sessions_path');

        // Créer le répertoire de session basé sur l'ID de session des métadonnées
        $sessionId = $this->metadata->get('session')['id'] ?? 'unknown';
        $sessionDir = $baseBackupDir.'/session-'.$sessionId;

        // S'assurer que le répertoire de session et le .gitignore existent
        DirectoryHelper::ensureExistsWithGitignore($sessionDir);

        $relativePath = str_replace(base_path().'/', '', $filePath);
        $backupFilename = str_replace('/', '_', $relativePath);
        $backupPath = $sessionDir.'/'.$backupFilename;

        copy($filePath, $backupPath);

        // Enregistrer la sauvegarde créée
        $backupInfo = [
            'original_file' => $filePath,
            'relative_path' => $relativePath,
            'backup_path' => $backupPath,
            'backup_filename' => $backupFilename,
            'session_dir' => $sessionDir,
            'session_id' => $sessionId,
            'created_at' => date('Y-m-d H:i:s'),
            'size' => filesize($backupPath),
        ];

        $this->createdBackups[] = $backupInfo;
        $this->metadata->addBackup($backupInfo);
    }

    /**
     * Collecter les sauvegardes depuis les résultats de migration
     */
    protected function collectBackupsFromResults(array $results): void
    {
        foreach ($results as $result) {
            if (isset($result['backup']) && $result['backup'] !== null) {
                $this->createdBackups[] = $result['backup'];
                $this->metadata->addBackup($result['backup']);
            }
        }
    }

    /**
     * Afficher les informations de debug de l'environnement
     */
    protected function displayDebugInfo(): void
    {
        $this->info('🔧 Informations de debug de l\'environnement :');
        $this->newLine();

        $debugInfo = [
            'Répertoire de travail' => getcwd(),
            'Utilisateur' => get_current_user(),
            'PHP SAPI' => PHP_SAPI,
            'Environnement Laravel' => app()->environment(),
            'Cache config' => app()->getCachedConfigPath() ?: 'Non mis en cache',
            'Cache routes' => app()->getCachedRoutesPath() ?: 'Non mis en cache',
            'Sessions path' => config('fontawesome-migrator.sessions_path'),
            'License type' => config('fontawesome-migrator.license_type'),
            'Backup enabled' => config('fontawesome-migrator.backup.enabled') ? 'Oui' : 'Non',
        ];

        // Afficher les chemins de scan
        $scanPaths = config('fontawesome-migrator.scan_paths', []);
        $debugInfo['Chemins de scan'] = \count($scanPaths) > 0 ? implode(', ', $scanPaths) : 'Aucun configuré';

        // Afficher les extensions de fichiers
        $fileExtensions = config('fontawesome-migrator.file_extensions', []);
        $debugInfo['Extensions fichiers'] = \count($fileExtensions) > 0 ? implode(', ', $fileExtensions) : 'Aucune configurée';

        // Affichage formaté
        foreach ($debugInfo as $key => $value) {
            $this->line(\sprintf('  <fg=cyan>%s:</> <fg=white>%s</>', $key, $value));
        }

        $this->newLine();
    }
}
