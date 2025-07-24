<?php

namespace FontAwesome\Migrator\Services;

use FontAwesome\Migrator\Services\IconMapper;
use FontAwesome\Migrator\Services\StyleMapper;
use Illuminate\Support\Facades\File;

class IconReplacer
{
    protected IconMapper $iconMapper;
    protected StyleMapper $styleMapper;
    protected FileScanner $fileScanner;
    protected array $config;

    public function __construct(
        IconMapper $iconMapper,
        StyleMapper $styleMapper,
        FileScanner $fileScanner
    ) {
        $this->iconMapper = $iconMapper;
        $this->styleMapper = $styleMapper;
        $this->fileScanner = $fileScanner;
        $this->config = config('fontawesome-migrator');
    }

    /**
     * Traiter une liste de fichiers pour la migration
     */
    public function processFiles(array $files, bool $isDryRun = false): array
    {
        $results = [];

        foreach ($files as $fileInfo) {
            $result = $this->processFile($fileInfo['path'], $isDryRun);
            $result['file'] = $fileInfo['relative_path'];
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Traiter un fichier individuel
     */
    public function processFile(string $filePath, bool $isDryRun = false): array
    {
        $analysis = $this->fileScanner->analyzeFile($filePath);

        if ($analysis['error']) {
            return [
                'success' => false,
                'error' => $analysis['error'],
                'changes' => [],
                'warnings' => []
            ];
        }

        if (empty($analysis['icons'])) {
            return [
                'success' => true,
                'changes' => [],
                'warnings' => []
            ];
        }

        $changes = [];
        $warnings = [];
        $content = $analysis['content'];
        $offset = 0;

        // Trier les icônes par offset décroissant pour éviter les décalages
        $icons = collect($analysis['icons'])->sortByDesc('offset')->values()->all();

        foreach ($icons as $icon) {
            $replacement = $this->getReplacement($icon);

            if ($replacement['warning']) {
                $warnings[] = $replacement['warning'];
            }

            if ($replacement['new_string'] !== $icon['full_match']) {
                $changes[] = [
                    'from' => $icon['full_match'],
                    'to' => $replacement['new_string'],
                    'line' => $icon['line'],
                    'type' => $replacement['type']
                ];

                // Appliquer le remplacement dans le contenu
                $content = substr_replace(
                    $content,
                    $replacement['new_string'],
                    $icon['offset'],
                    strlen($icon['full_match'])
                );
            }
        }

        // Sauvegarder le fichier si ce n'est pas un dry-run
        if (!$isDryRun && !empty($changes)) {
            $this->saveFile($filePath, $content);
        }

        return [
            'success' => true,
            'changes' => $changes,
            'warnings' => $warnings,
            'content' => $content
        ];
    }

    /**
     * Obtenir le remplacement pour une icône donnée
     */
    protected function getReplacement(array $icon): array
    {
        $style = $icon['style'];
        $iconName = $icon['name'];
        $originalString = $icon['full_match'];

        // Mapper le style FA5 vers FA6
        $newStyle = $this->styleMapper->mapStyle($style);

        // Mapper le nom de l'icône si nécessaire
        $mappingResult = $this->iconMapper->mapIcon($iconName, $style);
        $newIconName = $mappingResult['new_name'];

        // Construire la nouvelle chaîne
        $newString = str_replace(
            [$style, $iconName],
            [$newStyle, $newIconName],
            $originalString
        );

        $warning = null;
        $type = 'style_update';

        // Vérifier les cas spéciaux
        if ($mappingResult['deprecated']) {
            $warning = "Icône dépréciée '{$iconName}' remplacée par '{$newIconName}'";
            $type = 'deprecated_icon';
        }

        if ($mappingResult['pro_only'] && $this->config['license_type'] === 'free') {
            $warning = "Icône Pro uniquement '{$iconName}' - fallback vers style " . $this->config['fallback_strategy'];
            $type = 'pro_fallback';
        }

        if (!$mappingResult['found']) {
            $warning = "Icône non trouvée '{$iconName}' - vérification manuelle requise";
            $type = 'manual_review';
        }

        return [
            'new_string' => $newString,
            'warning' => $warning,
            'type' => $type
        ];
    }

    /**
     * Sauvegarder un fichier avec sauvegarde optionnelle
     */
    protected function saveFile(string $filePath, string $content): bool
    {
        // Créer une sauvegarde si configuré
        if ($this->config['backup_files']) {
            $this->createBackup($filePath);
        }

        return File::put($filePath, $content) !== false;
    }

    /**
     * Créer une sauvegarde d'un fichier
     */
    protected function createBackup(string $filePath): bool
    {
        $backupDir = $this->config['backup_path'];

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $relativePath = str_replace(base_path() . '/', '', $filePath);
        $backupPath = $backupDir . '/' . $relativePath . '.backup.' . date('Y-m-d_H-i-s');

        // Créer les dossiers nécessaires
        $backupDirectory = dirname($backupPath);
        if (!File::exists($backupDirectory)) {
            File::makeDirectory($backupDirectory, 0755, true);
        }

        return File::copy($filePath, $backupPath);
    }

    /**
     * Restaurer un fichier depuis sa sauvegarde
     */
    public function restoreFromBackup(string $filePath, ?string $backupTimestamp = null): bool
    {
        $backupDir = $this->config['backup_path'];
        $relativePath = str_replace(base_path() . '/', '', $filePath);

        if ($backupTimestamp) {
            $backupPath = $backupDir . '/' . $relativePath . '.backup.' . $backupTimestamp;
        } else {
            // Trouver la sauvegarde la plus récente
            $pattern = $backupDir . '/' . $relativePath . '.backup.*';
            $backups = glob($pattern);

            if (empty($backups)) {
                return false;
            }

            // Trier par date de modification décroissante
            usort($backups, fn($a, $b) => filemtime($b) <=> filemtime($a));
            $backupPath = $backups[0];
        }

        if (!File::exists($backupPath)) {
            return false;
        }

        return File::copy($backupPath, $filePath);
    }

    /**
     * Lister les sauvegardes disponibles pour un fichier
     */
    public function listBackups(string $filePath): array
    {
        $backupDir = $this->config['backup_path'];
        $relativePath = str_replace(base_path() . '/', '', $filePath);
        $pattern = $backupDir . '/' . $relativePath . '.backup.*';

        $backups = glob($pattern);

        return array_map(function ($backupPath) {
            $timestamp = basename($backupPath);
            $timestamp = str_replace(basename($backupPath, '.backup.*') . '.backup.', '', $timestamp);

            return [
                'path' => $backupPath,
                'timestamp' => $timestamp,
                'created_at' => date('Y-m-d H:i:s', filemtime($backupPath)),
                'size' => filesize($backupPath)
            ];
        }, $backups);
    }

    /**
     * Nettoyer les anciennes sauvegardes
     */
    public function cleanOldBackups(int $daysToKeep = 30): int
    {
        $backupDir = $this->config['backup_path'];

        if (!File::exists($backupDir)) {
            return 0;
        }

        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
        $deleted = 0;

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($backupDir)
        );

        foreach ($iterator as $file) {
            if ($file->isFile() &&
                str_contains($file->getFilename(), '.backup.') &&
                $file->getMTime() < $cutoffTime
            ) {
                if (File::delete($file->getRealPath())) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Valider qu'un remplacement est correct
     */
    protected function validateReplacement(array $icon, string $newString): bool
    {
        // Vérifier que la nouvelle chaîne contient bien les nouveaux préfixes FA6
        $fa6Styles = ['fa-solid', 'fa-regular', 'fa-light', 'fa-brands', 'fa-duotone', 'fa-thin', 'fa-sharp'];

        foreach ($fa6Styles as $style) {
            if (str_contains($newString, $style)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir les statistiques de remplacement
     */
    public function getReplacementStats(array $results): array
    {
        $stats = [
            'total_files' => count($results),
            'modified_files' => 0,
            'total_changes' => 0,
            'changes_by_type' => [],
            'warnings' => 0
        ];

        foreach ($results as $result) {
            if (!empty($result['changes'])) {
                $stats['modified_files']++;
                $stats['total_changes'] += count($result['changes']);

                foreach ($result['changes'] as $change) {
                    $type = $change['type'] ?? 'unknown';
                    $stats['changes_by_type'][$type] = ($stats['changes_by_type'][$type] ?? 0) + 1;
                }
            }

            if (!empty($result['warnings'])) {
                $stats['warnings'] += count($result['warnings']);
            }
        }

        return $stats;
    }
}