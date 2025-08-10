<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Http\Controllers\Migrations;

use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class DestroyController extends Controller
{
    /**
     * Supprimer une migration complète
     */
    public function __invoke(string $migrationId, MetadataManagerInterface $metadataManager)
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
}
