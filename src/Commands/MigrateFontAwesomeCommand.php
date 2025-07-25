<?php

namespace FontAwesome\Migrator\Commands;

use FontAwesome\Migrator\Services\AssetMigrator;
use FontAwesome\Migrator\Services\FileScanner;
use FontAwesome\Migrator\Services\IconReplacer;
use FontAwesome\Migrator\Services\MigrationReporter;
use Illuminate\Console\Command;

class MigrateFontAwesomeCommand extends Command
{
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
                            {--assets-only : Migrer uniquement les assets (CSS, JS, CDN)}';

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

        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $relativePath = str_replace(base_path().'/', '', $filePath);
        $backupPath = $backupDir.'/'.$timestamp.'_'.str_replace('/', '_', $relativePath);

        copy($filePath, $backupPath);
    }
}
