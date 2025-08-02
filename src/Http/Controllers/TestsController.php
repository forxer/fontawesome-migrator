<?php

namespace FontAwesome\Migrator\Http\Controllers;

use Exception;
use FontAwesome\Migrator\Services\MetadataManager;
use FontAwesome\Migrator\Services\MigrationVersionManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/**
 * Contrôleur pour les tests et debug
 */
class TestsController extends Controller
{
    /**
     * Afficher la page d'index des tests
     */
    public function index()
    {
        $sessions = MetadataManager::getAvailableSessions();
        $backupStats = $this->getBackupStats();

        // Ajouter les informations de migration multi-versions
        $versionManager = new MigrationVersionManager();
        $supportedMigrations = $versionManager->getSupportedMigrations();

        return view('fontawesome-migrator::tests.index', [
            'sessions' => $sessions,
            'backupStats' => $backupStats,
            'supportedMigrations' => $supportedMigrations,
        ]);
    }

    /**
     * Exécuter une migration multi-versions
     */
    public function runMultiVersionMigration(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'string', 'in:4,5,6'],
            'to' => ['nullable', 'string', 'in:5,6,7'],
            'mode' => ['required', 'string', 'in:complete,icons-only,assets-only'],
            'dry_run' => ['boolean'],
            'report' => ['boolean'],
        ]);

        try {
            $commandOptions = [
                '--no-interactive' => true,
                '--debug' => true,
            ];

            // Ajouter les options de version si spécifiées
            if ($request->filled('from')) {
                $commandOptions['--from'] = $request->input('from');
            }

            if ($request->filled('to')) {
                $commandOptions['--to'] = $request->input('to');
            }

            // Ajouter les options selon le mode
            switch ($request->input('mode')) {
                case 'icons-only':
                    $commandOptions['--icons-only'] = true;
                    break;

                case 'assets-only':
                    $commandOptions['--assets-only'] = true;
                    break;
                    // 'complete' ne nécessite pas d'option spéciale
            }

            // Ajouter les options dry-run et report
            if ($request->boolean('dry_run', true)) {
                $commandOptions['--dry-run'] = true;
            }

            if ($request->boolean('report', true)) {
                $commandOptions['--report'] = true;
            }

            // Construire la commande complète pour affichage
            $commandString = 'php artisan fontawesome:migrate';

            foreach ($commandOptions as $option => $value) {
                if ($value === true) {
                    $commandString .= ' '.$option;
                } elseif ($value !== false && $value !== null) {
                    $commandString .= ' '.$option.'='.$value;
                }
            }

            // Forcer le répertoire de travail à la racine du projet Laravel
            $originalCwd = getcwd();
            chdir(base_path());

            $exitCode = Artisan::call('fontawesome:migrate', $commandOptions);
            $output = Artisan::output();

            // Restaurer le répertoire de travail original
            chdir($originalCwd);

            return response()->json([
                'success' => $exitCode === 0,
                'exit_code' => $exitCode,
                'command' => $commandString,
                'options' => $commandOptions,
                'output' => $output,
                'from_version' => $request->input('from'),
                'to_version' => $request->input('to'),
                'mode' => $request->input('mode'),
                'timestamp' => date('Y-m-d H:i:s'),
            ]);

        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
                'timestamp' => date('Y-m-d H:i:s'),
            ], 500);
        }
    }

    /**
     * Exécuter une migration de test (legacy)
     */
    public function runMigration(Request $request)
    {
        $type = $request->input('type', 'dry-run');

        try {
            $exitCode = 0;
            $commandOptions = [];

            switch ($type) {
                case 'dry-run':
                    $commandOptions = [
                        '--dry-run' => true,
                        '--report' => true,
                        '--no-interactive' => true,
                        '--debug' => true,
                    ];
                    break;

                case 'real':
                    $commandOptions = [
                        '--report' => true,
                        '--no-interactive' => true,
                        '--debug' => true,
                    ];
                    break;

                case 'icons-only':
                    $commandOptions = [
                        '--icons-only' => true,
                        '--dry-run' => true,
                        '--report' => true,
                        '--no-interactive' => true,
                        '--debug' => true,
                    ];
                    break;

                case 'assets-only':
                    $commandOptions = [
                        '--assets-only' => true,
                        '--dry-run' => true,
                        '--report' => true,
                        '--no-interactive' => true,
                        '--debug' => true,
                    ];
                    break;
            }

            // Construire la commande complète pour affichage
            $commandString = 'php artisan fontawesome:migrate';

            foreach ($commandOptions as $option => $value) {
                if ($value) {
                    $commandString .= ' '.$option;
                } elseif ($value !== false) {
                    $commandString .= ' '.$option.'='.$value;
                }
            }

            // Forcer le répertoire de travail à la racine du projet Laravel
            $originalCwd = getcwd();
            chdir(base_path());

            $exitCode = Artisan::call('fontawesome:migrate', $commandOptions);
            $output = Artisan::output();

            // Restaurer le répertoire de travail original
            chdir($originalCwd);

            return response()->json([
                'success' => $exitCode === 0,
                'exit_code' => $exitCode,
                'command' => $commandString,
                'options' => $commandOptions,
                'output' => $output,
                'type' => $type,
                'sessions' => MetadataManager::getAvailableSessions(),
                'timestamp' => date('Y-m-d H:i:s'),
            ]);

        } catch (Exception $exception) {
            return response()->json([
                'success' => false,
                'error' => $exception->getMessage(),
                'type' => $type,
                'timestamp' => date('Y-m-d H:i:s'),
            ], 500);
        }
    }

    /**
     * Inspecter une session spécifique
     */
    public function inspectSession(string $sessionId)
    {
        $baseBackupDir = config('fontawesome-migrator.sessions_path');
        $sessionDir = $baseBackupDir.'/session-'.$sessionId;

        if (! File::exists($sessionDir)) {
            return response()->json(['error' => 'Session non trouvée'], 404);
        }

        $metadataPath = $sessionDir.'/metadata.json';
        $metadata = [];

        if (File::exists($metadataPath)) {
            $metadata = json_decode(File::get($metadataPath), true);
        }

        $files = File::files($sessionDir);
        $backupFiles = [];

        foreach ($files as $file) {
            if ($file->getFilename() !== 'metadata.json' && $file->getFilename() !== '.gitignore') {
                $backupFiles[] = [
                    'name' => $file->getFilename(),
                    'size' => $file->getSize(),
                    'modified' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        return response()->json([
            'session_id' => $sessionId,
            'session_dir' => $sessionDir,
            'metadata' => $metadata,
            'backup_files' => $backupFiles,
            'files_count' => \count($backupFiles),
        ]);
    }

    /**
     * Nettoyer les sessions de test
     */
    public function cleanupSessions(Request $request)
    {
        $days = $request->input('days', 7);
        $deleted = MetadataManager::cleanOldSessions($days);

        return response()->json([
            'message' => 'Nettoyage des sessions terminé',
            'deleted' => $deleted,
            'days' => $days,
        ]);
    }

    /**
     * Obtenir les statistiques des sauvegardes pour le test
     */
    protected function getBackupStats(): array
    {
        $baseBackupDir = config('fontawesome-migrator.sessions_path');

        if (! File::exists($baseBackupDir)) {
            return [
                'total_sessions' => 0,
                'total_backups' => 0,
                'total_size' => 0,
                'last_session' => null,
            ];
        }

        $sessions = MetadataManager::getAvailableSessions();
        $totalBackups = 0;
        $totalSize = 0;

        foreach ($sessions as $session) {
            $totalBackups += $session['backup_count'];

            // Calculer la taille totale des fichiers
            $files = File::files($session['directory']);

            foreach ($files as $file) {
                $totalSize += $file->getSize();
            }
        }

        return [
            'total_sessions' => \count($sessions),
            'total_backups' => $totalBackups,
            'total_size' => $totalSize,
            'last_session' => $sessions[0] ?? null,
        ];
    }
}
