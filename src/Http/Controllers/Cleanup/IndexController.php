<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Http\Controllers\Cleanup;

use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;

class IndexController extends Controller
{
    /**
     * Interface de nettoyage centralisée
     */
    public function __invoke(MetadataManagerInterface $metadataManager)
    {
        // Récupérer les statistiques pour l'affichage
        $migrations = collect($metadataManager->getAvailableMigrations());

        // Récupérer les valeurs de configuration
        $oldDaysThreshold = Config::integer('fontawesome-migrator.cleanup.old_migrations_days', 10);
        $testDaysThreshold = Config::integer('fontawesome-migrator.cleanup.test_migrations_days', 7);

        // Calculer les statistiques de nettoyage
        $now = now();
        $oldMigrationsCount = $migrations->filter(function ($migration) use ($now, $oldDaysThreshold): bool {
            $createdAt = \is_string($migration['created_at'])
                ? \Carbon\Carbon::parse($migration['created_at'])
                : $migration['created_at'];

            return $now->diffInDays($createdAt) > $oldDaysThreshold;
        })->count();

        $oldTestMigrationsCount = $migrations->filter(function ($migration) use ($now, $testDaysThreshold): bool {
            $createdAt = \is_string($migration['created_at'])
                ? \Carbon\Carbon::parse($migration['created_at'])
                : $migration['created_at'];

            return $now->diffInDays($createdAt) > $testDaysThreshold &&
                   ($migration['source'] ?? 'cli') === 'web_interface'; // migrations de test
        })->count();

        // Calculer l'espace disque utilisé
        $totalSize = $migrations->sum('metadata.total_size');

        return view('fontawesome-migrator::cleanup.index', [
            'totalMigrations' => $migrations->count(),
            'oldMigrationsCount' => $oldMigrationsCount,
            'oldTestMigrationsCount' => $oldTestMigrationsCount,
            'oldDaysThreshold' => $oldDaysThreshold,
            'testDaysThreshold' => $testDaysThreshold,
            'totalSize' => $totalSize,
            'migrations' => $migrations,
        ]);
    }

    /**
     * Compter les migrations selon l'âge (endpoint AJAX)
     */
    public function countMigrations(int $days, MetadataManagerInterface $metadataManager)
    {
        $migrations = collect($metadataManager->getAvailableMigrations());
        $now = now();

        // Compter toutes les migrations de plus de X jours (>= si days = 0)
        $oldMigrations = $migrations->filter(function ($migration) use ($now, $days): bool {
            if (! isset($migration['created_at'])) {
                return false;
            }
            $createdAt = \is_string($migration['created_at'])
                ? \Carbon\Carbon::parse($migration['created_at'])
                : $migration['created_at'];
            $diffDays = abs($now->diffInDays($createdAt, false)); // false = signed difference

            return $diffDays >= $days;
        })->count();

        // Compter les migrations de test de plus de X jours (>= si days = 0)
        $testMigrations = $migrations->filter(function ($migration) use ($now, $days): bool {
            if (! isset($migration['created_at'])) {
                return false;
            }
            $createdAt = \is_string($migration['created_at'])
                ? \Carbon\Carbon::parse($migration['created_at'])
                : $migration['created_at'];
            $diffDays = abs($now->diffInDays($createdAt, false)); // false = signed difference

            return $diffDays >= $days &&
                   ($migration['source'] ?? 'cli') === 'web_interface';
        })->count();

        return response()->json([
            'old_migrations' => $oldMigrations,
            'test_migrations' => $testMigrations,
        ]);
    }
}
