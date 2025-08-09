<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Core;

use Exception;
use FontAwesome\Migrator\Contracts\BackupManagerInterface;
use FontAwesome\Migrator\Contracts\ConfigurationInterface;
use FontAwesome\Migrator\Contracts\FileScannerInterface;
use FontAwesome\Migrator\Contracts\VersionMapperInterface;
use FontAwesome\Migrator\Services\Configuration\FontAwesomePatternService;
use Illuminate\Support\Facades\File;

class IconReplacer
{
    public function __construct(
        protected VersionMapperInterface $mapper,
        protected FileScannerInterface $fileScanner,
        protected BackupManagerInterface $backupManager,
        protected ConfigurationInterface $config,
        protected FontAwesomePatternService $patternService
    ) {}

    /**
     * Changer le mapper de version (pour migrations dynamiques)
     */
    public function setMapper(VersionMapperInterface $mapper): void
    {
        $this->mapper = $mapper;
    }

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
            $fileValidation = $this->validateFile($filePath);

            if (! $fileValidation['valid']) {
                return $fileValidation['result'];
            }

            $content = $fileValidation['content'];
            $icons = $this->patternService->extractIconsWithPositions($content);

            if ($icons === []) {
                return $this->buildEmptyResult();
            }

            $processResult = $this->processIconReplacements($content, $icons);
            $backupInfo = $this->handleFileSave($filePath, $processResult['content'], $processResult['changes'], $isDryRun);

