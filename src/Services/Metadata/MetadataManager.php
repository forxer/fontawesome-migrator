<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Metadata;

use Carbon\Carbon;
use FontAwesome\Migrator\Contracts\ConfigurationInterface;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use FontAwesome\Migrator\Support\FormatterHelper;
use FontAwesome\Migrator\Support\JsonFileHelper;
use Illuminate\Support\Facades\File;

class MetadataManager implements MetadataManagerInterface
{
    protected array $metadata = [];

    public function __construct(
        protected ConfigurationInterface $config,
        protected MigrationMigrationService $migrationService,
        protected MigrationResultsService $resultsService,
        protected MigrationStorageService $storageService,
        protected MetadataBuilder $metadataBuilder
    ) {}

    /**
     * Initialiser les métadonnées de base
     */
    public function initialize(): self
    {
        // Initialiser la migration via le service dédié
        $this->migrationService->initializeMigration();
        $migrationData = $this->migrationService->getMigrationData();

        // Construire la structure unifiée avec données de migration
        $defaultMetadata = $this->metadataBuilder->buildDefaultMetadata();
        $this->metadata = array_merge($migrationData, $defaultMetadata);

        return $this;
    }

    /**
     * Définir les options de migration
     */
    public function setMigrationOptions(array $options): self
    {
        // Déléguer au service de migration
        $this->migrationService->setMigrationOptions($options);

        // Synchroniser avec metadata local
        $migrationData = $this->migrationService->getMigrationData();
        $this->metadata = array_merge($this->metadata, $migrationData);

        return $this;
    }

    /**
     * Définir le mode dry-run
     */
    public function setDryRun(bool $isDryRun): self
    {
        // Déléguer au service de migration
        $this->migrationService->setDryRun($isDryRun);

        // Synchroniser avec metadata local
        $this->metadata['dry_run'] = $isDryRun;

        return $this;
    }

    /**
     * Marquer la fin de la migration
     */
    public function completeMigration(): self
    {
        // Déléguer au service de migration
        $this->migrationService->completeMigration();

        // Synchroniser avec metadata local
        $migrationData = $this->migrationService->getMigrationData();
        $this->metadata['completed_at'] = $migrationData['completed_at'] ?? null;
        $this->metadata['duration'] = $migrationData['duration'] ?? null;

        return $this;
    }

    /**
     * Ajouter une sauvegarde
     */
    public function addBackup(array $backupInfo): self
    {
        // Déléguer au service de résultats
        $this->resultsService->addBackup($backupInfo);

        // Synchroniser avec metadata local
        $backups = $this->resultsService->getBackups();
        $this->metadata['backup_files'] = $backups;
        $this->metadata['backup_count'] = \count($backups);
        $this->metadata['backup_size'] = array_sum(array_column($backups, 'size'));

        return $this;
    }

    /**
     * Stocker les résultats de migration
     */
    public function storeMigrationResults(array $results, array $stats, array $enrichedWarnings = []): self
    {
        // Déléguer au service de résultats
        $this->resultsService->storeResults($results, $stats, $enrichedWarnings);

        // Synchroniser avec metadata local
        $resultsData = $this->resultsService->getResults();
        $this->metadata['total_files'] = $resultsData['total_files'] ?? 0;
        $this->metadata['modified_files'] = $resultsData['modified_files'] ?? 0;
        $this->metadata['total_changes'] = $stats['total_changes'] ?? 0;
        $this->metadata['warnings'] = \count($resultsData['warnings'] ?? []);
        $this->metadata['errors'] = \count($resultsData['errors'] ?? []);
        $this->metadata['assets_migrated'] = $stats['assets_migrated'] ?? 0;
        $this->metadata['icons_migrated'] = $stats['icons_migrated'] ?? 0;
        $this->metadata['migration_success'] = $stats['migration_success'] ?? true;

        // === DETAILED DATA ===
        $this->metadata['files'] = array_map(fn ($result): array => [
            'file' => $result['file'],
            'success' => $result['success'] ?? true,
            'changes_count' => \count($result['changes'] ?? []),
            'warnings_count' => \count($result['warnings'] ?? []),
            'assets_count' => \count($result['assets'] ?? []),
            'changes' => $result['changes'] ?? [],
            'warnings' => $result['warnings'] ?? [],
            'assets' => $result['assets'] ?? [],
        ], $results);

        $this->metadata['warnings_details'] = $enrichedWarnings;
        $this->metadata['changes_by_type'] = $stats['changes_by_type'] ?? [];
        $this->metadata['asset_types'] = $stats['asset_types'] ?? [];

        return $this;
    }

