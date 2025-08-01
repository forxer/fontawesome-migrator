<?php

namespace FontAwesome\Migrator\Http\Controllers;

use Exception;
use FontAwesome\Migrator\Services\MetadataManager;
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

        return view('fontawesome-migrator::tests.index', [
            'sessions' => $sessions,
            'backupStats' => $backupStats,
        ]);
    }

    /**
     * Exécuter une migration de test
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
