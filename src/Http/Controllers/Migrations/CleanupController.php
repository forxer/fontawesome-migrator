<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Http\Controllers\Migrations;

use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class CleanupController extends Controller
{
    /**
     * Nettoyer les anciennes migrations
     */
    public function __invoke(Request $request, MetadataManagerInterface $metadataManager)
    {
        $days = $request->input('days', 30);
        $deleted = $metadataManager->cleanOldMigrations($days);

        return response()->json([
            'message' => 'Nettoyage terminé',
            'deleted' => $deleted,
            'days' => $days,
        ]);
    }
}