    /**
     * Obtenir les résultats de migration
     */
    public function getMigrationResults(): array
    {
        // Déléguer au service de résultats pour la structure principale
        // Note: $resultsData disponible si besoin pour enrichir la structure

        // Reconstituer la structure attendue pour compatibilité
        return [
            'summary' => [
                'total_files' => $this->metadata['total_files'] ?? 0,
                'modified_files' => $this->metadata['modified_files'] ?? 0,
                'total_changes' => $this->metadata['total_changes'] ?? 0,
                'warnings' => $this->metadata['warnings'] ?? 0,
                'errors' => $this->metadata['errors'] ?? 0,
                'assets_migrated' => $this->metadata['assets_migrated'] ?? 0,
                'icons_migrated' => $this->metadata['icons_migrated'] ?? 0,
                'migration_success' => $this->metadata['migration_success'] ?? true,
                'changes_by_type' => $this->metadata['changes_by_type'] ?? [],
                'asset_types' => $this->metadata['asset_types'] ?? [],
            ],
            'files' => $this->metadata['files'] ?? [],
            'enriched_warnings' => $this->metadata['warnings_details'] ?? [],
        ];
    }

    /**
     * Ajouter des données personnalisées
     */
    public function addCustomData(string $key, mixed $value): self
    {
        // Déléguer au service de résultats pour les données custom
        $this->resultsService->addCustomData($key, $value);

        // Mapper les données vers la nouvelle structure
        if ($key === 'command_options') {
            $this->metadata['command_options'] = $value;
        } elseif ($key === 'migration_scope') {
            $this->metadata['icons_only'] = $value['migrate_icons'] ?? false;
            $this->metadata['assets_only'] = $value['migrate_assets'] ?? false;
            $this->metadata['custom_path'] = $value['custom_path'] ?? null;
        } elseif ($key === 'migration_origin') {
            $this->metadata['migration_source'] = $value['source'] ?? 'command_line';
        }

        return $this;
    }

    /**
     * Obtenir toutes les métadonnées
     */
    public function getAll(): array
    {
        return $this->metadata;
    }

    /**
     * Obtenir une section spécifique des métadonnées
     */
    public function get(string $section): mixed
    {
        return $this->metadata[$section] ?? null;
    }

    /**
     * Obtenir les métadonnées formatées pour les rapports
     */
    public function getForMigration(): array
    {
        return [
            'meta' => [
                'migration_id' => $this->metadata['migration_id'],
                'generated_at' => $this->metadata['started_at'],
                'package_version' => $this->metadata['package_version'],
                'dry_run' => $this->metadata['dry_run'],
                'duration' => $this->metadata['duration'],
                'source_version' => $this->metadata['source_version'],
                'target_version' => $this->metadata['target_version'],
            ],
            'backups' => [
                'created' => $this->metadata['backup_files'],
                'count' => $this->metadata['backup_count'],
                'total_size' => $this->metadata['backup_size'],
            ],
            'migration_results' => $this->getMigrationResults(),
            'environment' => $this->metadata['environment'],
            'scan_config' => $this->metadata['scan_config'],
            'command_options' => $this->metadata['command_options'],
        ];
    }

    /**
     * Sauvegarder les métadonnées dans le répertoire de migration
     */
    public function saveToFile(?string $filePath = null): string
    {
        if ($filePath === null || $filePath === '' || $filePath === '0') {
            // Déterminer le répertoire de migration pour les métadonnées
            $migrationDir = $this->getMigrationDirectory();

            // S'assurer que le répertoire de migration existe avec .gitignore
            $this->ensureMigrationDirectoryExists($migrationDir);

            $filePath = $migrationDir.'/metadata.json';
        }

        // Déléguer au service de stockage
        return $this->storageService->saveToFile($this->metadata, $filePath);
    }

    /**
     * Charger les métadonnées depuis un fichier JSON
     */
    public function loadFromFile(string $filePath): self
    {
        // Déléguer au service de stockage
        $this->metadata = $this->storageService->loadFromFile($filePath);

        return $this;
    }

    /**
     * Valider la structure des métadonnées
     */
    public function validate(): array
    {
        $errors = [];

        // Vérifier les champs obligatoires de la nouvelle structure
        $requiredFields = ['migration_id', 'started_at', 'package_version'];

        foreach ($requiredFields as $field) {
            if (! isset($this->metadata[$field])) {
                $errors[] = 'Champ manquant: '.$field;
            }
        }

        return $errors;
    }

    /**
     * Réinitialiser les métadonnées
     */
    public function reset(): self
    {
        $this->metadata = [];

        return $this->initialize();
    }

