<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Metadata;

use FontAwesome\Migrator\Services\Configuration\PackageVersionService;
use FontAwesome\Migrator\Support\FormatterHelper;

/**
 * Service dédié à la gestion des sessions de migration
 * Responsabilité unique : gestion du cycle de vie des sessions
 */
class MigrationSessionService
{
    private array $sessionData = [];

    public function __construct(
        private readonly PackageVersionService $packageVersionService
    ) {}

    /**
     * Initialiser une nouvelle session de migration
     */
    public function initializeSession(): string
    {
        $sessionId = uniqid('migration_', true);
        $shortId = FormatterHelper::generateShortId('migration_');

        $this->sessionData = [
            'session_id' => $sessionId,
            'short_id' => $shortId,
            'package_version' => $this->packageVersionService->getVersion(),
            'started_at' => now()->toISOString(),
            'status' => 'initialized',
            'dry_run' => false,
            'migration_options' => [],
            'command_options' => [],
        ];

        return $sessionId;
    }

    /**
     * Marquer la session comme complétée
     */
    public function completeSession(): self
    {
        $this->sessionData['status'] = 'completed';
        $this->sessionData['completed_at'] = now()->toISOString();
        $this->sessionData['duration'] = $this->calculateDuration();

        return $this;
    }

    /**
     * Configurer les options de migration
     */
    public function setMigrationOptions(array $options): self
    {
        $this->sessionData['migration_options'] = $options;

        return $this;
    }

    /**
     * Configurer le mode dry run
     */
    public function setDryRun(bool $isDryRun): self
    {
        $this->sessionData['dry_run'] = $isDryRun;

        return $this;
    }

    /**
     * Obtenir l'ID de session
     */
    public function getSessionId(): string
    {
        return $this->sessionData['session_id'] ?? '';
    }

    /**
     * Obtenir les données de session
     */
    public function getSessionData(): array
    {
        return $this->sessionData;
    }

    /**
     * Charger une session depuis les données
     */
    public function loadSession(array $sessionData): self
    {
        $this->sessionData = $sessionData;

        return $this;
    }

    /**
     * Réinitialiser la session
     */
    public function resetSession(): self
    {
        $this->sessionData = [];

        return $this;
    }

    /**
     * Calculer la durée de la migration
     */
    private function calculateDuration(): ?int
    {
        if (empty($this->sessionData['started_at']) || empty($this->sessionData['completed_at'])) {
            return null;
        }

        $startTime = strtotime((string) $this->sessionData['started_at']);
        $endTime = strtotime((string) $this->sessionData['completed_at']);

        return $endTime - $startTime;
    }

    /**
     * Obtenir un résumé de la session
     */
    public function getSessionSummary(): array
    {
        return [
            'session_id' => $this->sessionData['session_id'] ?? null,
            'short_id' => $this->sessionData['short_id'] ?? null,
            'status' => $this->sessionData['status'] ?? 'unknown',
            'started_at' => $this->sessionData['started_at'] ?? null,
            'completed_at' => $this->sessionData['completed_at'] ?? null,
            'duration' => $this->sessionData['duration'] ?? null,
            'dry_run' => $this->sessionData['dry_run'] ?? false,
        ];
    }

    /**
     * Valider les données de session
     */
    public function validateSession(): array
    {
        $errors = [];

        if (empty($this->sessionData['session_id'])) {
            $errors[] = 'Session ID is required';
        }

        if (empty($this->sessionData['started_at'])) {
            $errors[] = 'Start time is required';
        }

        return $errors;
    }
}
