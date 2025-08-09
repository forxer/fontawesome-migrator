<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Metadata;

use FontAwesome\Migrator\Services\Configuration\PackageVersionService;
use FontAwesome\Migrator\Support\FormatterHelper;

/**
 * Service dédié à la gestion des du cycle de de migration
 * Responsabilité unique : gestion du cycle de vie des migrations
 */
class MigrationLifecyleService
{
    private array $migrationData = [];

    public function __construct(
        private readonly PackageVersionService $packageVersionService
    ) {}

    /**
     * Initialiser une nouvelle migration de migration
     */
    public function initializeMigration(): string
    {
        $migrationId = uniqid('migration_', true);
        $shortId = FormatterHelper::generateShortId('migration_');

        $this->migrationData = [
            'migration_id' => $migrationId,
            'short_id' => $shortId,
            'package_version' => $this->packageVersionService->getVersion(),
            'started_at' => now()->toDateTimeString(),
            'status' => 'initialized',
            'dry_run' => false,
            'migration_options' => [],
            'command_options' => [],
        ];

        return $migrationId;
    }

    /**
     * Marquer la migration comme complétée
     */
    public function completeMigration(): self
    {
        $this->migrationData['status'] = 'completed';
        $this->migrationData['completed_at'] = now()->toDateTimeString();
        $this->migrationData['duration'] = $this->calculateDuration();

        return $this;
    }

    /**
     * Configurer les options de migration
     */
    public function setMigrationOptions(array $options): self
    {
        $this->migrationData['migration_options'] = $options;

        return $this;
    }

    /**
     * Configurer le mode dry run
     */
    public function setDryRun(bool $isDryRun): self
    {
        $this->migrationData['dry_run'] = $isDryRun;

        return $this;
    }

    /**
     * Obtenir l'ID de migration
     */
    public function getmigrationId(): string
    {
        return $this->migrationData['migration_id'] ?? '';
    }

    /**
     * Obtenir les données de migration
     */
    public function getMigrationData(): array
    {
        return $this->migrationData;
    }

    /**
     * Charger une migration depuis les données
     */
    public function loadMigration(array $migrationData): self
    {
        $this->migrationData = $migrationData;

        return $this;
    }

    /**
     * Réinitialiser la migration
     */
    public function resetMigration(): self
    {
        $this->migrationData = [];

        return $this;
    }

    /**
     * Calculer la durée de la migration
     */
    private function calculateDuration(): ?int
    {
        if (empty($this->migrationData['started_at']) || empty($this->migrationData['completed_at'])) {
            return null;
        }

        $startTime = strtotime((string) $this->migrationData['started_at']);
        $endTime = strtotime((string) $this->migrationData['completed_at']);

        return $endTime - $startTime;
    }

    /**
     * Obtenir un résumé de la migration
     */
    public function getMigrationSummary(): array
    {
        return [
            'migration_id' => $this->migrationData['migration_id'] ?? null,
            'short_id' => $this->migrationData['short_id'] ?? null,
            'status' => $this->migrationData['status'] ?? 'unknown',
            'started_at' => $this->migrationData['started_at'] ?? null,
            'completed_at' => $this->migrationData['completed_at'] ?? null,
            'duration' => $this->migrationData['duration'] ?? null,
            'dry_run' => $this->migrationData['dry_run'] ?? false,
        ];
    }

    /**
     * Valider les données de migration
     */
    public function validateMigration(): array
    {
        $errors = [];

        if (empty($this->migrationData['migration_id'])) {
            $errors[] = 'Migration ID is required';
        }

        if (empty($this->migrationData['started_at'])) {
            $errors[] = 'Start time is required';
        }

        return $errors;
    }
}
