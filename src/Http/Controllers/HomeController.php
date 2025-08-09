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
        // Récupérer les statistiques globales depuis les métadonnées de migrations
        $migrations = $metadataManager->getAvailableMigrations();
        $totalSize = 0;
        $lastActivity = null;
        $successfulMigrations = 0;
        $totalChanges = 0;
        $dryRunCount = 0;
        $realRunCount = 0;

        foreach ($migrations as $migration) {
            $migrationMetadata = $migration['metadata'] ?? null;

            // Calculer la taille totale des fichiers de migration
            $files = File::files($migration['directory']);

            foreach ($files as $file) {
                $totalSize += $file->getSize();
            }

            if ($migrationMetadata) {
                // Dernière activité depuis les métadonnées
                if (isset($migrationMetadata['started_at'])) {
                    $migrationTime = Carbon::parse($migrationMetadata['started_at'])->timestamp;

                    if ($lastActivity === null || $migrationTime > $lastActivity) {
                        $lastActivity = $migrationTime;
                    }
                }

                // Compter les migrations réussies (non dry-run)
                if (isset($migrationMetadata['dry_run'])) {
                    if ($migrationMetadata['dry_run']) {
                        $dryRunCount++;
                    } else {
                        $realRunCount++;
                        $successfulMigrations++;
                    }
                }

                // Accumuler le total des changements
                if (isset($migrationMetadata['total_changes'])) {
                    $totalChanges += $migrationMetadata['total_changes'];
                }
            }
        }

        // Calculer les nouvelles métriques
        $totalMigrations = \count($migrations);
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

        foreach ($migrations as $migration) {
            $migrationMetadata = $migration['metadata'] ?? null;

            if ($migrationMetadata && isset($migrationMetadata['total_files'])) {
                $recentMigrations[] = [
                    'name' => 'Migration '.($migration['short_id'] ?? 'inconnue'),
                    'migration_id' => $migration['migration_id'],
                    'short_id' => $migration['short_id'],
                    'created_at' => Carbon::parse($migrationMetadata['started_at']),
                    'size' => File::size($migration['directory'].'/metadata.json'),
                    'dry_run' => $migrationMetadata['dry_run'] ?? false,
                    'files_modified' => $migrationMetadata['modified_files'] ?? 0,
                    'total_changes' => $migrationMetadata['total_changes'] ?? 0,
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
