<?php

namespace FontAwesome\Migrator\Http\Controllers;

use FontAwesome\Migrator\Services\MigrationReporter;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ReportsController extends Controller
{
    /**
     * Afficher la liste des rapports
     */
    public function index()
    {
        $reportPath = config('fontawesome-migrator.report_path');
        $reports = [];

        if (File::exists($reportPath)) {
            $files = File::files($reportPath);

            foreach ($files as $file) {
                if ($file->getExtension() === 'html') {
                    $relativePath = str_replace(storage_path('app/public'), '', $file->getRealPath());
                    $jsonPath = str_replace('.html', '.json', $file->getRealPath());
                    $jsonRelativePath = str_replace(storage_path('app/public'), '', $jsonPath);

                    $reports[] = [
                        'name' => $file->getFilenameWithoutExtension(),
                        'filename' => $file->getFilename(),
                        'created_at' => $file->getMTime(),
                        'size' => $file->getSize(),
                        'html_url' => Storage::url($relativePath),
                        'json_url' => File::exists($jsonPath) ? Storage::url($jsonRelativePath) : null,
                        'html_path' => $file->getRealPath(),
                        'json_path' => $jsonPath,
                    ];
                }
            }

            // Trier par date de création (plus récent en premier)
            usort($reports, fn ($a, $b): int => $b['created_at'] <=> $a['created_at']);
        }

        return view('fontawesome-migrator::reports.index', ['reports' => $reports]);
    }

    /**
     * Afficher un rapport spécifique
     */
    public function show(Request $request, string $filename)
    {
        $reportPath = config('fontawesome-migrator.report_path');
        $filePath = $reportPath.'/'.$filename;

        if (! File::exists($filePath)) {
            abort(404, 'Rapport non trouvé');
        }

        // Si c'est un fichier JSON, retourner en JSON
        if (pathinfo($filename, PATHINFO_EXTENSION) === 'json') {
            return response()->json(json_decode(File::get($filePath), true));
        }

        // Pour les fichiers HTML, utiliser la vue Blade partagée
        $jsonFilename = str_replace('.html', '.json', $filename);
        $jsonPath = $reportPath.'/'.$jsonFilename;

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
                'timestamp' => date('Y-m-d H:i:s', filemtime($filePath)),
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

            return view('fontawesome-migrator::reports.migration', $viewData);
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
                date('Y-m-d H:i:s', strtotime((string) $jsonData['meta']['generated_at'])) :
                date('Y-m-d H:i:s'),
            'isDryRun' => $jsonData['meta']['dry_run'] ?? false,
            'migrationOptions' => $jsonData['meta']['migration_options'] ?? [],
            'configuration' => $jsonData['meta']['configuration'] ?? [],
            'packageVersion' => $jsonData['meta']['package_version'] ?? '1.1.0',
            'enrichedWarnings' => $enrichedWarnings,
        ];

        return view('fontawesome-migrator::reports.migration', $viewData);
    }

    /**
     * Supprimer un rapport
     */
    public function destroy(string $filename)
    {
        $reportPath = config('fontawesome-migrator.report_path');
        $htmlPath = $reportPath.'/'.$filename;
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

        if ($deleted === 0) {
            return response()->json(['error' => 'Rapport non trouvé'], 404);
        }

        return response()->json(['message' => 'Rapport supprimé avec succès']);
    }

    /**
     * Nettoyer les anciens rapports
     */
    public function cleanup(Request $request)
    {
        $days = $request->input('days', 30);
        $reportPath = config('fontawesome-migrator.report_path');

        if (! File::exists($reportPath)) {
            return response()->json(['message' => 'Aucun rapport à nettoyer']);
        }

        $cutoffTime = time() - ($days * 24 * 60 * 60);
        $deleted = 0;

        $files = File::files($reportPath);

        foreach ($files as $file) {
            if ($file->getMTime() < $cutoffTime) {
                File::delete($file->getRealPath());
                $deleted++;
            }
        }

        return response()->json([
            'message' => 'Nettoyage terminé',
            'deleted' => $deleted,
            'days' => $days,
        ]);
    }
}
