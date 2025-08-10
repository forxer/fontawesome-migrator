<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Http\Controllers\Migrations;

use Carbon\Carbon;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use Illuminate\Routing\Controller;

class ShowController extends Controller
{
    /**
     * Afficher un rapport de migration spécifique
     */
    public function __invoke(string $migrationId, MetadataManagerInterface $metadataManager)
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
            'shortId' => $migrationMetadata['short_id'] ?? 'unknown',
            'duration' => $migrationMetadata['duration'] ?? null,

            // Compteurs calculés
            'filesCount' => \count($migrationMetadata['files'] ?? []),
            'changesCount' => array_sum(array_map(fn ($file): int => \count($file['changes'] ?? []), $migrationMetadata['files'] ?? [])),
            'filesWithWarnings' => array_filter($migrationMetadata['files'] ?? [], fn ($file): bool => \count($file['warnings'] ?? []) > 0),

            // Données pour la vue
            'files' => $migrationMetadata['files'] ?? [],
            'migrationCreatedAt' => Carbon::parse($migrationMetadata['started_at'] ?? now()),
        ];

        return view('fontawesome-migrator::migrations.show', $viewData);
    }
}
