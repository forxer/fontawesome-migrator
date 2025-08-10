<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Http\Controllers\Tests;

use FontAwesome\Migrator\Contracts\ConfigurationInterface;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use FontAwesome\Migrator\Services\Core\MigrationVersionManager;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class IndexController extends Controller
{
    public function __construct(
        private readonly MigrationVersionManager $versionManager
    ) {}

    /**
     * Afficher la page d'index des tests
     */
    public function __invoke(MetadataManagerInterface $metadataManager, ConfigurationInterface $config)
    {
        $migrations = $metadataManager->getAvailableMigrations();
        $backupStats = $this->getBackupStats($config);

        // Séparer les migrations par origine (web vs CLI)
        $webMigrations = [];
        $cliMigrations = [];

        foreach ($migrations as $migration) {
            $metadata = $migration['metadata'] ?? null;

            if (! $metadata) {
                continue;
            }

            $migrationSource = $metadata['migration_source'] ?? 'command_line';

            if ($migrationSource === 'web_interface') {
                $webMigrations[] = $migration;
            } else {
                $cliMigrations[] = $migration;
            }
        }

        // Ajuster les statistiques pour ne refléter que les migrations web (pour tests)
        $backupStats['total_migrations'] = \count($webMigrations);

        // Ajouter les informations de migration multi-versions
        $supportedMigrations = $this->versionManager->getSupportedMigrations();

        return view('fontawesome-migrator::tests.index', [
            'migrations' => $webMigrations, // Afficher seulement les migrations web (tests)
            'backupStats' => $backupStats,
            'supportedMigrations' => $supportedMigrations,
        ]);
    }

    /**
     * Obtenir les statistiques des sauvegardes
     */
    private function getBackupStats(ConfigurationInterface $config): array
    {
        $backupDir = $config->getMigrationsPath();
        $totalMigrations = 0;
        $totalBackups = 0;
        $totalSize = 0;
        $lastMigration = null;
        $timestamp = now()->toDateTimeString();

        if (File::exists($backupDir)) {
            $directories = File::directories($backupDir);
            $totalMigrations = \count($directories);

            // Compter tous les fichiers de sauvegarde et leur taille
            foreach ($directories as $directory) {
                $files = File::files($directory);
                // -1 pour exclure metadata.json du compteur
                $totalBackups += max(0, \count($files) - 1);

                // Calculer la taille totale de tous les fichiers
                foreach ($files as $file) {
                    $totalSize += $file->getSize();
                }

                // Déterminer la dernière migration (comme objet Carbon)
                $directoryTime = filemtime($directory);

                if ($lastMigration === null || $directoryTime > $lastMigration->timestamp) {
                    $lastMigration = now()->createFromTimestamp($directoryTime);
                }
            }
        }

        return [
            'total_migrations' => $totalMigrations,
            'total_backups' => $totalBackups,
            'total_size' => $totalSize,
            'last_migration' => $lastMigration,
            'timestamp' => $timestamp,
            'backup_directory' => $backupDir,
        ];
    }
}