    /**
     * Obtenir un résumé des métadonnées
     */
    public function getSummary(): array
    {
        return [
            'migration_id' => $this->metadata['migration_id'] ?? null,
            'version' => $this->metadata['package_version'] ?? null,
            'dry_run' => $this->metadata['dry_run'] ?? false,
            'backups_count' => $this->metadata['backup_count'] ?? 0,
            'files_modified' => $this->metadata['modified_files'] ?? 0,
            'changes_made' => $this->metadata['total_changes'] ?? 0,
            'duration' => $this->metadata['duration'] ?? null,
        ];
    }

    /**
     * S'assurer que le répertoire de migration existe avec .gitignore
     */
    protected function ensureMigrationDirectoryExists(string $migrationDir): void
    {
        if (! File::exists($migrationDir)) {
            File::makeDirectory($migrationDir, 0755, true);
        }

        $gitignorePath = $migrationDir.'/.gitignore';

        if (! File::exists($gitignorePath)) {
            $gitignoreContent = "# FontAwesome Migrator - Migration Backups\n*\n!.gitignore\n!metadata.json\n";
            File::put($gitignorePath, $gitignoreContent);
        }
    }

    /**
     * Obtenir le chemin du répertoire de migration
     */
    public function getMigrationDirectory(): string
    {
        $migrationId = $this->metadata['migration_id'] ?? 'unknown';

        return $this->config->getMigrationsPath().'/migration-'.$migrationId;
    }

    /**
     * Nettoyer les anciens répertoires de migration
     */
    public function cleanOldMigrations(int $daysToKeep = 30): int
    {
        $migrationsDir = config('fontawesome-migrator.migrations_path', storage_path('app/fontawesome-migrator/migrations'));

        if (! File::exists($migrationsDir)) {
            return 0;
        }

        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
        $deleted = 0;

        $directories = File::directories($migrationsDir);

        foreach ($directories as $directory) {
            // Vérifier si c'est un répertoire de migration
            if (\in_array(preg_match('/\/migration-/', (string) $directory), [0, false], true)) {
                continue;
            }

            // Vérifier la date de modification du répertoire
            if (filemtime($directory) >= $cutoffTime) {
                continue;
            }

            // Supprimer le répertoire de migration complet
            if (File::deleteDirectory($directory)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Lister tous les répertoires de migration disponibles
     */
    public function getAvailableMigrations(): array
    {
        $migrationsDir = config('fontawesome-migrator.migrations_path', storage_path('app/fontawesome-migrator/migrations'));
        $migrations = [];

        if (! File::exists($migrationsDir)) {
            return $migrations;
        }

        $directories = File::directories($migrationsDir);

        foreach ($directories as $directory) {
            if (\in_array(preg_match('/\/migration-(.+)$/', (string) $directory, $matches), [0, false], true)) {
                continue;
            }

            $migrationId = $matches[1];
            $metadataPath = $directory.'/metadata.json';

            // Calculer le short_id à partir du migration_id
            $shortId = FormatterHelper::generateShortId('migration_');

            $migrationInfo = [
                'migration_id' => $migrationId,
                'short_id' => $shortId,
                'directory' => $directory,
                'created_at' => Carbon::createFromTimestamp(filemtime($directory)),
                'has_metadata' => File::exists($metadataPath),
                'backup_count' => max(0, \count(File::files($directory)) - 1), // -1 pour exclure metadata.json, minimum 0
                'package_version' => 'unknown',
                'dry_run' => false,
                'duration' => null,
            ];

            // Charger les métadonnées si disponibles
            if ($migrationInfo['has_metadata']) {
                $metadata = JsonFileHelper::loadJson($metadataPath, []);

                // Adapter à la nouvelle structure simplifiée
                $migrationInfo['package_version'] = $metadata['package_version'] ?? 'unknown';
                $migrationInfo['dry_run'] = $metadata['dry_run'] ?? false;
                $migrationInfo['duration'] = $metadata['duration'] ?? null;

                // Inclure les métadonnées complètes
                $migrationInfo['metadata'] = $metadata;

                // Utiliser la date de création depuis les métadonnées comme source unique
                if (isset($metadata['started_at'])) {
                    $migrationInfo['created_at'] = Carbon::parse($metadata['started_at']);
                }

                // Utiliser le short_id des métadonnées s'il existe
                if (isset($metadata['short_id'])) {
                    $migrationInfo['short_id'] = $metadata['short_id'];
                }
            } else {
                // Migration sans métadonnées - ignorer
                continue;
            }

            $migrations[] = $migrationInfo;
        }

        // Trier par date de création décroissante
        usort($migrations, fn ($a, $b): int => Carbon::parse($b['created_at'])->timestamp - Carbon::parse($a['created_at'])->timestamp);

        return $migrations;
    }
}
