<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Http\Controllers\Cleanup;

use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use Illuminate\Routing\Controller;

class IndexController extends Controller
{
    /**
     * Interface de nettoyage centralisée
     */
    public function __invoke(MetadataManagerInterface $metadataManager)
    {
        // Récupérer les statistiques pour l'affichage
        $migrations = collect($metadataManager->getAvailableMigrations());

        // Calculer les statistiques de nettoyage
        $now = now();
        $old30Days = $migrations->filter(fn ($migration): bool => $now->diffInDays($migration['created_at']) > 30)->count();

        $old7Days = $migrations->filter(fn ($migration): bool => $now->diffInDays($migration['created_at']) > 7 &&
                ($migration['source'] ?? 'cli') === 'web_interface' // migrations de test
        )->count();

        // Calculer l'espace disque utilisé
        $totalSize = $migrations->sum('metadata.total_size');

        return view('fontawesome-migrator::cleanup.index', [
            'totalMigrations' => $migrations->count(),
            'old30Days' => $old30Days,
            'old7Days' => $old7Days,
            'totalSize' => $totalSize,
            'migrations' => $migrations,
        ]);
    }
}
