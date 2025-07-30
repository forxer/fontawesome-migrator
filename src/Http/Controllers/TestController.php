<?php

namespace FontAwesome\Migrator\Http\Controllers;

use Exception;
use FontAwesome\Migrator\Services\MetadataManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/**
 * Contrôleur pour le panneau de test et debug
 */
class TestController extends Controller
{
    /**
     * Afficher le panneau de test
     */
    public function panel()
    {
        $sessions = MetadataManager::getAvailableSessions();
        $backupStats = $this->getBackupStats();

        return view('fontawesome-migrator::test.panel', [
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

            switch ($type) {
                case 'dry-run':
                    $exitCode = Artisan::call('fontawesome:migrate', [
                        '--dry-run' => true,
                        '--report' => true,
                        '--no-interactive' => true,
                    ]);
                    break;

                case 'real':
                    $exitCode = Artisan::call('fontawesome:migrate', [
                        '--report' => true,
                        '--no-interactive' => true,
                    ]);
                    break;

                case 'icons-only':
                    $exitCode = Artisan::call('fontawesome:migrate', [
                        '--icons-only' => true,
                        '--dry-run' => true,
                        '--report' => true,
                        '--no-interactive' => true,
                    ]);
                    break;

                case 'assets-only':
                    $exitCode = Artisan::call('fontawesome:migrate', [
                        '--assets-only' => true,
                        '--dry-run' => true,
                        '--report' => true,
                        '--no-interactive' => true,
                    ]);
                    break;
            }

            $output = Artisan::output();

            return response()->json([
                'success' => $exitCode === 0,
                'exit_code' => $exitCode,
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
