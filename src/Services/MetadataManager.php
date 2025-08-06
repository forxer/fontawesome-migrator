<?php

namespace FontAwesome\Migrator\Services;

use Carbon\Carbon;
use FontAwesome\Migrator\Contracts\ConfigurationInterface;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use FontAwesome\Migrator\Support\FormatterHelper;
use Illuminate\Support\Facades\File;

class MetadataManager implements MetadataManagerInterface
{
    protected array $metadata = [];

    public function __construct(
        protected ConfigurationInterface $config
    ) {}

    /**
     * Initialiser les métadonnées de base
     */
    public function initialize(): self
    {
        $migrationId = uniqid('migration_', true);
        $shortId = FormatterHelper::generateShortId('migration_');

        $this->metadata = [
            // === IDENTIFICATION ===
            'session_id' => $migrationId,
            'short_id' => $shortId,
            'package_version' => PackageVersionService::getVersion(),

            // === EXECUTION ===
            'started_at' => Carbon::now()->toIso8601String(),
            'completed_at' => null,
            'duration' => null,
            'dry_run' => false,
            'migration_source' => 'command_line',

            // === MIGRATION CONFIG ===
            'source_version' => null,
            'target_version' => null,
            'license_type' => $this->config->getLicenseType(),
            'icons_only' => false,
            'assets_only' => false,
            'custom_path' => null,

            // === RESULTS (direct access) ===
            'total_files' => 0,
            'modified_files' => 0,
            'total_changes' => 0,
            'warnings' => 0,
            'errors' => 0,
            'assets_migrated' => 0,
            'icons_migrated' => 0,
            'migration_success' => true,

            // === DETAILED DATA ===
            'files' => [],
            'warnings_details' => [],
            'changes_by_type' => [],
            'asset_types' => [],

            // === BACKUPS ===
            'backup_files' => [],
            'backup_count' => 0,
            'backup_size' => 0,

            // === ENVIRONMENT (groupé) ===
            'environment' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'timezone' => Carbon::now()->timezoneName,
            ],

            // === CONFIGURATION (groupé) ===
            'scan_config' => [
                'paths' => $this->config->getScanPaths(),
                'extensions' => $this->config->getFileExtensions(),
                'backup_enabled' => $this->config->isBackupEnabled(),
                'migrations_path' => $this->config->getMigrationsPath(),
            ],

            // === COMMAND TRACE (groupé) ===
            'command_options' => [],
        ];

        return $this;
    }

    /**
     * Définir les options de migration
     */
    public function setMigrationOptions(array $options): self
    {
        // Mapper vers la nouvelle structure simplifiée
        $this->metadata['source_version'] = $options['source_version'] ?? null;
        $this->metadata['target_version'] = $options['target_version'] ?? null;
        $this->metadata['migration_source'] = $options['migration_source'] ?? 'command_line';

        return $this;
    }

    /**
     * Définir le mode dry-run
     */
    public function setDryRun(bool $isDryRun): self
    {
        $this->metadata['dry_run'] = $isDryRun;

        return $this;
    }

    /**
     * Marquer la fin de la migration
     */
    public function completeMigration(): self
    {
        $this->metadata['completed_at'] = Carbon::now()->toIso8601String();

        if ($this->metadata['started_at']) {
            $start = Carbon::parse($this->metadata['started_at']);
            $end = Carbon::parse($this->metadata['completed_at']);
            $this->metadata['duration'] = $start->diffInSeconds($end);
        }

        return $this;
    }

    /**
     * Ajouter une sauvegarde
     */
    public function addBackup(array $backupInfo): self
    {
        $this->metadata['backup_files'][] = $backupInfo;
        $this->metadata['backup_count'] = \count($this->metadata['backup_files']);
        $this->metadata['backup_size'] += $backupInfo['size'] ?? 0;

        return $this;
    }

    /**
     * Stocker les résultats de migration
     */
    public function storeMigrationResults(array $results, array $stats, array $enrichedWarnings = []): self
    {
        // === RESULTS (direct access) ===
        $this->metadata['total_files'] = $stats['total_files'] ?? 0;
        $this->metadata['modified_files'] = $stats['modified_files'] ?? 0;
        $this->metadata['total_changes'] = $stats['total_changes'] ?? 0;
        $this->metadata['warnings'] = $stats['warnings'] ?? 0;
        $this->metadata['errors'] = $stats['errors'] ?? 0;
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
                'session_id' => $this->metadata['session_id'],
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

        $directory = \dirname($filePath);

        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        File::put($filePath, json_encode($this->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $filePath;
    }

    /**
     * Charger les métadonnées depuis un fichier JSON
     */
    public function loadFromFile(string $filePath): self
    {
        if (File::exists($filePath)) {
            $content = File::get($filePath);
            $this->metadata = json_decode($content, true) ?? [];
        }

        return $this;
    }

    /**
     * Valider la structure des métadonnées
     */
    public function validate(): array
    {
        $errors = [];

        // Vérifier les champs obligatoires de la nouvelle structure
        $requiredFields = ['session_id', 'started_at', 'package_version'];

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
            'session_id' => $this->metadata['session_id'] ?? null,
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
            $gitignoreContent = "# FontAwesome Migrator - Session Backups\n*\n!.gitignore\n!metadata.json\n";
            File::put($gitignorePath, $gitignoreContent);
        }
    }

    /**
     * Obtenir le chemin du répertoire de migration
     */
    public function getMigrationDirectory(): string
    {
        $migrationId = $this->metadata['session_id'] ?? 'unknown';

        return $this->config->getMigrationsPath().'/migration-'.$migrationId;
    }

    /**
     * Nettoyer les anciens répertoires de migration
     */
    public static function cleanOldSessions(int $daysToKeep = 30): int
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
    public static function getAvailableMigrations(): array
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

            // Calculer le short_id à partir du session_id
            $shortId = FormatterHelper::generateShortId('migration_');

            $migrationInfo = [
                'session_id' => $migrationId,
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
                $metadata = json_decode(File::get($metadataPath), true);

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
                // Session sans métadonnées - ignorer
                continue;
            }

            $migrations[] = $migrationInfo;
        }

        // Trier par date de création décroissante
        usort($migrations, fn ($a, $b): int => Carbon::parse($b['created_at'])->timestamp - Carbon::parse($a['created_at'])->timestamp);

        return $migrations;
    }
}
