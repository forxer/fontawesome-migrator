<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Mappers;

use FontAwesome\Migrator\Contracts\VersionMapperInterface;
use FontAwesome\Migrator\Services\ConfigurationLoader;

/**
 * Mapper pour la migration FontAwesome 5 → 6
 * Basé sur l'architecture existante mais conforme à la nouvelle interface
 */
class FontAwesome5To6Mapper implements VersionMapperInterface
{
    private array $iconMappings;

    private array $styleMappings;

    private array $deprecatedIcons;

    private array $proOnlyIcons;

    private array $newIcons;

    private ConfigurationLoader $configLoader;

    public function __construct(private array $config = [], ?ConfigurationLoader $configLoader = null)
    {
        $this->configLoader = $configLoader ?? new ConfigurationLoader();
        $this->loadMappings();
    }

    /**
     * Charger tous les mappings pour FA5 → FA6 depuis les fichiers de configuration
     */
    private function loadMappings(): void
    {
        try {
            // Charger depuis les fichiers de configuration JSON
            $this->styleMappings = $this->configLoader->loadStyleMappings('5', '6');
            $this->iconMappings = $this->configLoader->loadIconMappings('5', '6');
            $this->deprecatedIcons = $this->configLoader->loadDeprecatedIcons('5', '6');
            $this->proOnlyIcons = $this->configLoader->loadProOnlyIcons('5', '6');
            $this->newIcons = $this->configLoader->loadNewIcons('5', '6');
        } catch (\Exception $e) {
            // Fallback vers les données hardcodées si les fichiers de config ne sont pas disponibles
            $this->loadHardcodedMappings();
        }
    }

    /**
     * Charger les mappings hardcodés (fallback)
     */
    private function loadHardcodedMappings(): void
    {
        // Fallback data is now minimal - just essential mappings to ensure the system works
        $this->styleMappings = [
            'fas' => 'fa-solid',
            'far' => 'fa-regular',
            'fab' => 'fa-brands',
        ];

        $this->iconMappings = [
            'fa-external-link' => 'fa-external-link-alt',
            'fa-sort-alpha-down' => 'fa-arrow-down-a-z',
            'fa-home' => 'fa-house',
        ];

        $this->deprecatedIcons = [
            'fa-glass',
            'fa-meetup',
        ];

        $this->proOnlyIcons = [
            'fa-analytics',
            'fa-apple-pay',
        ];

        $this->newIcons = [
            'fa-house',
            'fa-magnifying-glass',
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

    public function mapIcon(string $iconName, string $style = 'fas'): array
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
            $result['warnings'][] = \sprintf('Icône renommée: %s → %s', $iconName, $result['new_name']);
        }

        // Vérifier si l'icône est dépréciée
        if (\in_array($iconName, $this->deprecatedIcons)) {
            $result['deprecated'] = true;
            $result['warnings'][] = 'Icône dépréciée: '.$iconName;

            // Proposer une alternative si disponible
            $alternative = $this->getFreeFallback($iconName);

            if ($alternative !== null) {
                $result['new_name'] = $alternative;
                $result['warnings'][] = 'Alternative suggérée: '.$alternative;
            }
        }

        // Vérifier si l'icône est Pro uniquement
        if (\in_array($iconName, $this->proOnlyIcons) || \in_array($result['new_name'], $this->proOnlyIcons)) {
            $result['pro_only'] = true;

            if (($this->config['license_type'] ?? 'free') === 'free') {
                $result['warnings'][] = 'Icône Pro uniquement: '.$iconName;
                $fallback = $this->getFreeFallback($iconName);

                if ($fallback !== null) {
                    $result['new_name'] = $fallback;
                    $result['warnings'][] = 'Fallback gratuit: '.$fallback;
                }
            }
        }

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
        $proStyles = ['fal', 'fad', 'fa-light', 'fa-duotone', 'fa-thin', 'fa-sharp'];

        return \in_array($style, $proStyles);
    }

    public function getFreeFallback(string $proIcon): ?string
    {
        $fallbacks = [
            'fa-analytics' => 'fa-chart-line',
            'fa-apple-pay' => 'fa-credit-card',
            'fa-aws' => 'fa-cloud',
            'fa-circle-1' => 'fa-1',
            'fa-circle-2' => 'fa-2',
            'fa-circle-3' => 'fa-3',
            'fa-circle-4' => 'fa-4',
            'fa-circle-5' => 'fa-5',
            'fa-circle-6' => 'fa-6',
            'fa-circle-7' => 'fa-7',
            'fa-circle-8' => 'fa-8',
            'fa-circle-9' => 'fa-9',
            'fa-gallery-thumbnails' => 'fa-images',
            'fa-house-laptop' => 'fa-house',
            'fa-input-numeric' => 'fa-keyboard',
            'fa-input-text' => 'fa-keyboard',
            'fa-keynote' => 'fa-presentation-screen',
            'fa-lamp-desk' => 'fa-lightbulb',
            'fa-monitor-waveform' => 'fa-heartbeat',
            'fa-users-crown' => 'fa-users',
            'fa-wifi-1' => 'fa-wifi',
            'fa-wifi-2' => 'fa-wifi',
            'fa-wifi-fair' => 'fa-wifi',
            'fa-wifi-weak' => 'fa-wifi',
            // Alternatives pour icônes dépréciées
            'fa-glass' => 'fa-martini-glass-empty',
            'fa-star-o' => 'fa-star',
            'fa-close' => 'fa-xmark',
            'fa-remove' => 'fa-xmark',
            'fa-gear' => 'fa-cog',
            'fa-trash-o' => 'fa-trash-can',
            'fa-home' => 'fa-house',
            'fa-file-o' => 'fa-file',
            'fa-clock-o' => 'fa-clock',
        ];

        return $fallbacks[$proIcon] ?? null;
    }

    public function findSimilarIcons(string $iconName): array
    {
        $similar = [];
        $searchTerm = str_replace('fa-', '', $iconName);

        // Rechercher dans les icônes renommées
        foreach ($this->iconMappings as $old => $new) {
            if (str_contains($old, $searchTerm) || str_contains((string) $new, $searchTerm)) {
                $similar[] = [
                    'icon' => $new,
                    'reason' => 'Renommage',
                    'confidence' => 0.9,
                ];
            }
        }

        // Rechercher dans les nouvelles icônes FA6
        foreach ($this->newIcons as $newIcon) {
            if (str_contains((string) $newIcon, $searchTerm)) {
                $similar[] = [
                    'icon' => $newIcon,
                    'reason' => 'Nouvelle icône FA6',
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
        return '5';
    }

    public function getTargetVersion(): string
    {
        return '6';
    }

    public function iconExistsInTarget(string $iconName): bool
    {
        // Vérifier si l'icône est dans les mappings connus
        if (isset($this->iconMappings[$iconName])) {
            return true;
        }

        // Vérifier si c'est une nouvelle icône FA6
        if (\in_array($iconName, $this->newIcons)) {
            return true;
        }

        // Vérifier si ce n'est pas une icône dépréciée
        return ! \in_array($iconName, $this->deprecatedIcons);
    }

    public function getDetectionPatterns(): array
    {
        return [
            '/\bfas\s+fa-[a-zA-Z0-9-]+\b/',      // FA5 syntax
            '/\bfar\s+fa-[a-zA-Z0-9-]+\b/',      // FA5 syntax
            '/\bfal\s+fa-[a-zA-Z0-9-]+\b/',      // FA5 Pro
            '/fontawesome\.com\/releases\/v5/',   // CDN v5
            '/font-awesome\/5\.\d+\.\d+/',        // Package v5
        ];
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
