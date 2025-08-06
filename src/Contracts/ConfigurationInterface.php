<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Contracts;

interface ConfigurationInterface
{
    /**
     * Obtenir une valeur de configuration spécifique
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Obtenir la licence (free/pro)
     */
    public function getLicenseType(): string;

    /**
     * Vérifier si c'est une licence Pro
     */
    public function isProLicense(): bool;

    /**
     * Obtenir les chemins de scan
     */
    public function getScanPaths(): array;

    /**
     * Obtenir les extensions de fichiers autorisées
     */
    public function getFileExtensions(): array;

    /**
     * Obtenir les patterns d'exclusion
     */
    public function getExcludePatterns(): array;

    /**
     * Vérifier si les sauvegardes sont activées
     */
    public function isBackupEnabled(): bool;

    /**
     * Obtenir le chemin des migrations
     */
    public function getMigrationsPath(): string;

    /**
     * Valider la configuration
     */
    public function validate(): array;
}
