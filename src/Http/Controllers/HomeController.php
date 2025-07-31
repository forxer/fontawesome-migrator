<?php

namespace FontAwesome\Migrator\Http\Controllers;

use Carbon\Carbon;
use FontAwesome\Migrator\Services\MetadataManager;
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
        // Récupérer les statistiques globales
        $sessions = MetadataManager::getAvailableSessions();
        $reportsCount = 0;
        $totalSize = 0;
        $lastActivity = null;
        $successfulMigrations = 0;

        foreach ($sessions as $session) {
            // Compter les rapports dans chaque session
            $files = File::files($session['directory']);

            foreach ($files as $file) {
                if ($file->getExtension() === 'html' && $file->getFilename() !== 'metadata.json') {
                    $reportsCount++;
                    $totalSize += $file->getSize();

                    if ($lastActivity === null || $file->getMTime() > $lastActivity) {
                        $lastActivity = $file->getMTime();
                    }
                }
            }

            // Compter les migrations réussies
            if (isset($session['dry_run']) && ! $session['dry_run']) {
                $successfulMigrations++;
            }
        }

        $stats = [
            'total_sessions' => \count($sessions),
            'total_reports' => $reportsCount,
            'total_size' => $totalSize,
            'last_activity' => $lastActivity ? Carbon::createFromTimestamp($lastActivity) : null,
            'successful_migrations' => $successfulMigrations,
            'package_version' => $this->getPackageVersion(),
        ];

        // Dernières activités (derniers rapports)
        $recentReports = [];

        foreach ($sessions as $session) {
            $sessionDir = $session['directory'];
            $files = File::files($sessionDir);

            foreach ($files as $file) {
                if ($file->getExtension() === 'html' && $file->getFilename() !== 'metadata.json') {
                    $recentReports[] = [
                        'name' => $file->getFilenameWithoutExtension(),
                        'filename' => $file->getFilename(),
                        'session_id' => $session['session_id'],
                        'short_id' => $session['short_id'],
                        'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                        'size' => $file->getSize(),
                        'dry_run' => $session['dry_run'] ?? false,
                    ];
                }
            }
        }

        // Trier par date et garder les 5 plus récents
        usort($recentReports, fn ($a, $b): int => $b['created_at'] <=> $a['created_at']);
        $recentReports = \array_slice($recentReports, 0, 5);

        return view('fontawesome-migrator::home.index', [
            'stats' => $stats,
            'recentReports' => $recentReports,
            'breadcrumbs' => [],
        ]);
    }

    /**
     * Obtenir la version du package
     */
    protected function getPackageVersion(): string
    {
        $changelogPath = __DIR__.'/../../CHANGELOG.md';

        if (file_exists($changelogPath)) {
            $content = file_get_contents($changelogPath);

            if (preg_match('/^(\d+\.\d+\.\d+).*\n-+/m', $content, $matches) ||
                preg_match('/^## (\d+\.\d+\.\d+)/m', $content, $matches)) {
                return $matches[1];
            }
        }

        return '2.0.0-dev';
    }
}
