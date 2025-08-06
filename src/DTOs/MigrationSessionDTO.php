<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\DTOs;

/**
 * DTO pour les sessions de migration
 * Simplifie la structure des métadonnées de session
 */
readonly class MigrationSessionDTO
{
    public function __construct(
        public string $sessionId,
        public string $shortId,
        public string $status,
        public string $startedAt,
        public ?string $completedAt = null,
        public ?int $duration = null,
        public bool $isDryRun = false,
        public array $migrationOptions = [],
        public array $commandOptions = []
    ) {}

    /**
     * Créer depuis un array de données
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sessionId: $data['session_id'] ?? '',
            shortId: $data['short_id'] ?? '',
            status: $data['status'] ?? 'unknown',
            startedAt: $data['started_at'] ?? '',
            completedAt: $data['completed_at'] ?? null,
            duration: $data['duration'] ?? null,
            isDryRun: $data['dry_run'] ?? false,
            migrationOptions: $data['migration_options'] ?? [],
            commandOptions: $data['command_options'] ?? []
        );
    }

    /**
     * Convertir en array
     */
    public function toArray(): array
    {
        return [
            'session_id' => $this->sessionId,
            'short_id' => $this->shortId,
            'status' => $this->status,
            'started_at' => $this->startedAt,
            'completed_at' => $this->completedAt,
            'duration' => $this->duration,
            'dry_run' => $this->isDryRun,
            'migration_options' => $this->migrationOptions,
            'command_options' => $this->commandOptions,
        ];
    }

    /**
     * Vérifier si la session est complétée
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Vérifier si c'est un dry run
     */
    public function isDryRunMode(): bool
    {
        return $this->isDryRun;
    }

    /**
     * Obtenir la durée formatée
     */
    public function getFormattedDuration(): string
    {
        if ($this->duration === null || $this->duration === 0) {
            return 'N/A';
        }

        $minutes = \intval($this->duration / 60);
        $seconds = $this->duration % 60;

        if ($minutes > 0) {
            return \sprintf('%dm %ds', $minutes, $seconds);
        }

        return $seconds.'s';
    }

    /**
     * Obtenir la version source de migration
     */
    public function getSourceVersion(): ?string
    {
        return $this->migrationOptions['source_version'] ?? null;
    }

    /**
     * Obtenir la version cible de migration
     */
    public function getTargetVersion(): ?string
    {
        return $this->migrationOptions['target_version'] ?? null;
    }
}
