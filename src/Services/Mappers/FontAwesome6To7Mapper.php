<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Mappers;

use FontAwesome\Migrator\Contracts\VersionMapperInterface;

/**
 * Mapper pour la migration FontAwesome 6 → 7
 * Basé sur les données de recherche documentées
 */
class FontAwesome6To7Mapper implements VersionMapperInterface
{
    private array $iconMappings;

    private array $styleMappings;

    private array $deprecatedIcons;

    private array $proOnlyIcons;

    private array $newIcons;

    public function __construct(private array $config = [])
    {
        $this->loadMappings();
    }

    /**
     * Charger tous les mappings pour FA6 → FA7
     */
    private function loadMappings(): void
    {
        // Styles FA6 → FA7 (identiques, mais comportements changés)
        $this->styleMappings = [
            'fa-solid' => 'fa-solid',
            'fa-regular' => 'fa-regular',
            'fa-brands' => 'fa-brands',
            'fa-light' => 'fa-light',     // Pro
            'fa-duotone' => 'fa-duotone', // Pro
            'fa-thin' => 'fa-thin',       // Pro
            'fa-sharp' => 'fa-sharp',     // Pro
        ];

        // Icônes renommées FA6 → FA7
        $this->iconMappings = [
            'fa-user-large' => 'fa-user',
            'fa-headphones-simple' => 'fa-headphones',
            'fa-handshake-simple' => 'fa-handshake',
            // Autres renommages FA7 (à compléter selon releases)
        ];

        // Classes dépréciées en FA7
        $this->deprecatedIcons = [
            'fa-fw',              // Fixed width maintenant par défaut
            'sr-only',            // Remplacé par aria-label
            'fa-user-large',      // Renommé fa-user
            'fa-headphones-simple', // Renommé fa-headphones
            'fa-handshake-simple',  // Renommé fa-handshake
        ];

        // Nouvelles fonctionnalités Pro+ FA7
        $this->proOnlyIcons = [
            // Pro+ icon packs (collections curées)
            'fa-pro-plus-collection',
            // Nouvelles icônes Pro FA7 (à compléter)
        ];

        // Nouvelles icônes/fonctionnalités FA7
        $this->newIcons = [
            // Nouvelles icônes FA7 (à compléter selon releases)
            'fa-new-icon-example',
            // Pro+ collections
            'fa-curated-collection',
        ];
    }

    public function getIconMappings(): array
    {
        return $this->iconMappings;
    }

    public function getStyleMappings(): array
    {
        return $this->styleMappings;
    }

    public function getDeprecatedIcons(): array
    {
        return $this->deprecatedIcons;
    }

    public function getProOnlyIcons(): array
    {
        return $this->proOnlyIcons;
    }

    public function getNewIcons(): array
    {
        return $this->newIcons;
    }

    public function mapIcon(string $iconName, string $style = 'fa-solid'): array
    {
        $result = [
            'new_name' => $iconName,
            'found' => true,
            'deprecated' => false,
            'pro_only' => false,
            'renamed' => false,
            'warnings' => [],
        ];

        // Vérifier si l'icône est renommée
        if (isset($this->iconMappings[$iconName])) {
            $result['new_name'] = $this->iconMappings[$iconName];
            $result['renamed'] = true;
            $result['warnings'][] = \sprintf('Icône renommée FA7: %s → %s', $iconName, $result['new_name']);
        }

        // Vérifier si l'icône/classe est dépréciée
        if (\in_array($iconName, $this->deprecatedIcons)) {
            $result['deprecated'] = true;

            switch ($iconName) {
                case 'fa-fw':
                    $result['warnings'][] = 'fa-fw déprécié : largeur fixe maintenant par défaut en FA7';
                    break;

                case 'sr-only':
                    $result['warnings'][] = 'sr-only supprimé : utiliser aria-label pour l\'accessibilité';
                    break;

                default:
                    $result['warnings'][] = 'Élément déprécié en FA7: '.$iconName;
            }
        }

        // Vérifier si l'icône est Pro uniquement
        if (\in_array($iconName, $this->proOnlyIcons) || \in_array($result['new_name'], $this->proOnlyIcons)) {
            $result['pro_only'] = true;

            if (($this->config['license_type'] ?? 'free') === 'free') {
                $result['warnings'][] = 'Fonctionnalité Pro+ FA7: '.$iconName;
            }
        }

        // Avertissements spécifiques FA7
        $this->addFA7SpecificWarnings($result, $iconName, $style);

        return $result;
    }

