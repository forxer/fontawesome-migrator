<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Metadata;

use Carbon\Carbon;
use FontAwesome\Migrator\Contracts\ConfigurationInterface;

/**
 * Service dédié à la construction de structures de métadonnées
 * Extrait de MetadataManager pour respecter le Single Responsibility Principle
 */
class MetadataBuilder
{
    public function __construct(
        private readonly ConfigurationInterface $config
    ) {}

    /**
     * Construire la structure de métadonnées par défaut
     */
    public function buildDefaultMetadata(): array
    {
        return array_merge(
            $this->buildMigrationConfig(),
            $this->buildResultsDefaults(),
            $this->buildDetailedDataDefaults(),
            $this->buildBackupDefaults(),
            $this->buildEnvironmentData(),
            $this->buildScanConfiguration()
        );
    }

    /**
     * Configuration de migration de base
     */
    public function buildMigrationConfig(): array
    {
        return [
            'license_type' => $this->config->getLicenseType(),
            'icons_only' => false,
            'assets_only' => false,
            'custom_path' => null,
        ];
    }

    /**
     * Valeurs par défaut des résultats de migration
     */
    public function buildResultsDefaults(): array
    {
        return [
            'total_files' => 0,
            'modified_files' => 0,
            'total_changes' => 0,
            'warnings' => 0,
            'errors' => 0,
            'assets_migrated' => 0,
            'icons_migrated' => 0,
            'migration_success' => true,
        ];
    }

    /**
     * Structure par défaut des données détaillées
     */
    public function buildDetailedDataDefaults(): array
    {
        return [
            'files' => [],
            'warnings_details' => [],
            'changes_by_type' => [],
            'asset_types' => [],
        ];
    }

    /**
     * Configuration par défaut des backups
     */
    public function buildBackupDefaults(): array
    {
        return [
            'backup_files' => [],
            'backup_count' => 0,
            'backup_size' => 0,
        ];
    }

    /**
     * Données d'environnement d'exécution
     */
    public function buildEnvironmentData(): array
    {
        return [
            'environment' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'timezone' => Carbon::now()->timezoneName,
            ],
        ];
    }

    /**
     * Configuration de scan depuis ConfigurationInterface
     */
    public function buildScanConfiguration(): array
    {
        return [
            'scan_config' => [
                'paths' => $this->config->getScanPaths(),
                'extensions' => $this->config->getFileExtensions(),
                'backup_enabled' => $this->config->isBackupEnabled(),
                'migrations_path' => $this->config->getMigrationsPath(),
            ],
        ];
    }

    /**
     * Construire structure de résumé compacte pour affichage
     */
    public function buildSummaryStructure(array $metadata): array
    {
        return [
            'migration_id' => $metadata['migration_id'] ?? null,
            'started_at' => $metadata['started_at'] ?? null,
            'completed_at' => $metadata['completed_at'] ?? null,
            'duration' => $metadata['duration'] ?? null,
            'status' => $metadata['migration_success'] ? 'success' : 'failed',
            'stats' => [
                'total_files' => $metadata['total_files'] ?? 0,
                'modified_files' => $metadata['modified_files'] ?? 0,
                'total_changes' => $metadata['total_changes'] ?? 0,
                'warnings' => $metadata['warnings'] ?? 0,
                'errors' => $metadata['errors'] ?? 0,
            ],
            'version_info' => [
                'source_version' => $metadata['source_version'] ?? 'unknown',
                'target_version' => $metadata['target_version'] ?? 'unknown',
            ],
        ];
    }

    /**
     * Merger des données de migration dans les métadonnées existantes
     */
    public function mergeMigrationData(array $existingMetadata, array $migrationData): array
    {
        // Préserver les sections importantes lors de la fusion
        $preservedSections = [
            'environment',
            'scan_config',
            'files',
            'warnings_details',
            'changes_by_type',
            'asset_types',
        ];

        $merged = array_merge($existingMetadata, $migrationData);

        foreach ($preservedSections as $section) {
            if (isset($existingMetadata[$section]) && \is_array($existingMetadata[$section])) {
                $merged[$section] = array_merge(
                    $existingMetadata[$section],
                    $migrationData[$section] ?? []
                );
            }
        }

        return $merged;
    }
}
