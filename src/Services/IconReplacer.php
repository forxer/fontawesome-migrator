<?php

namespace FontAwesome\Migrator\Services;

use Exception;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class IconReplacer
{
    protected array $config;

    public function __construct(
        protected IconMapper $iconMapper,
        protected StyleMapper $styleMapper,
        protected FileScanner $fileScanner
    ) {
        $this->config = config('fontawesome-migrator');
    }

    /**
     * Traiter une liste de fichiers pour la migration
     */
    public function processFiles(array $files, bool $isDryRun = false): array
    {
        $results = [];

        foreach ($files as $filePath) {
            // Gérer les deux formats possibles (string ou array)
            if (\is_array($filePath)) {
                $actualPath = $filePath['path'];
                $relativePath = $filePath['relative_path'] ?? basename((string) $actualPath);
            } else {
                $actualPath = $filePath;
                $relativePath = basename((string) $filePath);
            }

            $result = $this->processFile($actualPath, $isDryRun);
            $result['file'] = $relativePath;
            $results[] = $result;
        }

        return $results;
    }

    /**
     * Traiter un fichier individuel
     */
    public function processFile(string $filePath, bool $isDryRun = false): array
    {
        try {
            if (! File::exists($filePath)) {
                return [
                    'success' => false,
                    'error' => 'File not found: '.$filePath,
                    'changes' => [],
                    'warnings' => [],
                ];
            }

            $content = File::get($filePath);
            $icons = $this->findFontAwesomeIcons($content);

            if ($icons === []) {
                return [
                    'success' => true,
                    'changes' => [],
                    'warnings' => [],
                ];
            }

            $changes = [];
            $warnings = [];
            $modifiedContent = $content;

            // Trier les icônes par offset décroissant pour éviter les décalages
            $icons = collect($icons)->sortByDesc('offset')->values()->all();

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
                        'type' => $replacement['type'],
                    ];

                    // Appliquer le remplacement dans le contenu
                    $modifiedContent = substr_replace(
                        $modifiedContent,
                        $replacement['new_string'],
                        $icon['offset'],
                        \strlen((string) $icon['full_match'])
                    );
                }
            }

            // Sauvegarder le fichier si ce n'est pas un dry-run
            if (! $isDryRun && $changes !== []) {
                $this->saveFile($filePath, $modifiedContent);
            }

            return [
                'success' => true,
                'changes' => $changes,
                'warnings' => $warnings,
                'content' => $modifiedContent,
            ];
        } catch (Exception $exception) {
            return [
                'success' => false,
                'error' => $exception->getMessage(),
                'changes' => [],
                'warnings' => [],
            ];
        }
    }

    /**
     * Trouver les icônes Font Awesome dans le contenu d'un fichier
     */
    protected function findFontAwesomeIcons(string $content): array
    {
        $icons = [];
        $lines = explode("\n", $content);
        $offset = 0;

        // Pattern pour capturer les icônes Font Awesome
        $pattern = '/\b(fas|far|fal|fab|fad|fa-solid|fa-regular|fa-light|fa-brands|fa-duotone|fa-thin|fa-sharp)\s+(fa-[a-zA-Z0-9-]+)\b/';

        foreach ($lines as $lineNumber => $line) {
            if (preg_match_all($pattern, $line, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as $index => $match) {
                    $fullMatch = $match[0];
                    $lineOffset = $match[1];
                    $style = $matches[1][$index][0];
                    $iconName = $matches[2][$index][0];

                    $icons[] = [
                        'full_match' => $fullMatch,
                        'style' => $style,
                        'name' => $iconName,
                        'line' => $lineNumber + 1,
                        'offset' => $offset + $lineOffset,
                    ];
                }
            }

            $offset += \strlen($line) + 1; // +1 pour le \n
        }

        return $icons;
    }

    /**
     * Obtenir le remplacement pour une icône donnée
     */
    protected function getReplacement(array $icon): array
    {
        $style = $icon['style'];
        $iconName = $icon['name'];
        $originalString = $icon['full_match'];

        // Mapper le style FA5 vers FA6 avec fallback selon la licence
        $newStyle = $this->styleMapper->mapStyleWithFallback($style);

        // Mapper le nom de l'icône si nécessaire
        $newIconName = $this->iconMapper->mapIcon($iconName);

        // Construire la nouvelle chaîne
        $newString = str_replace(
            [$style, $iconName],
            [$newStyle, $newIconName],
            $originalString
        );

        $warning = null;
        $type = 'style_update';

        // Vérifier les cas spéciaux
        if ($newIconName !== $iconName) {
            $warning = \sprintf("Icône renommée '%s' → '%s'", $iconName, $newIconName);
            $type = 'renamed_icon';
        }

        if ($this->styleMapper->isProStyle($style) && $this->config['license_type'] === 'free') {
            $warning = \sprintf("Style Pro '%s' → fallback vers '%s'", $style, $newStyle);
            $type = 'pro_fallback';
        }

        return [
            'new_string' => $newString,
            'warning' => $warning,
            'type' => $type,
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

        if (! File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $relativePath = str_replace(base_path().'/', '', $filePath);
        $backupPath = $backupDir.'/'.$relativePath.'.backup.'.date('Y-m-d_H-i-s');

        // Créer les dossiers nécessaires
        $backupDirectory = \dirname($backupPath);

        if (! File::exists($backupDirectory)) {
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
        $relativePath = str_replace(base_path().'/', '', $filePath);

        if ($backupTimestamp !== null && $backupTimestamp !== '' && $backupTimestamp !== '0') {
            $backupPath = $backupDir.'/'.$relativePath.'.backup.'.$backupTimestamp;
        } else {
            // Trouver la sauvegarde la plus récente
            $pattern = $backupDir.'/'.$relativePath.'.backup.*';
            $backups = glob($pattern);

            if ($backups === [] || $backups === false) {
                return false;
            }

            // Trier par date de modification décroissante
            usort($backups, fn ($a, $b): int => filemtime($b) <=> filemtime($a));
            $backupPath = $backups[0];
        }

        if (! File::exists($backupPath)) {
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
        $relativePath = str_replace(base_path().'/', '', $filePath);
        $pattern = $backupDir.'/'.$relativePath.'.backup.*';

        $backups = glob($pattern);

        return array_map(function ($backupPath): array {
            $timestamp = basename($backupPath);
            $timestamp = str_replace(basename($backupPath, '.backup.*').'.backup.', '', $timestamp);

            return [
                'path' => $backupPath,
                'timestamp' => $timestamp,
                'created_at' => date('Y-m-d H:i:s', filemtime($backupPath)),
                'size' => filesize($backupPath),
            ];
        }, $backups);
    }

    /**
     * Nettoyer les anciennes sauvegardes
     */
    public function cleanOldBackups(int $daysToKeep = 30): int
    {
        $backupDir = $this->config['backup_path'];

        if (! File::exists($backupDir)) {
            return 0;
        }

        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
        $deleted = 0;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($backupDir)
        );

        foreach ($iterator as $file) {
            if (! ($file->isFile() && str_contains((string) $file->getFilename(), '.backup.'))) {
                continue;
            }

            if ($file->getMTime() >= $cutoffTime) {
                continue;
            }

            if (! File::delete($file->getRealPath())) {
                continue;
            }

            $deleted++;
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
            'total_files' => \count($results),
            'modified_files' => 0,
            'total_changes' => 0,
            'changes_by_type' => [],
            'warnings' => 0,
        ];

        foreach ($results as $result) {
            if (! empty($result['changes'])) {
                $stats['modified_files']++;
                $stats['total_changes'] += \count($result['changes']);

                foreach ($result['changes'] as $change) {
                    $type = $change['type'] ?? 'unknown';
                    $stats['changes_by_type'][$type] = ($stats['changes_by_type'][$type] ?? 0) + 1;
                }
            }

            if (! empty($result['warnings'])) {
                $stats['warnings'] += \count($result['warnings']);
            }
        }

        return $stats;
    }
}
