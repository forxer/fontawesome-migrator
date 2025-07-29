<?php

namespace FontAwesome\Migrator\Commands;

use FontAwesome\Migrator\Services\AssetMigrator;
use FontAwesome\Migrator\Services\FileScanner;
use FontAwesome\Migrator\Services\IconReplacer;
use FontAwesome\Migrator\Services\MigrationReporter;
use FontAwesome\Migrator\Support\GitignoreHelper;
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
                            {--no-interactive : Désactiver le mode interactif}';

    /**
     * The console command description.
     */
    protected $description = 'Migrer Font Awesome 5 vers Font Awesome 6 (icônes et assets) dans votre application Laravel';

    public function __construct(
        protected FileScanner $scanner,
        protected IconReplacer $replacer,
        protected MigrationReporter $reporter,
        protected AssetMigrator $assetMigrator,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
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

        // Générer le rapport si demandé
        if ($this->option('report') || config('fontawesome-migrator.generate_report')) {
            // Préparer les options de migration pour le rapport
            $migrationOptions = [
                'dry_run' => $isDryRun,
                'custom_path' => $customPath,
                'icons_only' => $iconsOnly,
                'assets_only' => $assetsOnly,
                'migrate_icons' => $migrateIcons,
                'migrate_assets' => $migrateAssets,
                'backup' => $this->option('backup'),
                'no_backup' => $this->option('no-backup'),
                'report' => $this->option('report'),
                'created_backups' => $this->createdBackups,
                'backups_count' => \count($this->createdBackups),
            ];

            $reportInfo = $this->reporter
                ->setDryRun($isDryRun)
                ->setMigrationOptions($migrationOptions)
                ->generateReport($results);
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
        if ($this->option('backup')) {
            return true;
        }

        if ($this->option('no-backup')) {
            return false;
        }

        return config('fontawesome-migrator.backup.enabled', true);
    }

    /**
     * Créer une sauvegarde d'un fichier
     */
    protected function createBackup(string $filePath): void
    {
        $backupDir = config('fontawesome-migrator.backup.path', storage_path('app/fontawesome-backups'));

        // S'assurer que le répertoire et le .gitignore existent
        GitignoreHelper::ensureDirectoryWithGitignore($backupDir);

        $timestamp = date('Y-m-d_H-i-s');
        $relativePath = str_replace(base_path().'/', '', $filePath);
        $backupPath = $backupDir.'/'.$timestamp.'_'.str_replace('/', '_', $relativePath);

        copy($filePath, $backupPath);

        // Enregistrer la sauvegarde créée
        $this->createdBackups[] = [
            'original_file' => $filePath,
            'relative_path' => $relativePath,
            'backup_path' => $backupPath,
            'timestamp' => $timestamp,
            'created_at' => date('Y-m-d H:i:s'),
            'size' => filesize($backupPath),
        ];
    }

    /**
     * Collecter les sauvegardes depuis les résultats de migration
     */
    protected function collectBackupsFromResults(array $results): void
    {
        foreach ($results as $result) {
            if (isset($result['backup']) && $result['backup'] !== null) {
                $this->createdBackups[] = $result['backup'];
            }
        }
    }
}
