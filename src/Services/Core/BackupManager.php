<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Core;

use Exception;
use FontAwesome\Migrator\Contracts\BackupManagerInterface;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use FontAwesome\Migrator\Support\DirectoryHelper;
use Illuminate\Support\Facades\File;

/**
 * Service de gestion des sauvegardes de fichiers
 */
class BackupManager implements BackupManagerInterface
{
    private int $backupCount = 0;

    public function __construct(
        protected MetadataManagerInterface $metadataManager
    ) {}

    /**
     * Créer une sauvegarde d'un fichier dans le répertoire de migration
     */
    public function createBackup(string $filePath): array|bool
    {
        $migrationDirectory = $this->metadataManager->getMigrationDirectory();
        $backupDir = $migrationDirectory.'/backups';

        // S'assurer que le répertoire et le .gitignore existent
        DirectoryHelper::ensureExists($backupDir);

        $relativePath = str_replace(base_path().'/', '', $filePath);
        $backupPath = $backupDir.'/'.$relativePath.'.backup';

        // Créer les dossiers nécessaires avec DirectoryHelper
        $backupDirectory = \dirname($backupPath);
        DirectoryHelper::ensureExists($backupDirectory);

        $success = File::copy($filePath, $backupPath);

        if ($success) {
            $this->backupCount++;

            // Convertir le backup_path en chemin relatif depuis le répertoire de migration
            $relativeBackupPath = str_replace($this->metadataManager->getMigrationDirectory().'/', '', $backupPath);

            return [
                'original_path' => $relativePath,
                'backup_path' => $relativeBackupPath,
                'created_at' => now()->toDateTimeString(),
                'size' => File::size($filePath),
            ];
        }

        return false;
    }

    /**
     * Obtenir la liste des sauvegardes pour la migration courante
     */
    public function getMigrationBackups(): array
    {
        $migrationDirectory = $this->metadataManager->getMigrationDirectory();
        $backupDir = $migrationDirectory.'/backups';

        if (! File::isDirectory($backupDir)) {
            return [];
        }

        $backups = [];
        $files = File::allFiles($backupDir);

        foreach ($files as $file) {
            if ($file->isFile() && str_ends_with($file->getFilename(), '.backup')) {
                $backups[] = [
                    'path' => $file->getRealPath(),
                    'filename' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'modified' => now()->createFromTimestamp($file->getMTime())->toDateTimeString(),
                    'relative_path' => str_replace($backupDir.'/', '', $file->getRealPath()),
                ];
            }
        }

        return $backups;
    }

    /**
     * Supprimer toutes les sauvegardes d'une migration
     */
    public function clearMigrationBackups(): bool
    {
        $migrationDirectory = $this->metadataManager->getMigrationDirectory();
        $backupDir = $migrationDirectory.'/backups';

        if (! File::isDirectory($backupDir)) {
            return true;
        }

        try {
            File::deleteDirectory($backupDir);

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Obtenir les statistiques des sauvegardes pour la migration courante
     */
    public function getBackupStats(): array
    {
        $backups = $this->getMigrationBackups();

        $totalSize = array_sum(array_column($backups, 'size'));
        $count = \count($backups);

        return [
            'count' => $count,
            'total_size' => $totalSize,
            'total_size_human' => human_readable_bytes_size($totalSize),
            'oldest' => $count > 0 ? min(array_column($backups, 'modified')) : null,
            'newest' => $count > 0 ? max(array_column($backups, 'modified')) : null,
        ];
    }

    /**
     * Obtenir le nombre de backups créés pendant la migration courante
     */
    public function getBackupCount(): int
    {
        return $this->backupCount;
    }

    /**
     * Réinitialiser le compteur de backups (au début d'une nouvelle migration)
     */
    public function resetBackupCount(): void
    {
        $this->backupCount = 0;
    }
}
