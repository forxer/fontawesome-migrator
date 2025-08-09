<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Metadata;

use Exception;
use FontAwesome\Migrator\Contracts\ConfigurationInterface;
use FontAwesome\Migrator\Support\JsonFileHelper;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;

/**
 * Service dédié au stockage et chargement des migrations
 * Responsabilité unique : persistance des données de migration
 */
class MigrationStorageService
{
    public function __construct(
        private readonly ConfigurationInterface $config
    ) {}

    /**
     * Sauvegarder les données de migration
     */
    public function saveToFile(array $migrationData, ?string $filePath = null): string
    {
        $finalPath = $filePath ?? $this->generateFilePath($migrationData);

        $this->ensureDirectoryExists(\dirname($finalPath));

        JsonFileHelper::saveJson($finalPath, $migrationData);

        return $finalPath;
    }

    /**
     * Charger les données depuis un fichier
     */
    public function loadFromFile(string $filePath): array
    {
        if (! File::exists($filePath)) {
            throw new InvalidArgumentException('Migration file not found: '.$filePath);
        }

        return JsonFileHelper::loadJson($filePath);
    }

    /**
     * Obtenir le répertoire de migration
     */
    public function getMigrationDirectory(): string
    {
        return base_path($this->config->getMigrationsPath());
    }

    /**
     * Lister toutes les migrations disponibles
     */
    public function getAvailableMigrations(): array
    {
        $directory = $this->getMigrationDirectory();

        if (! File::isDirectory($directory)) {
            return [];
        }

        $files = File::files($directory);
        $migrations = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'json') {
                try {
                    $data = $this->loadFromFile($file->getPathname());
                    $migrations[] = [
                        'file' => $file->getFilename(),
                        'path' => $file->getPathname(),
                        'migration_id' => $data['migration_id'] ?? 'unknown',
                        'started_at' => $data['started_at'] ?? null,
                        'status' => $data['status'] ?? 'unknown',
                    ];
                } catch (Exception) {
                    // Ignorer les fichiers corrompus
                    continue;
                }
            }
        }

        // Trier par date de création (plus récent en premier)
        usort($migrations, fn ($a, $b): int => ($b['started_at'] ?? '') <=> ($a['started_at'] ?? ''));

        return $migrations;
    }

    /**
     * Supprimer un fichier de migration
     */
    public function deleteMigration(string $filePath): bool
    {
        if (! File::exists($filePath)) {
            return false;
        }

        return File::delete($filePath);
    }

    /**
     * Nettoyer les anciennes migrations
     */
    public function cleanupOldMigrations(int $keepCount = 10): int
    {
        $migrations = $this->getAvailableMigrations();
        $toDelete = \array_slice($migrations, $keepCount);
        $deletedCount = 0;

        foreach ($toDelete as $migration) {
            if ($this->deleteMigration($migration['path'])) {
                $deletedCount++;
            }
        }

        return $deletedCount;
    }

    /**
     * Générer le chemin du fichier de migration
     */
    private function generateFilePath(array $migrationData): string
    {
        $migrationId = $migrationData['migration_id'] ?? uniqid('migration_');
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = \sprintf('migration_%s_%s.json', $timestamp, $migrationId);

        return $this->getMigrationDirectory().'/'.$filename;
    }

    /**
     * S'assurer que le répertoire existe
     */
    private function ensureDirectoryExists(string $directory): void
    {
        if (! File::isDirectory($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    /**
     * Obtenir les statistiques de stockage
     */
    public function getStorageStats(): array
    {
        $directory = $this->getMigrationDirectory();
        $migrations = $this->getAvailableMigrations();

        $totalSize = 0;

        if (File::isDirectory($directory)) {
            $files = File::files($directory);

            foreach ($files as $file) {
                $totalSize += $file->getSize();
            }
        }

        return [
            'total_migrations' => \count($migrations),
            'directory_size' => $totalSize,
            'directory_path' => $directory,
            'latest_migration' => $migrations[0] ?? null,
        ];
    }
}
