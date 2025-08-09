<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Http\Controllers;

use Carbon\Carbon;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class MigrationsController extends Controller
{
    /**
     * Afficher la liste des rapports
     */
    public function index(MetadataManagerInterface $metadataManager)
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
     * Afficher un rapport spécifique (depuis métadonnées)
     */
    public function show(string $migrationId, MetadataManagerInterface $metadataManager)
    {
        // Chercher la migration par ID (court ou complet)
        $migrations = $metadataManager->getAvailableMigrations();
        $migrationInfo = array_find($migrations, fn ($migration): bool => $migration['short_id'] === $migrationId || $migration['migration_id'] === $migrationId);

        if (! $migrationInfo) {
            abort(404, 'Migration non trouvée');
        }

        $migrationMetadata = $migrationInfo['metadata'] ?? null;

        if (! $migrationMetadata) {
            abort(404, 'Métadonnées de migration non trouvées');
        }

        // Retourner JSON si demandé
        if (request()->wantsJson()) {
            return response()->json($migrationMetadata);
        }

        // Toutes les données proviennent de metadata.json
        $migrationMetadata = $migrationInfo['metadata'] ?? null;

        if (! $migrationMetadata) {
            abort(404, 'Métadonnées de migration non trouvées');
        }

        // Préparer les données pour la vue - TOUT depuis metadata.json simplifiée
        $viewData = [
            // Données métier
            'results' => $migrationMetadata['files'] ?? [],
            'stats' => [
                'total_files' => $migrationMetadata['total_files'] ?? 0,
                'modified_files' => $migrationMetadata['modified_files'] ?? 0,
                'total_changes' => $migrationMetadata['total_changes'] ?? 0,
                'warnings' => $migrationMetadata['warnings'] ?? 0,
                'errors' => $migrationMetadata['errors'] ?? 0,
                'assets_migrated' => $migrationMetadata['assets_migrated'] ?? 0,
                'icons_migrated' => $migrationMetadata['icons_migrated'] ?? 0,
                'migration_success' => $migrationMetadata['migration_success'] ?? true,
                'changes_by_type' => $migrationMetadata['changes_by_type'] ?? [],
                'asset_types' => $migrationMetadata['asset_types'] ?? [],
            ],
            'enrichedWarnings' => $migrationMetadata['warnings_details'] ?? [],

            // Données de contexte
            'timestamp' => Carbon::parse($migrationMetadata['started_at'] ?? now())->format('Y-m-d H:i:s'),
            'isDryRun' => $migrationMetadata['dry_run'] ?? false,
            'migrationOptions' => [
                'source_version' => $migrationMetadata['source_version'] ?? '5',
                'target_version' => $migrationMetadata['target_version'] ?? '6',
                'icons_only' => $migrationMetadata['icons_only'] ?? false,
                'assets_only' => $migrationMetadata['assets_only'] ?? false,
            ],
            'configuration' => $migrationMetadata['scan_config'] ?? [],
            'packageVersion' => $migrationMetadata['package_version'] ?? 'unknown',
            'migrationId' => $migrationMetadata['migration_id'] ?? 'unknown',
            'shortId' => $migrationMetadata['short_id'] ?? substr($migrationMetadata['migration_id'] ?? 'unknown', 0, 8),
            'duration' => $migrationMetadata['duration'] ?? null,
            'metadata' => $migrationMetadata, // Toutes les métadonnées pour accès aux données custom
        ];

        return view('fontawesome-migrator::migrations.show', $viewData);
    }

    /**
     * Supprimer une migration complète
     */
    public function destroy(string $migrationId, MetadataManagerInterface $metadataManager)
    {
        // Chercher la migration par ID (court ou complet)
        $migrations = $metadataManager->getAvailableMigrations();
        $migrationInfo = array_find($migrations, fn ($migration): bool => $migration['short_id'] === $migrationId || $migration['migration_id'] === $migrationId);

        if (! $migrationInfo) {
            return response()->json(['error' => 'Migration non trouvée'], 404);
        }

        // Supprimer tout le répertoire de migration
        $deleted = File::deleteDirectory($migrationInfo['directory']);

        if ($deleted) {
            return response()->json(['message' => 'Migration supprimée avec succès']);
        }

        return response()->json(['error' => 'Erreur lors de la suppression'], 500);
    }

    /**
     * Nettoyer les anciennes migrations
     */
    public function cleanup(Request $request, MetadataManagerInterface $metadataManager)
    {
        $days = $request->input('days', 30);
        $deleted = $metadataManager->cleanOldMigrations($days);

        return response()->json([
            'message' => 'Nettoyage terminé',
            'deleted' => $deleted,
            'days' => $days,
        ]);
    }

    /**
     * Obtenir les statistiques des migrations
     */
    protected function getMigrationStats(array $migrations): array
    {
        $totalBackups = 0;
        $totalSize = 0;

        foreach ($migrations as $migration) {
            $totalBackups += $migration['backup_count'];

            // Calculer la taille totale des fichiers
            $files = File::files($migration['directory']);

            foreach ($files as $file) {
                $totalSize += $file->getSize();
            }
        }

        return [
            'total_migrations' => \count($migrations),
            'total_backups' => $totalBackups,
            'total_size' => $totalSize,
            'last_migration' => $migrations[0] ?? null,
        ];
    }
}
