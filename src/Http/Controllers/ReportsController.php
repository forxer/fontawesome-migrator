<?php

namespace FontAwesome\Migrator\Http\Controllers;

use Carbon\Carbon;
use FontAwesome\Migrator\Services\MetadataManager;
use FontAwesome\Migrator\Services\MigrationReporter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

class ReportsController extends Controller
{
    /**
     * Afficher la liste des rapports
     */
    public function index()
    {
        // Récupérer les sessions qui contiennent des rapports
        $sessions = MetadataManager::getAvailableSessions();
        $reports = [];

        foreach ($sessions as $session) {
            $sessionDir = $session['directory'];
            $sessionId = $session['session_id'];
            $shortId = $session['short_id'];

            // Chercher les fichiers HTML de rapport dans la session
            $files = File::files($sessionDir);

            foreach ($files as $file) {
                if ($file->getExtension() === 'html' && $file->getFilename() !== 'metadata.json') {
                    $jsonPath = str_replace('.html', '.json', $file->getRealPath());

                    // Lire l'information dry_run depuis le JSON si disponible
                    $isDryRun = false;

                    if (File::exists($jsonPath)) {
                        $jsonContent = File::get($jsonPath);
                        $jsonData = json_decode($jsonContent, true);
                        $isDryRun = $jsonData['meta']['dry_run'] ?? false;
                    }

                    $reports[] = [
                        'name' => $file->getFilenameWithoutExtension(),
                        'filename' => $file->getFilename(),
                        'session_id' => $sessionId,
                        'short_id' => $shortId,
                        'created_at' => Carbon::createFromTimestamp($file->getMTime()),
                        'size' => $file->getSize(),
                        'html_path' => $file->getRealPath(),
                        'json_path' => $jsonPath,
                        'has_json' => File::exists($jsonPath),
                        'dry_run' => $isDryRun,
                    ];
                }
            }
        }

        // Trier par date de création (plus récent en premier)
        usort($reports, fn ($a, $b): int => $b['created_at'] <=> $a['created_at']);

        return view('fontawesome-migrator::reports.index', [
            'reports' => $reports,
            'breadcrumbs' => [
                ['label' => '📊 Rapports'],
            ],
        ]);
    }

    /**
     * Afficher un rapport spécifique
     */
    public function show(string $filename)
    {
        // Chercher le fichier dans toutes les sessions
        $sessions = MetadataManager::getAvailableSessions();
        $filePath = null;
        $sessionInfo = null;

        foreach ($sessions as $session) {
            $possiblePath = $session['directory'].'/'.$filename;

            if (File::exists($possiblePath)) {
                $filePath = $possiblePath;
                $sessionInfo = $session;
                break;
            }
        }

        if ($filePath === null || $filePath === '' || $filePath === '0') {
            abort(404, 'Rapport non trouvé');
        }

        // Si c'est un fichier JSON, retourner en JSON
        if (pathinfo($filename, PATHINFO_EXTENSION) === 'json') {
            return response()->json(json_decode(File::get($filePath), true));
        }

        // Pour les fichiers HTML, utiliser la vue Blade partagée
        $jsonPath = str_replace('.html', '.json', $filePath);

        if (! File::exists($jsonPath)) {
            // Fallback : utiliser la vue Blade avec données par défaut
            $viewData = [
                'results' => [],
                'stats' => [
                    'total_files' => 0,
                    'modified_files' => 0,
                    'total_changes' => 0,
                    'icons_migrated' => 0,
                    'assets_migrated' => 0,
                    'migration_success' => true,
                ],
                'timestamp' => Carbon::createFromTimestamp(filemtime($filePath))->format('Y-m-d H:i:s'),
                'isDryRun' => false,
                'migrationOptions' => [],
                'configuration' => [
                    'license_type' => 'free',
                    'scan_paths' => [],
                    'file_extensions' => [],
                    'backup_enabled' => true,
                ],
                'packageVersion' => '?',
            ];

            $viewData['breadcrumbs'] = [
                ['label' => 'Rapports', 'url' => route('fontawesome-migrator.reports.index')],
                ['label' => 'Rapport - Session '.($sessionInfo['short_id'] ?? 'inconnue')],
            ];

            return view('fontawesome-migrator::reports.show', $viewData);
        }

        // Charger les données depuis le fichier JSON
        $jsonData = json_decode(File::get($jsonPath), true);

        // Enrichir les données avec les avertissements formatés
        $results = $jsonData['files'] ?? [];
        $migrationReporter = app(MigrationReporter::class);
        $enrichedWarnings = $migrationReporter->extractWarnings($results);

        $viewData = [
            'results' => $results,
            'stats' => $jsonData['summary'] ?? [],
            'timestamp' => isset($jsonData['meta']['generated_at']) ?
                Carbon::parse($jsonData['meta']['generated_at'])->format('Y-m-d H:i:s') :
                Carbon::now()->format('Y-m-d H:i:s'),
            'isDryRun' => $jsonData['meta']['dry_run'] ?? false,
            'migrationOptions' => $jsonData['meta']['migration_options'] ?? [],
            'configuration' => $jsonData['meta']['configuration'] ?? [],
            'packageVersion' => $jsonData['meta']['package_version'] ?? '1.1.0',
            'enrichedWarnings' => $enrichedWarnings,
        ];

        $viewData['breadcrumbs'] = [
            ['label' => 'Rapports', 'url' => route('fontawesome-migrator.reports.index')],
            ['label' => 'Rapport - Session '.($sessionInfo['short_id'] ?? 'inconnue')],
        ];

        return view('fontawesome-migrator::reports.show', $viewData);
    }

    /**
     * Supprimer un rapport
     */
    public function destroy(string $filename)
    {
        // Chercher le fichier dans toutes les sessions
        $sessions = MetadataManager::getAvailableSessions();
        $htmlPath = null;

        foreach ($sessions as $session) {
            $possiblePath = $session['directory'].'/'.$filename;

            if (File::exists($possiblePath)) {
                $htmlPath = $possiblePath;
                break;
            }
        }

        if ($htmlPath === null || $htmlPath === '' || $htmlPath === '0') {
            return response()->json(['error' => 'Rapport non trouvé'], 404);
        }

        $jsonPath = str_replace('.html', '.json', $htmlPath);
        $deleted = 0;

        if (File::exists($htmlPath)) {
            File::delete($htmlPath);
            $deleted++;
        }

        if (File::exists($jsonPath)) {
            File::delete($jsonPath);
            $deleted++;
        }

        return response()->json(['message' => 'Rapport supprimé avec succès']);
    }

    /**
     * Nettoyer les anciens rapports
     */
    public function cleanup(Request $request)
    {
        $days = $request->input('days', 30);
        $cutoffTime = time() - ($days * 24 * 60 * 60);
        $deleted = 0;

        // Parcourir toutes les sessions
        $sessions = MetadataManager::getAvailableSessions();

        foreach ($sessions as $session) {
            $sessionDir = $session['directory'];
            $files = File::files($sessionDir);

            foreach ($files as $file) {
                // Ne supprimer que les fichiers de rapport (HTML/JSON), pas les métadonnées
                if (\in_array($file->getExtension(), ['html', 'json']) &&
                    $file->getFilename() !== 'metadata.json' &&
                    $file->getMTime() < $cutoffTime) {
                    File::delete($file->getRealPath());
                    $deleted++;
                }
            }
        }

        return response()->json([
            'message' => 'Nettoyage terminé',
            'deleted' => $deleted,
            'days' => $days,
        ]);
    }
}
