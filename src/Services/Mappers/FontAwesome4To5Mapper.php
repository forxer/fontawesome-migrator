<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Mappers;

use Exception;
use FontAwesome\Migrator\Contracts\VersionMapperInterface;
use FontAwesome\Migrator\Services\ConfigurationLoader;

/**
 * Mapper pour la migration FontAwesome 4 → 5
 * Basé sur les données de recherche documentées
 */
class FontAwesome4To5Mapper implements VersionMapperInterface
{
    private array $iconMappings;

    private array $styleMappings;

    private array $deprecatedIcons;

    private array $proOnlyIcons;

    private array $newIcons;

    private readonly ConfigurationLoader $configLoader;

    public function __construct(private array $config = [], ?ConfigurationLoader $configLoader = null)
    {
        $this->configLoader = $configLoader ?? new ConfigurationLoader();
        $this->loadMappings();
    }

    /**
     * Charger tous les mappings pour FA4 → FA5 depuis les fichiers de configuration
     */
    private function loadMappings(): void
    {
        try {
            // Charger depuis les fichiers de configuration JSON
            $this->styleMappings = $this->configLoader->loadStyleMappings('4', '5');
            $this->iconMappings = $this->configLoader->loadIconMappings('4', '5');
            $this->deprecatedIcons = $this->configLoader->loadDeprecatedIcons('4', '5');
            $this->proOnlyIcons = $this->configLoader->loadProOnlyIcons('4', '5');
            $this->newIcons = $this->configLoader->loadNewIcons('4', '5');
        } catch (Exception) {
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
            'fa' => 'fas',
            'fa fa-' => 'fas fa-',
        ];

        $this->iconMappings = [
            'fa-envelope-o' => 'fa-envelope',
            'fa-star-o' => 'fa-star',
            'fa-heart-o' => 'fa-heart',
        ];

        $this->deprecatedIcons = [
            'fa-glass',
            'fa-remove',
            'fa-close',
        ];

        $this->proOnlyIcons = [
            'fa-light',
            'fa-duotone',
        ];

        $this->newIcons = [
            'fa-table-cells',
            'fa-face-smile',
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

    public function mapIcon(string $iconName, string $style = 'fa'): array
    {
        $result = [
            'new_name' => $iconName,
            'found' => true,
            'deprecated' => false,
            'pro_only' => false,
            'renamed' => false,
            'warnings' => [],
        ];

        // Déterminer le nouveau style selon l'icône
        $newStyle = 'fas'; // Par défaut solid

        if ($this->isOutlinedIcon($iconName)) {
            $newStyle = 'far'; // Regular pour les icônes -o
            $result['warnings'][] = \sprintf('Icône outlined → style Regular (%s)', $newStyle);
        }

        // Vérifier si l'icône est renommée
        if (isset($this->iconMappings[$iconName])) {
            $result['new_name'] = $this->iconMappings[$iconName];
            $result['renamed'] = true;
            $result['warnings'][] = \sprintf('Icône renommée: %s → %s (%s)', $iconName, $result['new_name'], $newStyle);
        }

        // Vérifier si l'icône est dépréciée
        if (\in_array($iconName, $this->deprecatedIcons)) {
            $result['deprecated'] = true;
            $result['warnings'][] = 'Transformation FA4→FA5: '.$iconName;

            // Le mapping fournit déjà l'alternative
            if (! $result['renamed']) {
                $result['warnings'][] = 'Vérification manuelle recommandée';
            }
        }

        // Ajouter info de style dans les warnings
        if (isset($result['warnings']) && $result['warnings'] !== []) {
            $result['warnings'][] = \sprintf('Nouveau style recommandé: %s', $newStyle);
        }

        return $result;
    }

    public function mapStyle(string $style, bool $withFallback = true): string
    {
        // FA4 utilise seulement 'fa'
        if ($style === 'fa') {
            return 'fas'; // Par défaut solid en FA5
        }

        // Si déjà un style FA5, retourner tel quel
        $fa5Styles = ['fas', 'far', 'fab', 'fal', 'fad'];

        if (\in_array($style, $fa5Styles)) {
            if ($withFallback && ($this->config['license_type'] ?? 'free') === 'free' && $this->isProStyle($style)) {
                return $this->getFallbackStyle();
            }

            return $style;
        }

        return $style; // Inconnu, retourner tel quel
    }

    public function isProStyle(string $style): bool
    {
        $proStyles = ['fal', 'fad']; // Light et Duotone en FA5

        return \in_array($style, $proStyles);
    }

    public function getFreeFallback(string $proIcon): ?string
    {
        // Pour FA4→5, pas de fallbacks spécifiques
        // Les alternatives sont dans les mappings principaux
        return null;
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
                    'reason' => 'Migration FA4→FA5',
                    'confidence' => 0.9,
                ];
            }
        }

        // Rechercher dans les nouvelles icônes FA5
        foreach ($this->newIcons as $newIcon) {
            if (str_contains((string) $newIcon, $searchTerm)) {
                $similar[] = [
                    'icon' => $newIcon,
                    'reason' => 'Nouvelle icône FA5',
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
        return '4';
    }

    public function getTargetVersion(): string
    {
        return '5';
    }

    public function iconExistsInTarget(string $iconName): bool
    {
        // En FA4→5, tout doit être transformé
        // Les icônes FA4 n'existent plus telles quelles en FA5
        if (isset($this->iconMappings[$iconName])) {
            return true; // A un mapping
        }

        // Si pas de mapping et c'est déprécié, problématique
        // Pour les autres, on assume qu'elles existent (à vérifier)
        return ! \in_array($iconName, $this->deprecatedIcons);
    }

    public function getDetectionPatterns(): array
    {
        return [
            '/\bfa\s+fa-[a-zA-Z0-9-]+\b/',       // FA4 syntax (pas de préfixe style)
            '/fontawesome\.com\/font-awesome-4/', // CDN v4
            '/font-awesome\/4\.\d+\.\d+/',        // Package v4
            '/fa-envelope-o\b/',                   // Icône spécifique FA4 (-o suffix)
            '/fa-star-o\b/',                       // Icône spécifique FA4 (-o suffix)
            '/fa-[a-z-]+-o\b/',                   // Pattern général -o suffix
        ];
    }

    /**
     * Vérifier si une icône est "outlined" (suffixe -o)
     */
    private function isOutlinedIcon(string $iconName): bool
    {
        return str_ends_with($iconName, '-o');
    }

    /**
     * Obtenir le style de fallback configuré
     */
    private function getFallbackStyle(): string
    {
        $fallback = $this->config['fallback_strategy'] ?? 'solid';

        $fallbackMapping = [
            'solid' => 'fas',
            'regular' => 'far',
            'brands' => 'fab',
        ];

        return $fallbackMapping[$fallback] ?? 'fas';
    }
}
