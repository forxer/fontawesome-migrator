<?php

namespace FontAwesome\Migrator\Http\Controllers;

use FontAwesome\Migrator\Services\MetadataManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;

/**
 * Contrôleur pour la gestion des sessions de migration
 */
class SessionsController extends Controller
{
    /**
     * Afficher la liste des sessions
     */
    public function index()
    {
        $sessions = MetadataManager::getAvailableSessions();
        $stats = $this->getSessionStats($sessions);

        return view('fontawesome-migrator::sessions.index', [
            'sessions' => $sessions,
            'stats' => $stats,
        ]);
    }

    /**
     * Afficher une session spécifique
     */
    public function show(string $sessionId)
    {
        $baseBackupDir = config('fontawesome-migrator.sessions_path');
        $sessionDir = $baseBackupDir.'/session-'.$sessionId;

        if (! File::exists($sessionDir)) {
            abort(404, 'Session non trouvée');
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

        return view('fontawesome-migrator::sessions.show', [
            'sessionId' => $sessionId,
            'sessionDir' => $sessionDir,
            'metadata' => $metadata,
            'backupFiles' => $backupFiles,
        ]);
    }

    /**
     * Supprimer une session
     */
    public function destroy(string $sessionId)
    {
        $baseBackupDir = config('fontawesome-migrator.sessions_path');
        $sessionDir = $baseBackupDir.'/session-'.$sessionId;

        if (! File::exists($sessionDir)) {
            return response()->json(['error' => 'Session non trouvée'], 404);
        }

        if (File::deleteDirectory($sessionDir)) {
            return response()->json(['message' => 'Session supprimée avec succès']);
        }

        return response()->json(['error' => 'Erreur lors de la suppression'], 500);
    }

    /**
     * Nettoyer les anciennes sessions
     */
    public function cleanup(Request $request)
    {
        $days = $request->input('days', 30);
        $deleted = MetadataManager::cleanOldSessions($days);

        return response()->json([
            'message' => 'Nettoyage des sessions terminé',
            'deleted' => $deleted,
            'days' => $days,
        ]);
    }

    /**
     * Obtenir les statistiques des sessions
     */
    protected function getSessionStats(array $sessions): array
    {
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
