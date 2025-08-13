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
            'isDryRun' => $migrationMetadata['migration_options']['dry_run'],
            'migrationOptions' => [
                'source_version' => $migrationMetadata['migration_options']['source_version'],
                'target_version' => $migrationMetadata['migration_options']['target_version'],
                'icons_only' => $migrationMetadata['migration_options']['icons_only'],
                'assets_only' => $migrationMetadata['migration_options']['assets_only'],
            ],
            'configuration' => array_merge(
                $migrationMetadata['scan_config'] ?? [],
                ['license_type' => $migrationMetadata['license_type'] ?? 'free']
            ),
            'packageVersion' => $migrationMetadata['package_version'] ?? 'unknown',
            'migrationId' => $migrationMetadata['migration_id'] ?? 'unknown',
            'shortId' => $migrationMetadata['short_id'] ?? 'unknown',
            'duration' => $migrationMetadata['duration'] ?? null,
            'migrationSource' => $migrationMetadata['source'] ?? 'cli',
            'userAgent' => $migrationMetadata['user_agent'] ?? 'Unknown',
            'ipAddress' => $migrationMetadata['ip_address'] ?? '127.0.0.1',

            // Compteur de backups depuis metadata.json
            'backupsCount' => $migrationMetadata['backups_count'] ?? 0,

            // Données pour la vue
            'files' => $migrationMetadata['files'] ?? [],
            'migrationCreatedAt' => Carbon::parse($migrationMetadata['started_at'] ?? now()),
        ];

        return view('fontawesome-migrator::migrations.show', $viewData);
    }
}
