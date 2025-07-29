<?php

namespace FontAwesome\Migrator\Commands;

use FontAwesome\Migrator\Services\IconReplacer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\note;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\search;
use function Laravel\Prompts\select;
use function Laravel\Prompts\table;
use function Laravel\Prompts\text;
use function Laravel\Prompts\warning;

class BackupCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fontawesome:backup
                            {action? : Action à effectuer (list, restore, clean, info)}
                            {--file= : Fichier spécifique pour les actions restore/info}
                            {--timestamp= : Timestamp spécifique pour la restauration}
                            {--days=30 : Nombre de jours à conserver lors du nettoyage}
                            {--dry-run : Prévisualiser les actions sans les appliquer}
                            {--no-interactive : Désactiver le mode interactif}';

    /**
     * The console command description.
     */
    protected $description = 'Gérer les sauvegardes créées par FontAwesome Migrator (lister, restaurer, nettoyer)';

    public function __construct(
        protected IconReplacer $iconReplacer,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Mode interactif par défaut, sauf si --no-interactive est spécifié
        if (! $this->option('no-interactive')) {
            return $this->handleInteractive();
        }

        // Mode classique avec options de ligne de commande
        return $this->handleClassic();
    }

    /**
     * Mode interactif avec Laravel Prompts
     */
    protected function handleInteractive(): int
    {
        intro('🗂️ FontAwesome Migrator - Gestionnaire de Sauvegardes');

        $backupDir = config('fontawesome-migrator.backup_path');

        if (! File::exists($backupDir)) {
            warning('Aucun répertoire de sauvegarde trouvé.');
            note('Répertoire attendu : '.$backupDir);
            outro('❌ Aucune sauvegarde disponible');

            return Command::SUCCESS;
        }

        $backups = $this->getAllBackups();

        if ($backups === []) {
            warning('Aucune sauvegarde trouvée.');
            outro('💡 Effectuez une migration pour créer des sauvegardes');

            return Command::SUCCESS;
        }

        info(\sprintf('📊 %d sauvegarde(s) trouvée(s)', \count($backups)));

        $action = select(
            'Que souhaitez-vous faire ?',
            [
                'list' => '📋 Lister toutes les sauvegardes',
                'restore' => '🔄 Restaurer un fichier',
                'clean' => '🧹 Nettoyer les anciennes sauvegardes',
                'info' => '🔍 Informations détaillées',
            ]
        );

        return match ($action) {
            'list' => $this->listBackups($backups),
            'restore' => $this->interactiveRestore($backups),
            'clean' => $this->interactiveClean(),
            'info' => $this->interactiveInfo($backups),
            default => Command::SUCCESS,
        };
    }

    /**
     * Mode classique avec options de ligne de commande
     */
    protected function handleClassic(): int
    {
        $action = $this->argument('action') ?? 'list';

        return match ($action) {
            'list' => $this->handleList(),
            'restore' => $this->handleRestore(),
            'clean' => $this->handleClean(),
            'info' => $this->handleInfo(),
            default => $this->handleList(),
        };
    }

    /**
     * Lister toutes les sauvegardes
     */
    protected function handleList(): int
    {
        $backups = $this->getAllBackups();

        if ($backups === []) {
            $this->warn('Aucune sauvegarde trouvée.');

            return Command::SUCCESS;
        }

        return $this->listBackups($backups);
    }

    /**
     * Restaurer un fichier depuis sa sauvegarde
     */
    protected function handleRestore(): int
    {
        $filePath = $this->option('file');
        $timestamp = $this->option('timestamp');

        if (! $filePath) {
            $this->error('Option --file requise pour la restauration');

            return Command::FAILURE;
        }

        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Mode DRY-RUN - Aucune restauration ne sera effectuée');
        }

        $success = $this->restoreFile($filePath, $timestamp, $isDryRun);

        if ($success) {
            $message = $isDryRun ? 'Restauration simulée avec succès' : 'Fichier restauré avec succès';
            $this->info($message);

            return Command::SUCCESS;
        }

        $this->error('Échec de la restauration');

        return Command::FAILURE;
    }

    /**
     * Nettoyer les anciennes sauvegardes
     */
    protected function handleClean(): int
    {
        $days = (int) $this->option('days');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn('Mode DRY-RUN - Aucune suppression ne sera effectuée');
        }

        $deleted = $this->cleanOldBackups($days, $isDryRun);

        $message = $isDryRun
            ? \sprintf('%d sauvegarde(s) seraient supprimées', $deleted)
            : \sprintf('%d sauvegarde(s) supprimée(s)', $deleted);

        $this->info($message);

        return Command::SUCCESS;
    }

    /**
     * Afficher des informations détaillées
     */
    protected function handleInfo(): int
    {
        $backups = $this->getAllBackups();

        if ($backups === []) {
            $this->warn('Aucune sauvegarde trouvée.');

            return Command::SUCCESS;
        }

        return $this->showBackupInfo($backups);
    }

    /**
     * Restauration interactive
     */
    protected function interactiveRestore(array $backups): int
    {
        // Grouper les sauvegardes par fichier original
        $fileGroups = $this->groupBackupsByFile($backups);

        $fileOptions = [];

        foreach ($fileGroups as $originalFile => $fileBackups) {
            $count = \count($fileBackups);
            $latest = max(array_column($fileBackups, 'timestamp'));
            $fileOptions[$originalFile] = \sprintf('%s (%d sauvegarde(s), dernière: %s)',
                $originalFile, $count, $latest);
        }

        $selectedFile = search(
            'Sélectionnez le fichier à restaurer',
            fn ($value): array => array_filter(
                $fileOptions,
                fn ($label, $file): bool => str_contains(strtolower((string) $file), strtolower((string) $value)) ||
                                      str_contains(strtolower((string) $label), strtolower((string) $value)),
                ARRAY_FILTER_USE_BOTH
            )
        );

        if ($selectedFile === 0 || ($selectedFile === '' || $selectedFile === '0')) {
            outro('❌ Annulé');

            return Command::SUCCESS;
        }

        $fileBackups = $fileGroups[$selectedFile];

        // Sélectionner le timestamp
        $timestampOptions = [];

        foreach ($fileBackups as $backup) {
            $size = $this->formatFileSize($backup['size']);
            $timestampOptions[$backup['timestamp']] = \sprintf('%s (%s)',
                $backup['created_at'], $size);
        }

        $selectedTimestamp = select(
            'Sélectionnez la sauvegarde à restaurer',
            $timestampOptions,
            default: array_key_first($timestampOptions)
        );

        $isDryRun = confirm('Mode prévisualisation (dry-run) ?', false);

        if ($isDryRun) {
            warning('Mode DRY-RUN - Aucune restauration ne sera effectuée');
        }

        if (! $isDryRun && ! confirm(\sprintf('Confirmer la restauration de %s ?', basename($selectedFile)), false)) {
            outro('❌ Restauration annulée');

            return Command::SUCCESS;
        }

        $success = $this->restoreFile($selectedFile, $selectedTimestamp, $isDryRun);

        if ($success) {
            $message = $isDryRun ? '✅ Restauration simulée avec succès' : '✅ Fichier restauré avec succès';
            outro($message);
        } else {
            outro('❌ Échec de la restauration');
        }

        return $success ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Nettoyage interactif
     */
    protected function interactiveClean(): int
    {
        $days = (int) text(
            'Nombre de jours à conserver',
            default: '30',
            validate: fn ($value): ?string => is_numeric($value) && (int) $value > 0
                ? null
                : 'Veuillez entrer un nombre positif'
        );

        $isDryRun = confirm('Mode prévisualisation (dry-run) ?', true);

        if ($isDryRun) {
            warning('Mode DRY-RUN - Aucune suppression ne sera effectuée');
        }

        $deleted = $this->cleanOldBackups($days, $isDryRun);

        if ($deleted === 0) {
            info('Aucune sauvegarde à nettoyer');
        } else {
            $message = $isDryRun
                ? \sprintf('📊 %d sauvegarde(s) seraient supprimées', $deleted)
                : \sprintf('🧹 %d sauvegarde(s) supprimée(s)', $deleted);
            info($message);
        }

        outro('✅ Nettoyage terminé');

        return Command::SUCCESS;
    }

    /**
     * Informations interactives
     */
    protected function interactiveInfo(array $backups): int
    {
        $this->showBackupInfo($backups);
        outro('✅ Informations affichées');

        return Command::SUCCESS;
    }

    /**
     * Lister les sauvegardes avec tableau formaté
     */
    protected function listBackups(array $backups): int
    {
        $this->info('📋 Liste des sauvegardes :');
        $this->newLine();

        $headers = ['Fichier Original', 'Timestamp', 'Date de Création', 'Taille'];
        $rows = [];

        foreach ($backups as $backup) {
            $rows[] = [
                $backup['relative_path'],
                $backup['timestamp'],
                $backup['created_at'],
                $this->formatFileSize($backup['size']),
            ];
        }

        table($headers, $rows);

        return Command::SUCCESS;
    }

    /**
     * Afficher les informations détaillées des sauvegardes
     */
    protected function showBackupInfo(array $backups): int
    {
        $totalSize = array_sum(array_column($backups, 'size'));
        $fileCount = \count(array_unique(array_column($backups, 'relative_path')));

        $info = [
            '📊 Statistiques des sauvegardes :',
            '',
            \sprintf('• Nombre total de sauvegardes : %d', \count($backups)),
            \sprintf('• Nombre de fichiers sauvegardés : %d', $fileCount),
            \sprintf('• Taille totale : %s', $this->formatFileSize($totalSize)),
            \sprintf('• Répertoire : %s', config('fontawesome-migrator.backup_path')),
        ];

        note(implode("\n", $info));

        // Afficher les 5 sauvegardes les plus récentes
        $recent = \array_slice($backups, 0, 5);
        $this->newLine();
        $this->info('🕒 5 sauvegardes les plus récentes :');

        foreach ($recent as $backup) {
            $this->line(\sprintf('   • %s (%s) - %s',
                basename((string) $backup['relative_path']),
                $backup['created_at'],
                $this->formatFileSize($backup['size'])
            ));
        }

        return Command::SUCCESS;
    }

    /**
     * Obtenir toutes les sauvegardes disponibles
     */
    protected function getAllBackups(): array
    {
        $backupDir = config('fontawesome-migrator.backup_path');

        if (! File::exists($backupDir)) {
            return [];
        }

        $backups = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($backupDir)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            if (! str_contains((string) $file->getFilename(), '.backup.')) {
                continue;
            }
            $filepath = $file->getRealPath();
            $filename = $file->getFilename();

            // Extraire les informations depuis le nom de fichier
            if (preg_match('/^(.+)\.backup\.(\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})$/', (string) $filename, $matches)) {
                $relativePath = str_replace('_', '/', $matches[1]);
                $timestamp = $matches[2];

                $backups[] = [
                    'backup_path' => $filepath,
                    'relative_path' => $relativePath,
                    'timestamp' => $timestamp,
                    'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                    'size' => $file->getSize(),
                ];
            }
        }

        // Trier par date de création décroissante
        usort($backups, fn ($a, $b): int => strcmp((string) $b['timestamp'], (string) $a['timestamp']));

        return $backups;
    }

    /**
     * Grouper les sauvegardes par fichier original
     */
    protected function groupBackupsByFile(array $backups): array
    {
        $groups = [];

        foreach ($backups as $backup) {
            $originalFile = $backup['relative_path'];

            if (! isset($groups[$originalFile])) {
                $groups[$originalFile] = [];
            }

            $groups[$originalFile][] = $backup;
        }

        return $groups;
    }

    /**
     * Restaurer un fichier depuis sa sauvegarde
     */
    protected function restoreFile(string $filePath, ?string $timestamp = null, bool $isDryRun = false): bool
    {
        if ($isDryRun) {
            $this->line(\sprintf('🔄 [DRY-RUN] Restauration de %s', $filePath));

            if ($timestamp !== null && $timestamp !== '' && $timestamp !== '0') {
                $this->line(\sprintf('   Timestamp : %s', $timestamp));
            }

            return true;
        }

        return $this->iconReplacer->restoreFromBackup($filePath, $timestamp);
    }

    /**
     * Nettoyer les anciennes sauvegardes
     */
    protected function cleanOldBackups(int $daysToKeep, bool $isDryRun = false): int
    {
        if ($isDryRun) {
            // Simuler le nettoyage
            $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
            $backups = $this->getAllBackups();
            $toDelete = 0;

            foreach ($backups as $backup) {
                $backupTime = strtotime((string) $backup['created_at']);

                if ($backupTime < $cutoffTime) {
                    $toDelete++;
                    $this->line(\sprintf('🗑️ [DRY-RUN] Suppression : %s', $backup['backup_path']));
                }
            }

            return $toDelete;
        }

        return $this->iconReplacer->cleanOldBackups($daysToKeep);
    }

    /**
     * Formater la taille d'un fichier
     */
    protected function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $index = 0;
        $size = $bytes;

        while ($size >= 1024 && $index < \count($units) - 1) {
            $size /= 1024;
            $index++;
        }

        return \sprintf('%.1f %s', $size, $units[$index]);
    }
}