            return $this->buildSuccessResult($processResult, $backupInfo);

        } catch (Exception $exception) {
            return $this->buildErrorResult($exception);
        }
    }

    /**
     * Valider l'existence et lisibilité du fichier
     */
    private function validateFile(string $filePath): array
    {
        if (! File::exists($filePath)) {
            return [
                'valid' => false,
                'result' => [
                    'success' => false,
                    'error' => 'File not found: '.$filePath,
                    'changes' => [],
                    'warnings' => [],
                ],
            ];
        }

        return [
            'valid' => true,
            'content' => File::get($filePath),
        ];
    }

    /**
     * Construire résultat vide quand aucune icône trouvée
     */
    private function buildEmptyResult(): array
    {
        return [
            'success' => true,
            'changes' => [],
            'changes_count' => 0,
            'warnings' => [],
        ];
    }

    /**
     * Traiter tous les remplacements d'icônes dans le contenu
     */
    private function processIconReplacements(string $content, array $icons): array
    {
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

                $modifiedContent = $this->applyReplacement($modifiedContent, $icon, $replacement['new_string']);
            }
        }

        return [
            'content' => $modifiedContent,
            'changes' => $changes,
            'warnings' => $warnings,
        ];
    }

    /**
     * Appliquer un remplacement dans le contenu
     */
    private function applyReplacement(string $content, array $icon, string $newString): string
    {
        return substr_replace(
            $content,
            $newString,
            $icon['offset'],
            \strlen((string) $icon['full_match'])
        );
    }

    /**
     * Gérer la sauvegarde conditionnelle du fichier
     */
    private function handleFileSave(string $filePath, string $content, array $changes, bool $isDryRun): ?array
    {
        if ($isDryRun || $changes === []) {
            return null;
        }

        $saveResult = $this->saveFile($filePath, $content);

        return $saveResult['backup'];
    }

    /**
     * Construire résultat de succès
     */
    private function buildSuccessResult(array $processResult, ?array $backupInfo): array
    {
        return [
            'success' => true,
            'changes' => $processResult['changes'],
            'changes_count' => \count($processResult['changes']),
            'warnings' => $processResult['warnings'],
            'content' => $processResult['content'],
            'backup' => $backupInfo,
        ];
    }

    /**
     * Construire résultat d'erreur
     */
    private function buildErrorResult(Exception $exception): array
    {
        return [
            'success' => false,
            'error' => $exception->getMessage(),
            'changes' => [],
            'changes_count' => 0,
            'warnings' => [],
            'backup' => null,
        ];
    }

    /**
     * Obtenir le remplacement pour une icône donnée
     */
    protected function getIconReplacement(array $icon): array
    {
        $mappingData = $this->prepareMappingData($icon);
        $replacementResult = $this->determineReplacementType($mappingData);

        $finalWarning = $this->buildFinalWarning(
            $replacementResult['warning'],
            $mappingData['iconMapping']['warnings']
        );

        return [
            'new_string' => $replacementResult['new_string'],
            'warning' => $finalWarning,
            'type' => $replacementResult['type'],
        ];
    }

    /**
     * Préparer les données de mapping pour une icône
     */
    private function prepareMappingData(array $icon): array
    {
        $style = $icon['style'];
        $iconName = $icon['name'];
        $originalString = $icon['full_match'];

        $newStyle = $this->mapper->mapStyle($style);
        $iconMapping = $this->mapper->mapIcon($iconName, $style);
        $newIconName = $iconMapping['new_name'];

        $baseNewString = str_replace(
            [$style, $iconName],
            [$newStyle, $newIconName],
            $originalString
        );

        return [
            'style' => $style,
            'iconName' => $iconName,
            'originalString' => $originalString,
            'newStyle' => $newStyle,
            'newIconName' => $newIconName,
            'baseNewString' => $baseNewString,
            'iconMapping' => $iconMapping,
        ];
    }

    /**
     * Déterminer le type de remplacement et construire le résultat
     */
    private function determineReplacementType(array $mappingData): array
    {
        $iconMapping = $mappingData['iconMapping'];

        if ($iconMapping['renamed']) {
            return $this->buildRenamedReplacement($mappingData);
        }

        if ($iconMapping['deprecated']) {
            return $this->buildDeprecatedReplacement($mappingData);
        }

        if ($iconMapping['pro_only'] && ! $this->config->isProLicense()) {
            return $this->buildProFallbackReplacement($mappingData);
        }

        return $this->buildStyleUpdateReplacement($mappingData);
    }

    /**
     * Construire remplacement pour icône renommée
     */
    private function buildRenamedReplacement(array $mappingData): array
    {
        return [
            'new_string' => $mappingData['baseNewString'],
            'warning' => \sprintf("Icône renommée '%s' → '%s'", $mappingData['iconName'], $mappingData['newIconName']),
            'type' => 'renamed_icon',
        ];
    }

    /**
     * Construire remplacement pour icône dépréciée
     */
    private function buildDeprecatedReplacement(array $mappingData): array
    {
        return [
            'new_string' => $mappingData['baseNewString'],
            'warning' => \sprintf("Icône dépréciée '%s' → '%s'", $mappingData['iconName'], $mappingData['newIconName']),
            'type' => 'deprecated_icon',
        ];
    }

    /**
     * Construire remplacement pour icône Pro avec fallback Free
     */
    private function buildProFallbackReplacement(array $mappingData): array
    {
        $alternative = $this->mapper->getFreeAlternative($mappingData['iconName']);

        if ($alternative !== null && $alternative !== '' && $alternative !== '0') {
            $newString = str_replace(
                [$mappingData['style'], $mappingData['iconName']],
                [$mappingData['newStyle'], $alternative],
                $mappingData['originalString']
            );

            return [
                'new_string' => $newString,
                'warning' => \sprintf("Icône Pro '%s' → alternative Free '%s'", $mappingData['iconName'], $alternative),
                'type' => 'pro_fallback',
            ];
        }

        return [
            'new_string' => $mappingData['baseNewString'],
            'warning' => \sprintf("Icône Pro '%s' sans alternative Free disponible", $mappingData['iconName']),
            'type' => 'pro_fallback',
        ];
    }

    /**
     * Construire remplacement pour mise à jour de style simple
     */
    private function buildStyleUpdateReplacement(array $mappingData): array
    {
        return [
            'new_string' => $mappingData['baseNewString'],
            'warning' => null,
            'type' => 'style_update',
        ];
    }

    /**
     * Construire le warning final en fusionnant tous les warnings
     */
    private function buildFinalWarning(?string $primaryWarning, array $mapperWarnings): ?string
    {
        if ($mapperWarnings === []) {
            return $primaryWarning;
        }

        $mapperWarningsStr = implode(' | ', $mapperWarnings);

        if ($primaryWarning === null || $primaryWarning === '' || $primaryWarning === '0') {
            return $mapperWarningsStr;
        }

        return $primaryWarning.' | '.$mapperWarningsStr;
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
