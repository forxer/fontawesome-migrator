<?php

namespace FontAwesome\Migrator\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;

class MetadataManager
{
    protected array $config;

    protected array $metadata = [];

    public function __construct()
    {
        $this->config = config('fontawesome-migrator');
    }

    /**
     * Initialiser les métadonnées de base
     */
    public function initialize(): self
    {
        $sessionId = uniqid('migration_', true);
        // Extraire les 8 premiers caractères après 'migration_' pour le short_id
        $shortId = substr($sessionId, strpos($sessionId, '_') + 1, 8);

        $this->metadata = [
            'session' => [
                'id' => $sessionId,
                'short_id' => $shortId,
                'started_at' => Carbon::now()->toIso8601String(),
                'package_version' => PackageVersionService::getVersion(),
            ],
            'environment' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'timezone' => Carbon::now()->timezoneName,
            ],
            'configuration' => [
                'license_type' => $this->config['license_type'],
                'scan_paths' => $this->config['scan_paths'] ?? [],
                'file_extensions' => $this->config['file_extensions'] ?? [],
                'backup_enabled' => $this->config['backup_files'] ?? true,
                'sessions_path' => $this->config['sessions_path'] ?? null,
            ],
            'migration_options' => [],
            'runtime' => [
                'dry_run' => false,
                'started_at' => null,
                'completed_at' => null,
                'duration' => null,
            ],
            'backups' => [
                'created' => [],
                'count' => 0,
                'total_size' => 0,
            ],
            'statistics' => [
                'files_scanned' => 0,
                'files_modified' => 0,
                'changes_made' => 0,
                'warnings_generated' => 0,
                'errors_encountered' => 0,
            ],
            'migration_results' => [
                'summary' => [
                    'total_files' => 0,
                    'modified_files' => 0,
                    'total_changes' => 0,
                    'changes_by_type' => [],
                    'warnings' => 0,
                    'errors' => 0,
                    'assets_migrated' => 0,
                    'icons_migrated' => 0,
                    'asset_types' => [],
                    'migration_success' => true,
                ],
                'files' => [],
                'enriched_warnings' => [],
                'generated_at' => null,
            ],
        ];

