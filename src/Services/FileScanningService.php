<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services;

use FontAwesome\Migrator\Contracts\ConfigurationInterface;

/**
 * Service orchestrateur pour le scan de fichiers
 * Réduit le couplage en centralisant les opérations de scan
 */
class FileScanningService
{
    public function __construct(
        private readonly ConfigurationInterface $config,
        private readonly FontAwesomePatternService $patternService,
        private readonly ConfigurationLoader $configLoader
    ) {}

    /**
     * Analyser un fichier et extraire les informations FontAwesome
     */
    public function analyzeFileForFontAwesome(string $filePath): array
    {
        if (! file_exists($filePath)) {
            return [
                'has_icons' => false,
                'version' => null,
                'icons' => [],
                'error' => 'File not found',
            ];
        }

        try {
            $content = file_get_contents($filePath);

            if ($content === false) {
                return [
                    'has_icons' => false,
                    'version' => null,
                    'icons' => [],
                    'error' => 'Cannot read file',
                ];
            }

            return $this->performContentAnalysis($content);

        } catch (\Exception $e) {
            return [
                'has_icons' => false,
                'version' => null,
                'icons' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Effectuer l'analyse du contenu
     */
    private function performContentAnalysis(string $content): array
    {
        // Détecter la version FontAwesome
        $detectedVersion = $this->patternService->detectVersion($content);

        if ($detectedVersion === null) {
            return [
                'has_icons' => false,
                'version' => null,
                'icons' => [],
            ];
        }

        // Extraire les icônes selon la version détectée
        $icons = $this->extractVersionSpecificIcons($content, $detectedVersion);

        return [
            'has_icons' => ! empty($icons),
            'version' => $detectedVersion,
            'icons' => $icons,
        ];
    }

    /**
     * Extraire les icônes spécifiques à une version
     */
    private function extractVersionSpecificIcons(string $content, string $version): array
    {
        $versionIcons = $this->patternService->extractVersionIcons($content, $version);

        if (empty($versionIcons)) {
            return [];
        }

        // Pour les versions autres que 4, enrichir avec les mappings de styles
        if ($version !== '4') {
            return $this->enrichIconsWithStyleMappings($versionIcons, $version);
        }

        return $versionIcons;
    }

    /**
     * Enrichir les icônes avec les mappings de styles
     */
    private function enrichIconsWithStyleMappings(array $icons, string $version): array
    {
        try {
            $targetVersion = $this->determineTargetVersion($version);
            $styleMappings = $this->configLoader->loadStyleMappings($version, $targetVersion);

            foreach ($icons as &$iconData) {
                $parsedIcon = $this->patternService->parseIconWithStyleMappings(
                    $iconData['full_match'],
                    $styleMappings
                );
                $iconData = array_merge($iconData, $parsedIcon);
            }
        } catch (\Exception) {
            // En cas d'erreur de mapping, retourner les icônes brutes
        }

        return $icons;
    }

    /**
     * Déterminer la version cible basée sur la version source
     */
    private function determineTargetVersion(string $sourceVersion): string
    {
        return match ($sourceVersion) {
            '4' => '5',
            '5' => '6',
            '6' => '7',
            default => '7'
        };
    }

    /**
     * Obtenir les extensions de fichiers supportées
     */
    public function getSupportedExtensions(): array
    {
        return $this->config->getFileExtensions();
    }

    /**
     * Obtenir les patterns d'exclusion
     */
    public function getExcludePatterns(): array
    {
        return $this->config->getExcludePatterns();
    }
}
