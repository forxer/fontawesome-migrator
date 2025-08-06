<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Http\Controllers;

use Carbon\Carbon;
use FontAwesome\Migrator\Services\MetadataManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class MigrationsController extends Controller
{
    /**
     * Afficher la liste des rapports
     */
    public function index()
    {
        // Récupérer les sessions qui contiennent des rapports
        $sessions = MetadataManager::getAvailableMigrations();
        $reports = [];

        foreach ($sessions as $session) {
            $sessionDir = $session['directory'];
            $sessionId = $session['session_id'];
            $shortId = $session['short_id'];
            $sessionMetadata = $session['metadata'];

            // Métadonnées de la session
            // Ignorer les sessions sans données de migration
            if (! $sessionMetadata) {
                continue;
            }

            if (! isset($sessionMetadata['migration_results'])) {
                continue;
            }

            $reports[] = [
                'name' => 'Migration Report',
                'filename' => 'metadata.json',
                'session_id' => $sessionId,
                'short_id' => $shortId,
                'created_at' => Carbon::parse($sessionMetadata['started_at']),
                'size' => File::size($sessionDir.'/metadata.json'),
                'metadata_path' => $sessionDir.'/metadata.json',
                'has_json' => true,
                'dry_run' => $sessionMetadata['dry_run'] ?? false,
                'metadata' => $sessionMetadata,

                // Données enrichies de session
                'backup_count' => $session['backup_count'] ?? 0,
                'package_version' => $sessionMetadata['package_version'] ?? 'unknown',
                'duration' => $sessionMetadata['duration'] ?? null,
                'migration_origin' => $sessionMetadata['migration_source'] ?? 'unknown',
                'migration_options' => [
                    'source_version' => $sessionMetadata['source_version'],
                    'target_version' => $sessionMetadata['target_version'],
                    'icons_only' => $sessionMetadata['icons_only'],
                    'assets_only' => $sessionMetadata['assets_only'],
                ],
                'statistics' => [
                    'total_files' => $sessionMetadata['total_files'] ?? 0,
                    'modified_files' => $sessionMetadata['modified_files'] ?? 0,
                    'total_changes' => $sessionMetadata['total_changes'] ?? 0,
                    'warnings' => $sessionMetadata['warnings'] ?? 0,
                    'errors' => $sessionMetadata['errors'] ?? 0,
                ],
                'migration_summary' => [
                    'total_files' => $sessionMetadata['total_files'] ?? 0,
                    'modified_files' => $sessionMetadata['modified_files'] ?? 0,
                    'total_changes' => $sessionMetadata['total_changes'] ?? 0,
                ],
            ];
        }

        // Trier par date de création (plus récent en premier)
        usort($reports, fn ($a, $b): int => $b['created_at'] <=> $a['created_at']);

        // Calculer les statistiques globales
        $stats = $this->getSessionStats($sessions);

        return view('fontawesome-migrator::migrations.index', [
            'reports' => $reports,
            'stats' => $stats,
        ]);
    }

    /**
     * Afficher un rapport spécifique (depuis métadonnées)
     */
    public function show(string $sessionId)
    {
        // Chercher la session par ID (court ou complet)
        $sessions = MetadataManager::getAvailableMigrations();
        $sessionInfo = array_find($sessions, fn ($session): bool => $session['short_id'] === $sessionId || $session['session_id'] === $sessionId);

        if (! $sessionInfo) {
            abort(404, 'Session de migration non trouvée');
        }

        $sessionMetadata = $sessionInfo['metadata'] ?? null;

        if (! $sessionMetadata) {
            abort(404, 'Métadonnées de session non trouvées');
        }

        // Retourner JSON si demandé
        if (request()->wantsJson()) {
            return response()->json($sessionMetadata['migration_results']);
        }

        // Toutes les données proviennent de metadata.json
        $sessionMetadata = $sessionInfo['metadata'] ?? null;

        if (! $sessionMetadata) {
            abort(404, 'Métadonnées de session non trouvées');
        }

        // Préparer les données pour la vue - TOUT depuis metadata.json simplifiée
        $viewData = [
            // Données métier
            'results' => $sessionMetadata['files'] ?? [],
            'stats' => [
                'total_files' => $sessionMetadata['total_files'] ?? 0,
                'modified_files' => $sessionMetadata['modified_files'] ?? 0,
                'total_changes' => $sessionMetadata['total_changes'] ?? 0,
                'warnings' => $sessionMetadata['warnings'] ?? 0,
                'errors' => $sessionMetadata['errors'] ?? 0,
                'assets_migrated' => $sessionMetadata['assets_migrated'] ?? 0,
                'icons_migrated' => $sessionMetadata['icons_migrated'] ?? 0,
                'migration_success' => $sessionMetadata['migration_success'] ?? true,
                'changes_by_type' => $sessionMetadata['changes_by_type'] ?? [],
                'asset_types' => $sessionMetadata['asset_types'] ?? [],
            ],
            'enrichedWarnings' => $sessionMetadata['warnings_details'] ?? [],

            // Données de contexte
            'timestamp' => Carbon::parse($sessionMetadata['started_at'])->format('Y-m-d H:i:s'),
            'isDryRun' => $sessionMetadata['dry_run'],
            'migrationOptions' => [
                'source_version' => $sessionMetadata['source_version'],
                'target_version' => $sessionMetadata['target_version'],
                'icons_only' => $sessionMetadata['icons_only'],
                'assets_only' => $sessionMetadata['assets_only'],
            ],
            'configuration' => $sessionMetadata['scan_config'] ?? [],
            'packageVersion' => $sessionMetadata['package_version'],
            'sessionId' => $sessionMetadata['session_id'],
            'shortId' => $sessionMetadata['short_id'],
            'duration' => $sessionMetadata['duration'],
            'metadata' => $sessionMetadata, // Toutes les métadonnées pour accès aux données custom
        ];

        return view('fontawesome-migrator::migrations.show', $viewData);
    }

    /**
     * Supprimer une session complète
     */
    public function destroy(string $sessionId)
    {
        // Chercher la session par ID (court ou complet)
        $sessions = MetadataManager::getAvailableMigrations();
        $sessionInfo = array_find($sessions, fn ($session): bool => $session['short_id'] === $sessionId || $session['session_id'] === $sessionId);

        if (! $sessionInfo) {
            return response()->json(['error' => 'Session non trouvée'], 404);
        }

        // Supprimer tout le répertoire de session
        $deleted = File::deleteDirectory($sessionInfo['directory']);

        if ($deleted) {
            return response()->json(['message' => 'Session supprimée avec succès']);
        }

        return response()->json(['error' => 'Erreur lors de la suppression'], 500);
    }

    /**
     * Nettoyer les anciennes sessions
     */
    public function cleanup(Request $request)
    {
        $days = $request->input('days', 30);
        $deleted = MetadataManager::cleanOldSessions($days);

        return response()->json([
            'message' => 'Nettoyage terminé',
            'deleted' => $deleted,
            'days' => $days,
        ]);
    }

    /**
     * Obtenir les statistiques des sessions
     */
    protected function getSessionStats(array $sessions): array
    {
        $totalBackups = 0;
        $totalSize = 0;

        foreach ($sessions as $session) {
            $totalBackups += $session['backup_count'];

            // Calculer la taille totale des fichiers
            $files = File::files($session['directory']);

            foreach ($files as $file) {
                $totalSize += $file->getSize();
            }
        }

        return [
            'total_migrations' => \count($sessions),
            'total_backups' => $totalBackups,
            'total_size' => $totalSize,
            'last_migration' => $sessions[0] ?? null,
        ];
    }
}
