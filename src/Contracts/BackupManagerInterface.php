<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Contracts;

interface BackupManagerInterface
{
    /**
     * Créer une sauvegarde d'un fichier
     */
    public function createBackup(string $filePath): array|bool;

    /**
     * Obtenir la liste des sauvegardes pour la session courante
     */
    public function getSessionBackups(): array;

    /**
     * Supprimer toutes les sauvegardes d'une session
     */
    public function clearSessionBackups(): bool;

    /**
     * Obtenir les statistiques des sauvegardes
     */
    public function getBackupStats(): array;
}
