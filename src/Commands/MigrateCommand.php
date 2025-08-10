<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Commands;

use FontAwesome\Migrator\Contracts\FileScannerInterface;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use FontAwesome\Migrator\Services\Core\AssetMigrator;
use FontAwesome\Migrator\Services\Core\IconReplacer;
use FontAwesome\Migrator\Services\Core\MigrationVersionManager;
use FontAwesome\Migrator\Services\Core\VersionConfigurationService;
use FontAwesome\Migrator\Services\Metadata\MigrationReporter;
use Illuminate\Console\Command;

use function Laravel\Prompts\intro;

class MigrateCommand extends Command
{
    /**
     * Liste des sauvegardes créées pendant la migration
     */
    protected array $createdBackups = [];

    protected FileScannerInterface $scanner;

    protected IconReplacer $replacer;

    protected MigrationReporter $reporter;

    protected AssetMigrator $assetMigrator;

    protected MetadataManagerInterface $metadata;

    protected MigrationVersionManager $versionManager;

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
                            {--path= : Chemin spécifique à analyser}
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
        IconReplacer $replacer,
        MigrationReporter $reporter,
        AssetMigrator $assetMigrator,
        MetadataManagerInterface $metadata,
        MigrationVersionManager $versionManager,
        VersionConfigurationService $versionConfigService
    ): int {
        // Assigner les services aux propriétés de classe
        $this->scanner = $scanner;
        $this->replacer = $replacer;
        $this->reporter = $reporter;
        $this->assetMigrator = $assetMigrator;
        $this->metadata = $metadata;
        $this->versionManager = $versionManager;
        $this->versionConfigService = $versionConfigService;

        intro('🚀 FontAwesome Migrator v2.0');

        // 1. Initialiser les métadonnées
        $this->metadata->initialize();

        // 2. Capturer les options de commande
        $this->captureCommandOptions();

        // 3. Transmettre les options aux métadonnées
        $this->metadata->setMigrationOptions($this->migrationOptions);

        // 4. Configurer les versions
        $this->configureVersions();

        info('✅ Métadonnées, options et versions configurées');

        return Command::SUCCESS;
    }

    private function captureCommandOptions(): void
    {
        // Stocker les options dans la commande
        $this->migrationOptions = [
            'source_version' => $this->option('from'),
            'target_version' => $this->option('to'),
            'dry_run' => $this->option('dry-run'),
            'path' => $this->option('path'),
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

            info("🌐 Migration configurée : FontAwesome {$versionConfig['source_version']} → {$versionConfig['target_version']}");

            // Mettre à jour les métadonnées
            $this->metadata->setMigrationOptions($this->migrationOptions);

        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            exit(Command::FAILURE);
        }
    }
}
