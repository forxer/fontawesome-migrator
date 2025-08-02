<?php

namespace FontAwesome\Migrator\Http\Controllers;

use Carbon\Carbon;
use FontAwesome\Migrator\Services\MetadataManager;
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
            $sessionMetadata = $session['metadata'];

            // Métadonnées de la session
            // Ignorer les sessions sans données de migration
            if (! $sessionMetadata) {
                continue;
            }

            if (! isset($sessionMetadata['migration_results'])) {
                continue;
            }

            // Chercher les fichiers HTML de rapport dans la session
            $files = File::files($sessionDir);

            foreach ($files as $file) {
                if ($file->getExtension() === 'html' && $file->getFilename() !== 'metadata.json') {
                    $reports[] = [
                        'name' => $file->getFilenameWithoutExtension(),
                        'filename' => $file->getFilename(),
                        'session_id' => $sessionId,
                        'short_id' => $shortId,
                        'created_at' => isset($sessionMetadata['session']['started_at'])
                            ? Carbon::parse($sessionMetadata['session']['started_at'])
                            : Carbon::createFromTimestamp($file->getMTime()),
                        'size' => $file->getSize(),
                        'html_path' => $file->getRealPath(),
                        'json_path' => null, // Données intégrées dans metadata.json
                        'has_json' => true,
                        'dry_run' => $sessionMetadata['runtime']['dry_run'] ?? false,
                    ];
                }
            }
        }

        // Trier par date de création (plus récent en premier)
        usort($reports, fn ($a, $b): int => $b['created_at'] <=> $a['created_at']);

        return view('fontawesome-migrator::reports.index', [
            'reports' => $reports,
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

        // Si c'est un fichier JSON, retourner les données depuis metadata.json
        if (pathinfo($filename, PATHINFO_EXTENSION) === 'json') {
            $sessionMetadata = $sessionInfo['metadata'] ?? null;

            if (! $sessionMetadata) {
                abort(404, 'Métadonnées de session non trouvées');
            }

            // Retourner les données depuis metadata.json
            return response()->json($sessionMetadata['migration_results'] ?? [
                'summary' => [],
                'files' => [],
                'enriched_warnings' => [],
            ]);
        }

        // Toutes les données proviennent de metadata.json
        $sessionMetadata = $sessionInfo['metadata'] ?? null;

        if (! $sessionMetadata) {
            abort(404, 'Métadonnées de session non trouvées');
        }

        $migrationResults = $sessionMetadata['migration_results'] ?? [
            'summary' => [
                'total_files' => 0,
                'modified_files' => 0,
                'total_changes' => 0,
                'icons_migrated' => 0,
                'assets_migrated' => 0,
                'migration_success' => true,
            ],
            'files' => [],
            'enriched_warnings' => [],
        ];

        // Préparer les données pour la vue - TOUT depuis metadata.json
        $viewData = [
            // Données métier
            'results' => $migrationResults['files'] ?? [],
            'stats' => $migrationResults['summary'] ?? [],
            'enrichedWarnings' => $migrationResults['enriched_warnings'] ?? [],

            // Données de contexte
            'timestamp' => Carbon::parse($sessionMetadata['session']['started_at'])->format('Y-m-d H:i:s'),
            'isDryRun' => $sessionMetadata['runtime']['dry_run'] ?? false,
            'migrationOptions' => $sessionMetadata['migration_options'] ?? [],
            'configuration' => $sessionMetadata['configuration'] ?? [
                'license_type' => 'free',
                'scan_paths' => [],
                'file_extensions' => [],
                'backup_enabled' => true,
            ],
            'packageVersion' => $sessionMetadata['session']['package_version'] ?? '?',
            'sessionId' => $sessionMetadata['session']['id'] ?? 'unknown',
            'shortId' => $sessionMetadata['session']['short_id'] ?? 'unknown',
            'duration' => $sessionMetadata['runtime']['duration'] ?? null,
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

        // Supprimer seulement le fichier HTML de visualisation
        $deleted = 0;

        if (File::exists($htmlPath)) {
            File::delete($htmlPath);
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
                // Supprimer seulement les fichiers HTML (données dans metadata.json)
                if ($file->getExtension() === 'html' &&
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
