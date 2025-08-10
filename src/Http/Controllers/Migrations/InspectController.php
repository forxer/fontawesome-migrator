<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Http\Controllers\Migrations;

use FontAwesome\Migrator\Contracts\ConfigurationInterface;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class InspectController extends Controller
{
    /**
     * Inspecter une migration spécifique
     */
    public function __invoke(string $migrationId, ConfigurationInterface $config, MetadataManagerInterface $metadataManager)
    {
        // Récupérer toutes les migrations disponibles
        $migrations = $metadataManager->getAvailableMigrations();

        // Chercher la migration par short_id ou migration_id complet
        $migrationInfo = null;

        foreach ($migrations as $migration) {
            if ($migration['short_id'] === $migrationId || $migration['migration_id'] === $migrationId) {
                $migrationInfo = $migration;
                break;
            }
        }

        if (! $migrationInfo) {
            return response()->json(['error' => 'Migration non trouvée'], 404);
        }

        $baseBackupDir = $config->getMigrationsPath();
        // Les dossiers sont nommés "migration-XXXX"
        $migrationDir = $baseBackupDir.'/migration-'.$migrationInfo['migration_id'];

        if (! File::exists($migrationDir)) {
            // Essayer aussi avec le répertoire direct depuis migrationInfo
            if (isset($migrationInfo['directory']) && File::exists($migrationInfo['directory'])) {
                $migrationDir = $migrationInfo['directory'];
            } else {
                return response()->json([
                    'error' => 'Répertoire de migration non trouvé',
                    'searched_path' => $migrationDir,
                    'migration_id' => $migrationInfo['migration_id'] ?? 'non défini',
                    'short_id' => $migrationInfo['short_id'] ?? 'non défini',
                    'base_dir' => $baseBackupDir,
                ], 404);
            }
        }

        $metadataPath = $migrationDir.'/metadata.json';
        $metadata = [];

        if (File::exists($metadataPath)) {
            $metadata = File::json($metadataPath);
        }

        $files = File::files($migrationDir);
        $backupFiles = [];

        foreach ($files as $file) {
            if ($file->getFilename() !== 'metadata.json' && $file->getFilename() !== '.gitignore') {
                $backupFiles[] = [
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'modified' => now()->createFromTimestamp($file->getMTime())->toDateTimeString(),
                ];
            }
        }

        return response()->json([
            'migration_id' => $migrationInfo['migration_id'],
            'short_id' => $migrationInfo['short_id'],
            'migration_dir' => $migrationDir,
            'metadata' => $metadata,
            'backup_files' => $backupFiles,
            'files_count' => \count($backupFiles),
        ]);
    }
}
