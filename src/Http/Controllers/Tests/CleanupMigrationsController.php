<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Http\Controllers\Tests;

use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CleanupMigrationsController extends Controller
{
    /**
     * Nettoyer les migrations de test
     */
    public function __invoke(Request $request, MetadataManagerInterface $metadataManager)
    {
        $days = $request->input('days', 7);
        $deleted = $metadataManager->cleanOldMigrations($days);

        return response()->json([
            'message' => 'Nettoyage des migrations terminé',
            'deleted' => $deleted,
            'days' => $days,
        ]);
    }
}
