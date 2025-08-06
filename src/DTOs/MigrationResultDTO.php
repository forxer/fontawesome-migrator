<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\DTOs;

/**
 * DTO pour les résultats de migration
 * Simplifie les structures de données complexes
 */
readonly class MigrationResultDTO
{
    public function __construct(
        public string $file,
        public string $relativePath,
        public bool $success,
        public int $changesCount,
        public ?string $error = null,
        public array $changes = [],
        public array $warnings = []
    ) {}

    /**
     * Créer depuis un array de données
     */
    public static function fromArray(array $data): self
    {
        return new self(
            file: $data['file'] ?? '',
            relativePath: $data['relative_path'] ?? '',
            success: $data['success'] ?? false,
            changesCount: $data['changes_count'] ?? 0,
            error: $data['error'] ?? null,
            changes: $data['changes'] ?? [],
            warnings: $data['warnings'] ?? []
        );
    }

    /**
     * Convertir en array
     */
    public function toArray(): array
    {
        return [
            'file' => $this->file,
            'relative_path' => $this->relativePath,
            'success' => $this->success,
            'changes_count' => $this->changesCount,
            'error' => $this->error,
            'changes' => $this->changes,
            'warnings' => $this->warnings,
        ];
    }

    /**
     * Vérifier si la migration a réussi avec des changements
     */
    public function hasChanges(): bool
    {
        return $this->success && $this->changesCount > 0;
    }

    /**
     * Vérifier s'il y a des erreurs
     */
    public function hasError(): bool
    {
        return $this->error !== null && $this->error !== '' && $this->error !== '0';
    }

    /**
     * Vérifier s'il y a des avertissements
     */
    public function hasWarnings(): bool
    {
        return $this->warnings !== [];
    }
}
