<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Reports;

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
    public function __construct(
        protected MetadataManagerInterface $metadataManager
    ) {}

    /**
     * Créer une sauvegarde d'un fichier dans le répertoire de session
     */
    public function createBackup(string $filePath): array|bool
    {
        $sessionDirectory = $this->metadataManager->getMigrationDirectory();
        $backupDir = $sessionDirectory.'/backups';

        // S'assurer que le répertoire et le .gitignore existent
        DirectoryHelper::ensureExistsWithGitignore($backupDir);

        $relativePath = str_replace(base_path().'/', '', $filePath);
        $timestamp = date('Y-m-d_H-i-s');
        $backupPath = $backupDir.'/'.$relativePath.'.backup.'.$timestamp;

        // Créer les dossiers nécessaires avec DirectoryHelper
        $backupDirectory = \dirname($backupPath);
        DirectoryHelper::ensureExists($backupDirectory);

        $success = File::copy($filePath, $backupPath);

        if ($success) {
            return [
                'original_file' => $filePath,
                'relative_path' => $relativePath,
                'backup_path' => $backupPath,
                'timestamp' => $timestamp,
                'created_at' => date('Y-m-d H:i:s'),
                'size' => File::size($filePath),
            ];
        }

        return false;
    }

    /**
     * Obtenir la liste des sauvegardes pour la session courante
     */
    public function getSessionBackups(): array
    {
        $sessionDirectory = $this->metadataManager->getMigrationDirectory();
        $backupDir = $sessionDirectory.'/backups';

        if (! File::isDirectory($backupDir)) {
            return [];
        }

        $backups = [];
        $files = File::allFiles($backupDir);

        foreach ($files as $file) {
            if (str_contains($file->getFilename(), '.backup.')) {
                $backups[] = [
                    'path' => $file->getRealPath(),
                    'filename' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                    'relative_path' => str_replace($backupDir.'/', '', $file->getRealPath()),
                ];
            }
        }

        return $backups;
    }

    /**
     * Supprimer toutes les sauvegardes d'une session
     */
    public function clearSessionBackups(): bool
    {
        $sessionDirectory = $this->metadataManager->getMigrationDirectory();
        $backupDir = $sessionDirectory.'/backups';

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
     * Obtenir les statistiques des sauvegardes pour la session courante
     */
    public function getBackupStats(): array
    {
        $backups = $this->getSessionBackups();

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
}