    public function mapStyle(string $style, bool $withFallback = true): string
    {
        $mappedStyle = $this->styleMappings[$style] ?? $style;

        if ($withFallback && ($this->config['license_type'] ?? 'free') === 'free' && $this->isProStyle($mappedStyle)) {
            return $this->getFallbackStyle();
        }

        return $mappedStyle;
    }

    public function isProStyle(string $style): bool
    {
        $proStyles = ['fa-light', 'fa-duotone', 'fa-thin', 'fa-sharp'];

        return \in_array($style, $proStyles);
    }

    public function getFreeFallback(string $proIcon): ?string
    {
        // Fallbacks spécifiques pour FA7
        $fallbacks = [];

        return $fallbacks[$proIcon] ?? null;
    }

    public function findSimilarIcons(string $iconName): array
    {
        $similar = [];
        $searchTerm = str_replace('fa-', '', $iconName);

        // Rechercher dans les icônes renommées
        foreach ($this->iconMappings as $old => $new) {
            if (str_contains($old, $searchTerm) || str_contains($new, $searchTerm)) {
                $similar[] = [
                    'icon' => $new,
                    'reason' => 'Migration FA6→FA7',
                    'confidence' => 0.9,
                ];
            }
        }

        // Rechercher dans les nouvelles icônes FA7
        foreach ($this->newIcons as $newIcon) {
            if (str_contains($newIcon, $searchTerm)) {
                $similar[] = [
                    'icon' => $newIcon,
                    'reason' => 'Nouvelle fonctionnalité FA7',
                    'confidence' => 0.7,
                ];
            }
        }

        return $similar;
    }

    public function getMappingStats(): array
    {
        return [
            'renamed_icons' => \count($this->iconMappings),
            'deprecated_icons' => \count($this->deprecatedIcons),
            'pro_only_icons' => \count($this->proOnlyIcons),
            'new_icons' => \count($this->newIcons),
        ];
    }

    public function getSourceVersion(): string
    {
        return '6';
    }

    public function getTargetVersion(): string
    {
        return '7';
    }

    public function iconExistsInTarget(string $iconName): bool
    {
        // Vérifier si l'icône est dans les mappings connus
        if (isset($this->iconMappings[$iconName])) {
            return true;
        }

        // Vérifier si c'est une nouvelle fonctionnalité FA7
        if (\in_array($iconName, $this->newIcons)) {
            return true;
        }

        // Vérifier si ce n'est pas déprécié (sauf fa-fw qui devient implicite)
        if (\in_array($iconName, $this->deprecatedIcons) && $iconName !== 'fa-fw') {
            return false;
        }

        // Pour la plupart des icônes FA6, elles existent encore en FA7
        return true;
    }

    public function getDetectionPatterns(): array
    {
        return [
            '/\bfa-solid\s+fa-[a-zA-Z0-9-]+\b/',  // FA6/7 syntax
            '/fontawesome\.com\/releases\/v6/',    // CDN v6
            '/font-awesome\/6\.\d+\.\d+/',         // Package v6
            '/fa-house\b/',                        // Icône spécifique FA6
            '/fa-magnifying-glass\b/',             // Icône spécifique FA6
            '/fa-user-large\b/',                   // Icône à renommer en FA7
        ];
    }

    /**
     * Ajouter des avertissements spécifiques à FA7
     */
    private function addFA7SpecificWarnings(array &$result, string $iconName, string $style): void
    {
        // Avertissement largeur fixe par défaut
        if (str_contains($iconName, 'fa-fw')) {
            $result['warnings'][] = 'FA7: Largeur fixe maintenant par défaut, fa-fw inutile';
        }

        // Avertissement accessibilité
        if (str_contains($iconName, 'sr-only')) {
            $result['warnings'][] = 'FA7: Utiliser aria-label au lieu de sr-only';
        }

        // Avertissement format .woff2 uniquement
        if (str_contains($iconName, 'webfont') || str_contains($iconName, 'font-face')) {
            $result['warnings'][] = 'FA7: Format .woff2 uniquement pour les webfonts';
        }

        // Avertissement migration Dart Sass
        if (str_contains($iconName, 'sass') || str_contains($iconName, 'scss')) {
            $result['warnings'][] = 'FA7: Dart Sass requis, abandon node-sass/libsass';
        }
    }

    /**
     * Obtenir le style de fallback configuré
     */
    private function getFallbackStyle(): string
    {
        $fallback = $this->config['fallback_strategy'] ?? 'solid';

        $fallbackMapping = [
            'solid' => 'fa-solid',
            'regular' => 'fa-regular',
            'brands' => 'fa-brands',
        ];

        return $fallbackMapping[$fallback] ?? 'fa-solid';
    }
}
