<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Http\Controllers\Cleanup;

use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class ExecuteController extends Controller
{
    /**
     * Exécuter les actions de nettoyage
     */
    public function __invoke(Request $request, MetadataManagerInterface $metadataManager)
    {
        $action = $request->input('action');
        $results = [];

        switch ($action) {
            case 'cleanup_old_migrations':
                $days = (int) $request->input('days', Config::integer('fontawesome-migrator.cleanup.old_migrations_days', 10));
                $deleted = $metadataManager->cleanOldMigrations($days);
                $results = [
                    'message' => \sprintf('Nettoyage terminé : %d migrations supprimées (plus de %s jours)', $deleted, $days),
                    'deleted' => $deleted,
                    'type' => 'old_migrations',
                ];
                break;

            case 'cleanup_test_migrations':
                $days = (int) $request->input('days', Config::integer('fontawesome-migrator.cleanup.test_migrations_days', 7));
                // Nettoyer spécifiquement les migrations de test/web interface
                $migrations = $metadataManager->getAvailableMigrations();
                $deleted = 0;

                foreach ($migrations as $migration) {
                    $createdAt = \is_string($migration['created_at'])
                        ? \Carbon\Carbon::parse($migration['created_at'])
                        : $migration['created_at'];
                    $age = abs(now()->diffInDays($createdAt, false));
                    $isTestMigration = ($migration['source'] ?? 'cli') === 'web_interface';

                    if ($age > $days && $isTestMigration && File::deleteDirectory($migration['directory'])) {
                        $deleted++;
                    }
                }

                $results = [
                    'message' => \sprintf('Migrations de test nettoyées : %d suppressions (plus de %s jours)', $deleted, $days),
                    'deleted' => $deleted,
                    'type' => 'test_migrations',
                ];
                break;

            case 'delete_specific':
                $migrationId = $request->input('migration_id');
                $migrations = $metadataManager->getAvailableMigrations();
                $migrationInfo = array_find($migrations, fn ($migration): bool => $migration['short_id'] === $migrationId || $migration['migration_id'] === $migrationId
                );

                if (! $migrationInfo) {
                    return response()->json(['error' => 'Migration non trouvée'], 404);
                }

                $deleted = File::deleteDirectory($migrationInfo['directory']);
                $results = [
                    'message' => $deleted ? 'Migration supprimée avec succès' : 'Erreur lors de la suppression',
                    'deleted' => $deleted ? 1 : 0,
                    'type' => 'specific_migration',
                ];
                break;

            case 'cleanup_all':
                // Nettoyage complet : toutes les migrations anciennes
                $oldDaysThreshold = Config::integer('fontawesome-migrator.cleanup.old_migrations_days', 10);
                $testDaysThreshold = Config::integer('fontawesome-migrator.cleanup.test_migrations_days', 7);

                $deletedOld = $metadataManager->cleanOldMigrations($oldDaysThreshold);

                // Plus nettoyer les migrations de test
                $migrations = $metadataManager->getAvailableMigrations();
                $deletedTests = 0;

                foreach ($migrations as $migration) {
                    $createdAt = \is_string($migration['created_at'])
                        ? \Carbon\Carbon::parse($migration['created_at'])
                        : $migration['created_at'];
                    $age = abs(now()->diffInDays($createdAt, false));
                    $isTestMigration = ($migration['source'] ?? 'cli') === 'web_interface';

                    if ($age > $testDaysThreshold && $isTestMigration && File::deleteDirectory($migration['directory'])) {
                        $deletedTests++;
                    }
                }

                $results = [
                    'message' => \sprintf('Nettoyage complet : %d anciennes + %d tests supprimées', $deletedOld, $deletedTests),
                    'deleted' => $deletedOld + $deletedTests,
                    'type' => 'complete_cleanup',
                ];
                break;

            case 'cleanup_everything':
                // SUPPRIMER TOUTES les migrations sans exception
                $migrations = $metadataManager->getAvailableMigrations();
                $deletedAll = 0;

                foreach ($migrations as $migration) {
                    if (File::deleteDirectory($migration['directory'])) {
                        $deletedAll++;
                    }
                }

                $results = [
                    'message' => \sprintf('⚠️ TOUT supprimé : %d migrations complètement effacées', $deletedAll),
                    'deleted' => $deletedAll,
                    'type' => 'nuclear_cleanup',
                ];
                break;

            default:
                return response()->json(['error' => 'Action non reconnue'], 400);
        }

        return response()->json($results);
    }
}
