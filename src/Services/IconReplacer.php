<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services;

use Exception;
use FontAwesome\Migrator\Contracts\BackupManagerInterface;
use FontAwesome\Migrator\Contracts\ConfigurationInterface;
use FontAwesome\Migrator\Contracts\FileScannerInterface;
use FontAwesome\Migrator\Contracts\VersionMapperInterface;
use Illuminate\Support\Facades\File;

class IconReplacer
{
    public function __construct(
        protected VersionMapperInterface $mapper,
        protected FileScannerInterface $fileScanner,
        protected BackupManagerInterface $backupManager,
        protected ConfigurationInterface $config
    ) {}

    /**
     * Traiter une liste de fichiers pour la migration
     */
    public function processFiles(array $files, bool $isDryRun = false): array
    {
        $results = [];

        foreach ($files as $fileInfo) {
            $actualPath = $fileInfo['path'];
            $relativePath = $fileInfo['relative_path'] ?? basename((string) $actualPath);

            $result = $this->processFile($actualPath, $isDryRun);
            $result['file'] = $actualPath;
            $result['relative_path'] = $relativePath;

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
                $replacement = $this->getIconReplacement($icon);

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

            $backupInfo = null;

            // Sauvegarder le fichier si ce n'est pas un dry-run
            if (! $isDryRun && $changes !== []) {

                $saveResult = $this->saveFile($filePath, $modifiedContent);
                $backupInfo = $saveResult['backup'];
            }

            return [
                'success' => true,
                'changes' => $changes,
                'warnings' => $warnings,
                'content' => $modifiedContent,
                'backup' => $backupInfo,
            ];
        } catch (Exception $exception) {

            return [
                'success' => false,
                'error' => $exception->getMessage(),
                'changes' => [],
                'warnings' => [],
                'backup' => null,
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
    protected function getIconReplacement(array $icon): array
    {
        $style = $icon['style'];
        $iconName = $icon['name'];
        $originalString = $icon['full_match'];

        // Mapper le style avec le nouveau système
        $newStyle = $this->mapper->mapStyle($style);

        // Mapper l'icône avec informations détaillées
        $iconMapping = $this->mapper->mapIcon($iconName, $style);
        $newIconName = $iconMapping['new_name'];

        // Construire la nouvelle chaîne
        $newString = str_replace(
            [$style, $iconName],
            [$newStyle, $newIconName],
            $originalString
        );

        $warning = null;
        $type = 'style_update';

        // Analyser les résultats du mapping pour déterminer le type et warning
        if ($iconMapping['renamed']) {
            $type = 'renamed_icon';
            $warning = \sprintf("Icône renommée '%s' → '%s'", $iconName, $newIconName);
        } elseif ($iconMapping['deprecated']) {
            $type = 'deprecated_icon';
            $warning = \sprintf("Icône dépréciée '%s' → '%s'", $iconName, $newIconName);
        } elseif ($iconMapping['pro_only'] && ! $this->config->isProLicense()) {
            $type = 'pro_fallback';
            $alternative = $this->mapper->getFreeAlternative($iconName);

            if ($alternative !== null && $alternative !== '' && $alternative !== '0') {
                $newIconName = $alternative;
                $newString = str_replace(
                    [$style, $iconName],
                    [$newStyle, $newIconName],
                    $originalString
                );
                $warning = \sprintf("Icône Pro '%s' → alternative Free '%s'", $iconName, $alternative);
            } else {
                $warning = \sprintf("Icône Pro '%s' sans alternative Free disponible", $iconName);
            }
        }

        // Ajouter les warnings du mapper
        if (! empty($iconMapping['warnings'])) {
            $warning = $warning !== null && $warning !== '' && $warning !== '0' ? $warning.' | '.implode(' | ', $iconMapping['warnings']) : implode(' | ', $iconMapping['warnings']);
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
    protected function saveFile(string $filePath, string $content): array
    {
        $backupInfo = null;

        // Créer une sauvegarde si configuré
        if ($this->config->isBackupEnabled()) {
            $backupResult = $this->createBackup($filePath);

            if (\is_array($backupResult)) {
                $backupInfo = $backupResult;
            }
        }

        $success = File::put($filePath, $content) !== false;

        return [
            'success' => $success,
            'backup' => $backupInfo,
        ];
    }

    /**
     * Créer une sauvegarde d'un fichier
     */
    protected function createBackup(string $filePath): array|bool
    {
        return $this->backupManager->createBackup($filePath);
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
