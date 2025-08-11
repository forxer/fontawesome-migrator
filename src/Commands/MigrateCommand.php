<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Commands;

use FontAwesome\Migrator\Contracts\FileScannerInterface;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use FontAwesome\Migrator\Services\Core\MigrationProcessor;
use FontAwesome\Migrator\Services\Core\VersionConfigurationService;
use FontAwesome\Migrator\Services\Metadata\MigrationReporter;
use Illuminate\Console\Command;
use RuntimeException;

use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\table;

class MigrateCommand extends Command
{
    /**
     * Liste des sauvegardes créées pendant la migration
     */
    protected array $createdBackups = [];

    protected FileScannerInterface $scanner;

    protected MigrationProcessor $processor;

    protected MigrationReporter $reporter;

    protected MetadataManagerInterface $metadata;

    protected VersionConfigurationService $versionConfigService;

    /**
     * Options de migration stockées dans la commande
     */
    protected array $migrationOptions = [];

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fontawesome:migrate
                            {--from= : Version source de FontAwesome (4, 5, 6)}
                            {--to= : Version cible de FontAwesome (5, 6, 7)}
                            {--dry-run : Prévisualiser les changements sans les appliquer}
                            {--backup : Forcer la création de sauvegardes}
                            {--no-backup : Désactiver les sauvegardes}
                            {--icons-only : Migrer uniquement les classes d\'icônes}
                            {--assets-only : Migrer uniquement les assets (CSS, JS, CDN)}
                            {--no-interactive : Désactiver le mode interactif}
                            {--debug : Afficher les informations de debug de l\'environnement}
                            {--web-interface : Marquer que la migration provient de l\'interface web}
                            ';

    /**
     * The console command description.
     */
    protected $description = 'Migrer FontAwesome vers une version plus récente dans votre application Laravel';

    /**
     * Execute the console command.
     */
    public function handle(
        FileScannerInterface $scanner,
        MigrationProcessor $processor,
        MigrationReporter $reporter,
        MetadataManagerInterface $metadata,
        VersionConfigurationService $versionConfigService
    ): int {
        // Assigner les services aux propriétés de classe
        $this->scanner = $scanner;
        $this->processor = $processor;
        $this->reporter = $reporter;
        $this->metadata = $metadata;
        $this->versionConfigService = $versionConfigService;

        intro('🚀 FontAwesome Migrator v2.0');

        // 1. Initialiser les métadonnées
        $this->metadata->initialize();

        // 2. Capturer les options de commande
        $this->captureCommandOptions();

        // 2b. Afficher les informations de debug si demandé
        if ($this->migrationOptions['debug']) {
            $this->displayDebugInfo();
        }

        // 3. Transmettre les options aux métadonnées
        $this->metadata->setMigrationOptions($this->migrationOptions);

        // 4. Configurer les versions
        $this->configureVersions();

        info('✅ Métadonnées, options et versions configurées');

        // 5. Scanner les fichiers à migrer
        $files = $this->scanFiles();

        // 6. Déterminer si les sauvegardes sont nécessaires
        $this->configureBackupOption();

        // 7. Traiter les migrations (icônes et assets)
        $results = $this->processor->process($files, $this->migrationOptions);

        // 7. Afficher les résultats consolidés
        $this->displayResults($results);

        return Command::SUCCESS;
    }

    private function captureCommandOptions(): void
    {
        // Stocker les options dans la commande
        $this->migrationOptions = [
            'source_version' => $this->option('from'),
            'target_version' => $this->option('to'),
            'dry_run' => $this->option('dry-run'),
            'backup' => $this->option('backup'),
            'no_backup' => $this->option('no-backup'),
            'icons_only' => $this->option('icons-only'),
            'assets_only' => $this->option('assets-only'),
            'no_interactive' => $this->option('no-interactive'),
            'debug' => $this->option('debug'),
            'web_interface' => $this->option('web-interface'),
        ];
    }

    private function configureVersions(): void
    {
        try {
            $sourceVersion = $this->migrationOptions['source_version'];
            $targetVersion = $this->migrationOptions['target_version'];

            // Déléguer la configuration au service
            $versionConfig = $this->versionConfigService->configureVersions($sourceVersion, $targetVersion);

            // Stocker les versions finales
            $this->migrationOptions['source_version'] = $versionConfig['source_version'];
            $this->migrationOptions['target_version'] = $versionConfig['target_version'];

            info(\sprintf('🌐 Migration configurée : FontAwesome %s → %s', $versionConfig['source_version'], $versionConfig['target_version']));

            // Mettre à jour les métadonnées
            $this->metadata->setMigrationOptions($this->migrationOptions);

        } catch (RuntimeException $runtimeException) {
            $this->error($runtimeException->getMessage());

            exit(Command::FAILURE);
        }
    }

    private function scanFiles(): array
    {
        // Récupérer les chemins configurés
        $paths = config('fontawesome-migrator.scan_paths', []);

        if (empty($paths)) {
            throw new RuntimeException('Aucun chemin configuré pour le scan. Vérifiez votre configuration fontawesome-migrator.scan_paths');
        }

        // Scanner les fichiers
        $files = $this->scanner->scanPaths($paths);

        if ($files === []) {
            $this->warn('Aucun fichier trouvé dans les chemins configurés');

            return [];
        }

        info('📁 {'.\count($files).'} fichier(s) trouvé(s) à analyser');

        return $files;
    }

    private function displayDebugInfo(): void
    {
        info('');
        info('🔍 Debug Information');
        info('');

        // Environnement
        table(
            ['📦 Environment', 'Value'],
            [
                ['Laravel Version', app()->version()],
                ['PHP Version', PHP_VERSION],
                ['FontAwesome Migrator', config('fontawesome-migrator.version', 'v2.0')],
                ['Working Directory', getcwd()],
            ]
        );

        // Options de commande
        $commandOptions = [];

        foreach ($this->migrationOptions as $key => $value) {
            $displayValue = \is_bool($value) ? ($value ? '✅ true' : '❌ false') : ($value ?? '⚪ null');
            $commandOptions[] = [str_replace('_', ' ', ucfirst($key)), $displayValue];
        }

        table(
            ['⚙️  Command Option', 'Value'],
            $commandOptions
        );

        // Configuration
        table(
            ['📋 Configuration', 'Value'],
            [
                ['Scan Paths', implode(', ', config('fontawesome-migrator.scan_paths', []))],
                ['Migrations Path', config('fontawesome-migrator.migrations_path')],
                ['Backup Enabled', config('fontawesome-migrator.backup_files') ? '✅ Yes' : '❌ No'],
                ['Auto-detect Version', config('fontawesome-migrator.auto_detect_version') ? '✅ Yes' : '❌ No'],
            ]
        );

        // Versions supportées
        table(
            ['🔄 Migration', 'Description'],
            [
                ['4 → 5', 'FontAwesome 4 to 5'],
                ['5 → 6', 'FontAwesome 5 to 6'],
                ['6 → 7', 'FontAwesome 6 to 7'],
            ]
        );

        info('');
    }

    private function configureBackupOption(): void
    {
        // Priorité : --no-backup > --backup > config > default (true)
        if ($this->migrationOptions['no_backup']) {
            $this->migrationOptions['create_backups'] = false;
            info('⚠️  Sauvegardes désactivées (--no-backup)');
        } elseif ($this->migrationOptions['backup']) {
            $this->migrationOptions['create_backups'] = true;
            info('💾 Sauvegardes forcées (--backup)');
        } else {
            // Utiliser la config par défaut
            $this->migrationOptions['create_backups'] = config('fontawesome-migrator.backup_files', true);

            if ($this->migrationOptions['create_backups']) {
                info('💾 Sauvegardes activées (configuration par défaut)');
            }
        }

        // En mode dry-run, pas de sauvegardes
        if ($this->migrationOptions['dry_run']) {
            $this->migrationOptions['create_backups'] = false;
            info('ℹ️  Mode dry-run : sauvegardes désactivées');
        }

        // Transmettre l'option aux métadonnées
        $this->metadata->setMigrationOptions($this->migrationOptions);
    }

    private function displayResults(array $results): void
    {
        $dryRun = $this->migrationOptions['dry_run'];

        info('');
        info('📊 Résumé de la migration'.($dryRun ? ' (dry-run)' : ''));
        info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Statistiques globales
        info('📁 Fichiers analysés : '.$results['total_files_processed']);
        info('✏️  Fichiers modifiés : '.$results['total_files_modified']);

        // Détails icônes
        $iconChanges = $results['icons']['total_changes'] ?? 0;

        if ($iconChanges > 0) {
            info('🔄 Icônes migrées : '.$iconChanges);
        }

        // Détails assets
        $assetChanges = $results['assets']['total_assets'] ?? 0;

        if ($assetChanges > 0) {
            info('📦 Assets migrés : '.$assetChanges);
        }

        if ($dryRun) {
            info('');
            info('💡 Mode dry-run : aucun fichier n\'a été modifié');
            info('   Pour appliquer les changements, relancez sans --dry-run');
        }
    }
}
