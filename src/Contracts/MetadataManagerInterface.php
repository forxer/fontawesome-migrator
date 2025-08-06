<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Contracts;

interface MetadataManagerInterface
{
    /**
     * Initialiser les métadonnées de base
     */
    public function initialize(): self;

    /**
     * Définir les options de migration
     */
    public function setMigrationOptions(array $options): self;

    /**
     * Définir le mode dry-run
     */
    public function setDryRun(bool $isDryRun): self;

    /**
     * Marquer la fin de la migration
     */
    public function completeMigration(): self;

    /**
     * Stocker les résultats de migration
     */
    public function storeMigrationResults(array $results, array $stats, array $enrichedWarnings = []): self;

    /**
     * Obtenir le chemin du répertoire de migration
     */
    public function getMigrationDirectory(): string;

    /**
     * Sauvegarder les métadonnées dans un fichier
     */
    public function saveToFile(?string $filePath = null): string;

    /**
     * Obtenir toutes les métadonnées
     */
    public function getAll(): array;

    /**
     * Obtenir une section spécifique des métadonnées
     */
    public function get(string $section): mixed;

    /**
     * Ajouter des données personnalisées
     */
    public function addCustomData(string $key, mixed $value): self;

    /**
     * Ajouter une sauvegarde
     */
    public function addBackup(array $backupInfo): self;
}
