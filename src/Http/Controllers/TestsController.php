<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Http\Controllers;

use Exception;
use FontAwesome\Migrator\Services\Core\MigrationVersionManager;
use FontAwesome\Migrator\Services\Metadata\MetadataManager;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

/**
 * Contrôleur pour les tests et debug
 */
class TestsController extends Controller
{
    public function __construct(
        private readonly MigrationVersionManager $versionManager
    ) {}

    /**
     * Afficher la page d'index des tests
     */
    public function index()
    {
        $sessions = MetadataManager::getAvailableMigrations();
        $backupStats = $this->getBackupStats();

        // Ajouter les informations de migration multi-versions
        $supportedMigrations = $this->versionManager->getSupportedMigrations();

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
        ]);

        try {
            $commandOptions = [
                '--no-interactive' => true,
                '--debug' => true,
                '--web-interface' => true, // Marquer que la migration vient de l'interface web
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

            // Ajouter l'option dry-run
            if ($request->boolean('dry_run', true)) {
                $commandOptions['--dry-run'] = true;
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
     * Inspecter une migration spécifique
     */
    public function inspectMigration(string $migrationId)
    {
        $baseBackupDir = config('fontawesome-migrator.migrations_path');
        $migrationDir = $baseBackupDir.'/migration-'.$migrationId;

        if (! File::exists($migrationDir)) {
            return response()->json(['error' => 'Migration non trouvée'], 404);
        }

        $metadataPath = $migrationDir.'/metadata.json';
        $metadata = [];

        if (File::exists($metadataPath)) {
            $metadata = json_decode(File::get($metadataPath), true);
        }

        $files = File::files($migrationDir);
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
            'session_id' => $migrationId,
            'session_dir' => $migrationDir,
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
        $baseBackupDir = config('fontawesome-migrator.migrations_path');

        if (! File::exists($baseBackupDir)) {
            return [
                'total_migrations' => 0,
                'total_backups' => 0,
                'total_size' => 0,
                'last_session' => null,
            ];
        }

        $sessions = MetadataManager::getAvailableMigrations();
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
            'total_migrations' => \count($sessions),
            'total_backups' => $totalBackups,
            'total_size' => $totalSize,
            'last_migration' => $sessions[0] ?? null,
        ];
    }
}
