<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Metadata;

/**
 * Service dédié à la gestion des résultats de migration
 * Responsabilité unique : collecte et traitement des résultats
 */
class MigrationResultsService
{
    private array $results = [];

    private array $backups = [];

    private array $customData = [];

    /**
     * Stocker les résultats de migration
     */
    public function storeResults(array $results, array $stats, array $warnings = []): self
    {
        $this->results = [
            'total_files' => \count($results),
            'modified_files' => \count(array_filter($results, fn ($r): bool => $r['success'] && $r['changes_count'] > 0)),
            'errors' => array_filter(array_column($results, 'error')),
            'warnings' => $warnings,
            'stats' => $stats,
            'details' => $results,
        ];

        return $this;
    }

    /**
     * Ajouter une sauvegarde
     */
    public function addBackup(array $backupInfo): self
    {
        $this->backups[] = [
            'file' => $backupInfo['original_file'],
            'backup_path' => $backupInfo['backup_path'],
            'created_at' => $backupInfo['created_at'] ?? now()->toDateTimeString(),
            'size' => $backupInfo['size'] ?? 0,
        ];

        return $this;
    }

    /**
     * Ajouter des données personnalisées
     */
    public function addCustomData(string $key, mixed $value): self
    {
        $this->customData[$key] = $value;

        return $this;
    }

    /**
     * Obtenir tous les résultats
     */
    public function getResults(): array
    {
        return $this->results;
    }

    /**
     * Obtenir les sauvegardes
     */
    public function getBackups(): array
    {
        return $this->backups;
    }

    /**
     * Obtenir les données personnalisées
     */
    public function getCustomData(): array
    {
        return $this->customData;
    }

    /**
     * Obtenir un résumé des résultats
     */
    public function getResultsSummary(): array
    {
        return [
            'total_files' => $this->results['total_files'] ?? 0,
            'modified_files' => $this->results['modified_files'] ?? 0,
            'error_count' => \count($this->results['errors'] ?? []),
            'warning_count' => \count($this->results['warnings'] ?? []),
            'backup_count' => \count($this->backups),
        ];
    }

    /**
     * Obtenir toutes les données pour export
     */
    public function getAllData(): array
    {
        return [
            'migration_results' => $this->results,
            'backups' => $this->backups,
            'custom_data' => $this->customData,
        ];
    }

    /**
     * Réinitialiser tous les résultats
     */
    public function reset(): self
    {
        $this->results = [];
        $this->backups = [];
        $this->customData = [];

        return $this;
    }

    /**
     * Valider les résultats
     */
    public function validateResults(): array
    {
        $errors = [];

        if ($this->results === []) {
            $errors[] = 'No migration results stored';
        }

        if (isset($this->results['total_files']) && $this->results['total_files'] < 0) {
            $errors[] = 'Invalid total_files count';
        }

        return $errors;
    }
}
