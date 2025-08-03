<?php

namespace FontAwesome\Migrator\Http\Controllers;

use Carbon\Carbon;
use FontAwesome\Migrator\Services\MetadataManager;
use FontAwesome\Migrator\Services\PackageVersionService;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

/**
 * Contrôleur pour la page d'accueil
 */
class HomeController extends Controller
{
    /**
     * Afficher la page d'accueil
     */
    public function index()
    {
        // Récupérer les statistiques globales depuis les métadonnées de sessions
        $sessions = MetadataManager::getAvailableSessions();
        $totalSize = 0;
        $lastActivity = null;
        $successfulMigrations = 0;
        $totalFilesMigrated = 0;

        foreach ($sessions as $session) {
            $sessionMetadata = $session['metadata'] ?? null;

            // Calculer la taille totale des fichiers de session
            $files = File::files($session['directory']);

            foreach ($files as $file) {
                $totalSize += $file->getSize();
            }

            if ($sessionMetadata) {
                // Dernière activité depuis les métadonnées
                if (isset($sessionMetadata['session']['started_at'])) {
                    $sessionTime = Carbon::parse($sessionMetadata['session']['started_at'])->timestamp;

                    if ($lastActivity === null || $sessionTime > $lastActivity) {
                        $lastActivity = $sessionTime;
                    }
                }

                // Compter les migrations réussies (non dry-run)
                if (isset($sessionMetadata['runtime']['dry_run']) && ! $sessionMetadata['runtime']['dry_run']) {
                    $successfulMigrations++;
                }

                // Compter les fichiers migrés
                if (isset($sessionMetadata['migration_results']['summary']['modified_files'])) {
                    $totalFilesMigrated += $sessionMetadata['migration_results']['summary']['modified_files'];
                }
            }
        }

        $stats = [
            'total_migrations' => \count($sessions),
            'total_files_migrated' => $totalFilesMigrated,
            'total_size' => $totalSize,
            'last_activity' => $lastActivity ? Carbon::createFromTimestamp($lastActivity) : null,
            'successful_migrations' => $successfulMigrations,
            'package_version' => PackageVersionService::getVersion(),
        ];

        // Dernières migrations (depuis métadonnées)
        $recentMigrations = [];

        foreach ($sessions as $session) {
            $sessionMetadata = $session['metadata'] ?? null;

            if ($sessionMetadata && isset($sessionMetadata['migration_results'])) {
                $recentMigrations[] = [
                    'name' => 'Migration '.($session['short_id'] ?? 'inconnue'),
                    'session_id' => $session['session_id'],
                    'short_id' => $session['short_id'],
                    'created_at' => Carbon::parse($sessionMetadata['session']['started_at']),
                    'size' => File::size($session['directory'].'/metadata.json'),
                    'dry_run' => $sessionMetadata['runtime']['dry_run'] ?? false,
                    'files_modified' => $sessionMetadata['migration_results']['summary']['modified_files'] ?? 0,
                    'total_changes' => $sessionMetadata['migration_results']['summary']['total_changes'] ?? 0,
                ];
            }
        }

        // Trier par date et garder les 5 plus récents
        usort($recentMigrations, fn ($a, $b): int => $b['created_at'] <=> $a['created_at']);
        $recentMigrations = \array_slice($recentMigrations, 0, 5);

        return view('fontawesome-migrator::home.index', [
            'stats' => $stats,
            'recentMigrations' => $recentMigrations,
        ]);
    }
}
