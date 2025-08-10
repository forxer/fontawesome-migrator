<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Http\Controllers\Migrations;

use Carbon\Carbon;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class IndexController extends Controller
{
    /**
     * Afficher la liste des migrations
     */
    public function __invoke(MetadataManagerInterface $metadataManager)
    {
        // Récupérer les migrations qui contiennent des rapports
        $migrations = $metadataManager->getAvailableMigrations();
        $reports = [];

        foreach ($migrations as $migration) {
            $migrationDir = $migration['directory'];
            $migrationId = $migration['migration_id'];
            $shortId = $migration['short_id'];
            $migrationMetadata = $migration['metadata'];

            // Métadonnées de la migration
            // Ignorer les migrations sans données de migration
            if (! $migrationMetadata) {
                continue;
            }

            // Vérifier qu'il y a bien des données de migration (files ou total_files)
            if (! isset($migrationMetadata['total_files']) && ! isset($migrationMetadata['files'])) {
                continue;
            }

            $reports[] = [
                'name' => 'Migration Report',
                'filename' => 'metadata.json',
                'migration_id' => $migrationId,
                'short_id' => $shortId,
                'created_at' => Carbon::parse($migrationMetadata['started_at']),
                'size' => File::size($migrationDir.'/metadata.json'),
                'metadata_path' => $migrationDir.'/metadata.json',
                'has_json' => true,
                'dry_run' => $migrationMetadata['dry_run'] ?? false,
                'metadata' => $migrationMetadata,

                // Données enrichies de migration
                'backup_count' => $migration['backup_count'] ?? 0,
                'package_version' => $migrationMetadata['package_version'] ?? 'unknown',
                'duration' => $migrationMetadata['duration'] ?? null,
                'migration_origin' => $migrationMetadata['migration_source'] ?? 'unknown',
                'migration_options' => [
                    'source_version' => $migrationMetadata['source_version'] ?? '5',
                    'target_version' => $migrationMetadata['target_version'] ?? '6',
                    'icons_only' => $migrationMetadata['icons_only'] ?? false,
                    'assets_only' => $migrationMetadata['assets_only'] ?? false,
                ],
                'statistics' => [
                    'total_files' => $migrationMetadata['total_files'] ?? 0,
                    'modified_files' => $migrationMetadata['modified_files'] ?? 0,
                    'total_changes' => $migrationMetadata['total_changes'] ?? 0,
                    'warnings' => $migrationMetadata['warnings'] ?? 0,
                    'errors' => $migrationMetadata['errors'] ?? 0,
                ],
                'migration_summary' => [
                    'total_files' => $migrationMetadata['total_files'] ?? 0,
                    'modified_files' => $migrationMetadata['modified_files'] ?? 0,
                    'total_changes' => $migrationMetadata['total_changes'] ?? 0,
                ],
            ];
        }

        // Trier par date de création (plus récent en premier)
        usort($reports, fn ($a, $b): int => $b['created_at'] <=> $a['created_at']);

        // Calculer les statistiques globales
        $stats = $this->getMigrationStats($migrations);

        return view('fontawesome-migrator::migrations.index', [
            'reports' => $reports,
            'stats' => $stats,
        ]);
    }

    /**
     * Obtenir les statistiques des migrations
     */
    private function getMigrationStats(array $migrations): array
    {
        $stats = [
            'total_migrations' => 0,
            'dry_run_count' => 0,
            'real_run_count' => 0,
            'total_files_scanned' => 0,
            'total_files_modified' => 0,
            'total_changes_made' => 0,
            'total_warnings' => 0,
            'total_errors' => 0,
            'total_backups' => 0,
            'total_size' => 0,
            'avg_changes' => 0,
            'total_changes' => 0,
            'last_migration' => null,
        ];

        $totalChanges = 0;

        foreach ($migrations as $migration) {
            $metadata = $migration['metadata'] ?? null;

            if (! $metadata) {
                continue;
            }

            $stats['total_migrations']++;

            if ($metadata['dry_run'] ?? false) {
                $stats['dry_run_count']++;
            } else {
                $stats['real_run_count']++;
            }

            $stats['total_files_scanned'] += $metadata['total_files'] ?? 0;
            $stats['total_files_modified'] += $metadata['modified_files'] ?? 0;
            $stats['total_changes_made'] += $metadata['total_changes'] ?? 0;
            $stats['total_warnings'] += $metadata['warnings'] ?? 0;
            $stats['total_errors'] += $metadata['errors'] ?? 0;
            $stats['total_backups'] += $migration['backup_count'] ?? 0;

            // Calculer la taille totale des fichiers de migration
            $migrationDir = $migration['directory'];

            if (File::exists($migrationDir)) {
                $files = File::allFiles($migrationDir);

                foreach ($files as $file) {
                    $stats['total_size'] += $file->getSize();
                }
            }

            $totalChanges += $metadata['total_changes'] ?? 0;

            if (! $stats['last_migration'] instanceof Carbon
                || Carbon::parse($metadata['started_at']) > $stats['last_migration']) {
                $stats['last_migration'] = Carbon::parse($metadata['started_at']);
            }
        }

        if ($stats['total_migrations'] > 0) {
            $stats['avg_changes'] = (int) round($totalChanges / $stats['total_migrations']);
        }

        $stats['total_changes'] = $totalChanges;

        return $stats;
    }
}