        return $this;
    }

    /**
     * Définir les options de migration
     */
    public function setMigrationOptions(array $options): self
    {
        $this->metadata['migration_options'] = $options;

        return $this;
    }

    /**
     * Définir le mode dry-run
     */
    public function setDryRun(bool $isDryRun): self
    {
        $this->metadata['runtime']['dry_run'] = $isDryRun;

        return $this;
    }

    /**
     * Marquer le début de la migration
     */
    public function startMigration(): self
    {
        $this->metadata['runtime']['started_at'] = Carbon::now()->toIso8601String();

        return $this;
    }

    /**
     * Marquer la fin de la migration
     */
    public function completeMigration(): self
    {
        $this->metadata['runtime']['completed_at'] = Carbon::now()->toIso8601String();

        if ($this->metadata['runtime']['started_at']) {
            $start = Carbon::parse($this->metadata['runtime']['started_at']);
            $end = Carbon::parse($this->metadata['runtime']['completed_at']);
            $this->metadata['runtime']['duration'] = $start->diffInSeconds($end);
        }

        return $this;
    }

    /**
     * Ajouter une sauvegarde
     */
    public function addBackup(array $backupInfo): self
    {
        $this->metadata['backups']['created'][] = $backupInfo;
        $this->metadata['backups']['count'] = \count($this->metadata['backups']['created']);
        $this->metadata['backups']['total_size'] += $backupInfo['size'] ?? 0;

        return $this;
    }

    /**
     * Mettre à jour les statistiques
     */
    public function updateStatistics(array $stats): self
    {
        $this->metadata['statistics'] = array_merge($this->metadata['statistics'], $stats);

        return $this;
    }

    /**
     * Stocker les résultats de migration
     */
    public function storeMigrationResults(array $results, array $stats, array $enrichedWarnings = []): self
    {
        $this->metadata['migration_results'] = [
            'summary' => $stats,
            'files' => array_map(fn ($result): array => [
                'file' => $result['file'],
                'success' => $result['success'] ?? true,
                'changes_count' => \count($result['changes'] ?? []),
                'warnings_count' => \count($result['warnings'] ?? []),
                'assets_count' => \count($result['assets'] ?? []),
                'changes' => $result['changes'] ?? [],
                'warnings' => $result['warnings'] ?? [],
                'assets' => $result['assets'] ?? [],
            ], $results),
            'enriched_warnings' => $enrichedWarnings,
            'generated_at' => Carbon::now()->toIso8601String(),
        ];

        return $this;
    }

    /**
     * Obtenir les résultats de migration
     */
    public function getMigrationResults(): array
    {
        return $this->metadata['migration_results'] ?? [
            'summary' => [],
            'files' => [],
            'enriched_warnings' => [],
        ];
    }

    /**
     * Ajouter des données personnalisées
     */
    public function addCustomData(string $key, mixed $value): self
    {
        $this->metadata['custom'][$key] = $value;

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
    public function getForReport(): array
    {
        return [
            'meta' => [
                'session_id' => $this->metadata['session']['id'],
                'generated_at' => $this->metadata['session']['started_at'],
                'package_version' => $this->metadata['session']['package_version'],
                'dry_run' => $this->metadata['runtime']['dry_run'],
                'duration' => $this->metadata['runtime']['duration'],
                'migration_options' => $this->metadata['migration_options'],
                'configuration' => $this->metadata['configuration'],
                'environment' => $this->metadata['environment'],
            ],
            'backups' => $this->metadata['backups'],
            'statistics' => $this->metadata['statistics'],
            'custom' => $this->metadata['custom'] ?? [],
        ];
    }

    /**
     * Sauvegarder les métadonnées dans le répertoire de session
     */
    public function saveToFile(?string $filePath = null): string
    {
        if ($filePath === null || $filePath === '' || $filePath === '0') {
            // Déterminer le répertoire de session pour les métadonnées
            $baseBackupDir = config('fontawesome-migrator.sessions_path');
            $sessionId = $this->metadata['session']['id'] ?? 'unknown';
            $sessionDir = $baseBackupDir.'/session-'.$sessionId;

            // S'assurer que le répertoire de session existe avec .gitignore
            $this->ensureSessionDirectoryExists($sessionDir);

            $filePath = $sessionDir.'/metadata.json';
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

        // Vérifier les sections obligatoires
        $requiredSections = ['session', 'configuration', 'runtime'];

        foreach ($requiredSections as $section) {
            if (! isset($this->metadata[$section])) {
                $errors[] = 'Section manquante: '.$section;
            }
        }

        // Vérifier les champs obligatoires
        if (isset($this->metadata['session'])) {
            $requiredFields = ['id', 'started_at', 'package_version'];

            foreach ($requiredFields as $field) {
                if (! isset($this->metadata['session'][$field])) {
                    $errors[] = 'Champ manquant dans session: '.$field;
                }
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
            'session_id' => $this->metadata['session']['id'] ?? null,
            'version' => $this->metadata['session']['package_version'] ?? null,
            'dry_run' => $this->metadata['runtime']['dry_run'] ?? false,
            'backups_count' => $this->metadata['backups']['count'] ?? 0,
            'files_modified' => $this->metadata['statistics']['files_modified'] ?? 0,
            'changes_made' => $this->metadata['statistics']['changes_made'] ?? 0,
            'duration' => $this->metadata['runtime']['duration'] ?? null,
        ];
    }

    /**
     * S'assurer que le répertoire de session existe avec .gitignore
     */
    protected function ensureSessionDirectoryExists(string $sessionDir): void
    {
        if (! File::exists($sessionDir)) {
            File::makeDirectory($sessionDir, 0755, true);
        }

        $gitignorePath = $sessionDir.'/.gitignore';

        if (! File::exists($gitignorePath)) {
            $gitignoreContent = "# FontAwesome Migrator - Session Backups\n*\n!.gitignore\n!metadata.json\n";
            File::put($gitignorePath, $gitignoreContent);
        }
    }

    /**
     * Obtenir le chemin du répertoire de session
     */
    public function getSessionDirectory(): string
    {
        $baseBackupDir = config('fontawesome-migrator.sessions_path');
        $sessionId = $this->metadata['session']['id'] ?? 'unknown';

        return $baseBackupDir.'/session-'.$sessionId;
    }

    /**
     * Nettoyer les anciens répertoires de session
     */
    public static function cleanOldSessions(int $daysToKeep = 30): int
    {
        $baseBackupDir = config('fontawesome-migrator.sessions_path');

        if (! File::exists($baseBackupDir)) {
            return 0;
        }

        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
        $deleted = 0;

        $directories = File::directories($baseBackupDir);

        foreach ($directories as $directory) {
            // Vérifier si c'est un répertoire de session
            if (\in_array(preg_match('/\/session-/', (string) $directory), [0, false], true)) {
                continue;
            }

            // Vérifier la date de modification du répertoire
            if (filemtime($directory) >= $cutoffTime) {
                continue;
            }

            // Supprimer le répertoire de session complet
            if (File::deleteDirectory($directory)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Lister tous les répertoires de session disponibles
     */
    public static function getAvailableSessions(): array
    {
        $baseBackupDir = config('fontawesome-migrator.sessions_path');
        $sessions = [];

        if (! File::exists($baseBackupDir)) {
            return $sessions;
        }

        $directories = File::directories($baseBackupDir);

        foreach ($directories as $directory) {
            if (\in_array(preg_match('/\/session-(.+)$/', (string) $directory, $matches), [0, false], true)) {
                continue;
            }

            $sessionId = $matches[1];
            $metadataPath = $directory.'/metadata.json';

            // Calculer le short_id à partir du session_id
            $shortId = substr($sessionId, strpos($sessionId, '_') + 1, 8);

            $sessionInfo = [
                'session_id' => $sessionId,
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
            if ($sessionInfo['has_metadata']) {
                $metadata = json_decode(File::get($metadataPath), true);
                $sessionInfo['package_version'] = $metadata['session']['package_version'] ?? 'unknown';
                $sessionInfo['dry_run'] = $metadata['runtime']['dry_run'] ?? false;
                $sessionInfo['duration'] = $metadata['runtime']['duration'] ?? null;

                // Inclure les métadonnées complètes
                $sessionInfo['metadata'] = $metadata;

                // Utiliser la date de création depuis les métadonnées comme source unique
                if (isset($metadata['session']['started_at'])) {
                    $sessionInfo['created_at'] = Carbon::parse($metadata['session']['started_at']);
                }

                // Utiliser le short_id des métadonnées s'il existe
                if (isset($metadata['session']['short_id'])) {
                    $sessionInfo['short_id'] = $metadata['session']['short_id'];
                }
            } else {
                // Session sans métadonnées - ignorer
                continue;
            }

            $sessions[] = $sessionInfo;
        }

        // Trier par date de création décroissante
        usort($sessions, fn ($a, $b): int => Carbon::parse($b['created_at'])->timestamp - Carbon::parse($a['created_at'])->timestamp);

        return $sessions;
    }
}
