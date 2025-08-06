<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Configuration;

use Exception;

/**
 * Service centralisé pour la gestion des patterns FontAwesome
 * Évite la duplication dans FileScanner, IconReplacer, MigrationVersionManager
 */
class FontAwesomePatternService
{
    public function __construct(
        private readonly ConfigurationLoader $configLoader,
    ) {}

    /**
     * Obtenir les patterns de détection pour une version spécifique
     */
    public function getDetectionPatterns(string $version): array
    {
        return match ($version) {
            '4' => [
                '/\bfa\s+fa-[a-zA-Z0-9-]+\b/',           // FA4 syntax (pas de préfixe style)
                '/fontawesome\.com\/font-awesome-4/',     // CDN v4
                '/font-awesome\/4\.\d+\.\d+/',            // Package v4
                '/fa-envelope-o\b/',                       // Icône spécifique FA4 (-o suffix)
                '/fa-star-o\b/',                           // Icône spécifique FA4 (-o suffix)
            ],
            '5' => [
                '/\bfas\s+fa-[a-zA-Z0-9-]+\b/',          // FA5 syntax
                '/\bfar\s+fa-[a-zA-Z0-9-]+\b/',          // FA5 syntax
                '/\bfal\s+fa-[a-zA-Z0-9-]+\b/',          // FA5 Pro
                '/\bfad\s+fa-[a-zA-Z0-9-]+\b/',          // FA5 Pro duotone
                '/fontawesome\.com\/releases\/v5/',        // CDN v5
                '/font-awesome\/5\.\d+\.\d+/',             // Package v5
            ],
            '6' => [
                '/\bfa-solid\s+fa-[a-zA-Z0-9-]+\b/',     // FA6 syntax
                '/\bfa-regular\s+fa-[a-zA-Z0-9-]+\b/',   // FA6 syntax
                '/\bfa-light\s+fa-[a-zA-Z0-9-]+\b/',     // FA6 Pro
                '/\bfa-duotone\s+fa-[a-zA-Z0-9-]+\b/',   // FA6 Pro
                '/fontawesome\.com\/releases\/v6/',        // CDN v6
                '/font-awesome\/6\.\d+\.\d+/',             // Package v6
                '/fa-house\b/',                            // Icône spécifique FA6
                '/fa-magnifying-glass\b/',                 // Icône spécifique FA6
            ],
            '7' => [
                '/\bfa-solid\s+fa-[a-zA-Z0-9-]+\b/',     // FA7 syntax
                '/\bfa-regular\s+fa-[a-zA-Z0-9-]+\b/',   // FA7 syntax
                '/\bfa-light\s+fa-[a-zA-Z0-9-]+\b/',     // FA7 Pro
                '/\bfa-duotone\s+fa-[a-zA-Z0-9-]+\b/',   // FA7 Pro
                '/\bfa-thin\s+fa-[a-zA-Z0-9-]+\b/',      // FA7 Pro
                '/\bfa-sharp\s+fa-[a-zA-Z0-9-]+\b/',     // FA7 Sharp
                '/fontawesome\.com\/releases\/v7/',        // CDN v7
                '/font-awesome\/7\.\d+\.\d+/',             // Package v7
            ],
            default => []
        };
    }

    /**
     * Construire les patterns regex basés sur les styles supportés par version
     */
    public function buildStylePatternsFromConfiguration(string $version, string $targetVersion): array
    {
        try {
            $styleMappings = $this->configLoader->loadStyleMappings($version, $targetVersion);

            if ($styleMappings === []) {
                return [];
            }

            $styleKeys = array_keys($styleMappings);
            $stylesPattern = implode('|', array_map('preg_quote', $styleKeys));

            return [
                // Pattern principal pour style + icône
                '/\b('.$stylesPattern.')\s+(fa-[a-zA-Z0-9-]+)\b/',
                // Pattern dans attributs class
                '/class=["\']([^"\']*\b(?:'.$stylesPattern.')\s+fa-[a-zA-Z0-9-]+[^"\']*)["\']/',
                // Pattern dans data-icon ou autres attributs
                '/(?:data-icon|data-prefix)=["\'][^"\']*\b('.$stylesPattern.')\s+(fa-[a-zA-Z0-9-]+)[^"\']*["\']/',
            ];
        } catch (Exception) {
            return [];
        }
    }

    /**
     * Extraire les données d'icône d'une chaîne en utilisant les styles connus
     */
    public function parseIconWithStyleMappings(string $iconString, array $styleMappings): ?array
    {
        if ($styleMappings === []) {
            return null;
        }

        $styleKeys = array_keys($styleMappings);
        $stylesPattern = implode('|', array_map('preg_quote', $styleKeys));

        if (preg_match('/\b('.$stylesPattern.')\s+(fa-[a-zA-Z0-9-]+)\b/', $iconString, $matches)) {
            return [
                'style' => $matches[1],
                'name' => $matches[2],
                'original' => $iconString,
            ];
        }

        return null;
    }

    /**
     * Détecter automatiquement la version FontAwesome dans le contenu
     */
    public function detectVersion(string $content): string
    {
        $versions = ['7', '6', '5', '4']; // Ordre de priorité

        foreach ($versions as $version) {
            $patterns = $this->getDetectionPatterns($version);

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $content)) {
                    return $version;
                }
            }
        }

        return 'unknown';
    }

    /**
     * Vérifier si le contenu contient des icônes FontAwesome d'une version spécifique
     */
    public function hasVersionIcons(string $content, string $version): bool
    {
        $patterns = $this->getDetectionPatterns($version);

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extraire toutes les icônes d'une version spécifique dans le contenu
     */
    public function extractVersionIcons(string $content, string $version): array
    {
        $icons = [];
        $patterns = $this->getDetectionPatterns($version);

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
                foreach ($matches as $match) {
                    $fullMatch = $match[0][0];
                    $offset = $match[0][1];

                    $icons[] = [
                        'full_match' => $fullMatch,
                        'offset' => $offset,
                        'line' => substr_count(substr($content, 0, $offset), "\n") + 1,
                        'version' => $version,
                    ];
                }
            }
        }

        return array_unique($icons, SORT_REGULAR);
    }

    /**
     * Extraire toutes les icônes FontAwesome d'un contenu avec leurs positions
     */
    public function extractIconsWithPositions(string $content): array
    {
        $icons = [];
        $lines = explode("\n", $content);
        $offset = 0;

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

            $offset += \strlen($line) + 1;
        }

        return $icons;
    }

    /**
     * Obtenir les statistiques des patterns disponibles
     */
    public function getPatternStats(): array
    {
        $versions = ['4', '5', '6', '7'];
        $stats = [];

        foreach ($versions as $version) {
            $patterns = $this->getDetectionPatterns($version);
            $stats[$version] = \count($patterns);
        }

        return [
            'total_versions' => \count($versions),
            'patterns_by_version' => $stats,
            'total_patterns' => array_sum($stats),
        ];
    }
}
