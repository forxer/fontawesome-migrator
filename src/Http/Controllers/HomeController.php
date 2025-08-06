<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Http\Controllers;

use Carbon\Carbon;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use FontAwesome\Migrator\Services\Configuration\PackageVersionService;
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
    public function index(MetadataManagerInterface $metadataManager, PackageVersionService $packageVersionService)
    {
        // Récupérer les statistiques globales depuis les métadonnées de sessions
        $sessions = $metadataManager->getAvailableMigrations();
        $totalSize = 0;
        $lastActivity = null;
        $successfulMigrations = 0;
        $totalChanges = 0;
        $dryRunCount = 0;
        $realRunCount = 0;

        foreach ($sessions as $session) {
            $sessionMetadata = $session['metadata'] ?? null;

            // Calculer la taille totale des fichiers de session
            $files = File::files($session['directory']);

            foreach ($files as $file) {
                $totalSize += $file->getSize();
            }

            if ($sessionMetadata) {
                // Dernière activité depuis les métadonnées
                if (isset($sessionMetadata['started_at'])) {
                    $sessionTime = Carbon::parse($sessionMetadata['started_at'])->timestamp;

                    if ($lastActivity === null || $sessionTime > $lastActivity) {
                        $lastActivity = $sessionTime;
                    }
                }

                // Compter les migrations réussies (non dry-run)
                if (isset($sessionMetadata['dry_run'])) {
                    if ($sessionMetadata['dry_run']) {
                        $dryRunCount++;
                    } else {
                        $realRunCount++;
                        $successfulMigrations++;
                    }
                }

                // Accumuler le total des changements
                if (isset($sessionMetadata['total_changes'])) {
                    $totalChanges += $sessionMetadata['total_changes'];
                }
            }
        }

        // Calculer les nouvelles métriques
        $totalMigrations = \count($sessions);
        $successRate = $totalMigrations > 0 ? round(($successfulMigrations / $totalMigrations) * 100) : 0;
        $avgChanges = $totalMigrations > 0 ? round($totalChanges / $totalMigrations) : 0;

        $stats = [
            'total_migrations' => $totalMigrations,
            'total_size' => $totalSize,
            'last_activity' => $lastActivity ? Carbon::createFromTimestamp($lastActivity) : null,
            'successful_migrations' => $successfulMigrations,
            'package_version' => $packageVersionService->getVersion(),
            'success_rate' => $successRate,
            'avg_changes' => $avgChanges,
            'dry_run_count' => $dryRunCount,
            'real_run_count' => $realRunCount,
        ];

        // Dernières migrations (depuis métadonnées)
        $recentMigrations = [];

        foreach ($sessions as $session) {
            $sessionMetadata = $session['metadata'] ?? null;

            if ($sessionMetadata && isset($sessionMetadata['total_files'])) {
                $recentMigrations[] = [
                    'name' => 'Migration '.($session['short_id'] ?? 'inconnue'),
                    'session_id' => $session['session_id'],
                    'short_id' => $session['short_id'],
                    'created_at' => Carbon::parse($sessionMetadata['started_at']),
                    'size' => File::size($session['directory'].'/metadata.json'),
                    'dry_run' => $sessionMetadata['dry_run'] ?? false,
                    'files_modified' => $sessionMetadata['modified_files'] ?? 0,
                    'total_changes' => $sessionMetadata['total_changes'] ?? 0,
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
