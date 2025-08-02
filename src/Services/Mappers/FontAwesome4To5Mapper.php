<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Mappers;

use FontAwesome\Migrator\Contracts\VersionMapperInterface;

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

    public function __construct(private array $config = [])
    {
        $this->loadMappings();
    }

    /**
     * Charger tous les mappings pour FA4 → FA5
     */
    private function loadMappings(): void
    {
        // Transformation des styles FA4 → FA5
        $this->styleMappings = [
            'fa' => 'fas', // Par défaut : solid
            'fa fa-' => 'fas fa-', // Transformation générique
        ];

        // Icônes avec suffixe -o (outlined) FA4 → FA5 Regular
        $outlinedIcons = [
            'fa-envelope-o' => 'fa-envelope',    // far fa-envelope
            'fa-star-o' => 'fa-star',            // far fa-star
            'fa-heart-o' => 'fa-heart',          // far fa-heart
            'fa-clock-o' => 'fa-clock',          // far fa-clock
            'fa-file-o' => 'fa-file',            // far fa-file
            'fa-folder-o' => 'fa-folder',        // far fa-folder
            'fa-folder-open-o' => 'fa-folder-open', // far fa-folder-open
            'fa-trash-o' => 'fa-trash-can',      // far fa-trash-can (renommé aussi)
            'fa-square-o' => 'fa-square',        // far fa-square
            'fa-circle-o' => 'fa-circle',        // far fa-circle
            'fa-check-square-o' => 'fa-square-check', // far fa-square-check
            'fa-plus-square-o' => 'fa-square-plus',   // far fa-square-plus
            'fa-minus-square-o' => 'fa-square-minus', // far fa-square-minus
            'fa-times-circle-o' => 'fa-circle-xmark', // far fa-circle-xmark
            'fa-arrow-circle-o-down' => 'fa-circle-arrow-down', // far fa-circle-arrow-down
            'fa-arrow-circle-o-up' => 'fa-circle-arrow-up',     // far fa-circle-arrow-up
            'fa-play-circle-o' => 'fa-circle-play',    // far fa-circle-play
            'fa-stop-circle-o' => 'fa-circle-stop',    // far fa-circle-stop
            'fa-pause-circle-o' => 'fa-circle-pause',  // far fa-circle-pause
            'fa-picture-o' => 'fa-image',         // far fa-image
            'fa-smile-o' => 'fa-face-smile',      // far fa-face-smile
            'fa-frown-o' => 'fa-face-frown',      // far fa-face-frown
            'fa-meh-o' => 'fa-face-meh',          // far fa-face-meh
            'fa-keyboard-o' => 'fa-keyboard',     // far fa-keyboard
            'fa-flag-o' => 'fa-flag',             // far fa-flag
        ];

        // Icônes renommées FA4 → FA5 (sans changement de style)
        $renamedIcons = [
            'fa-glass' => 'fa-martini-glass-empty',
            'fa-remove' => 'fa-xmark',
            'fa-close' => 'fa-xmark',
            'fa-gear' => 'fa-cog',
            'fa-trash' => 'fa-trash-can',
            'fa-home' => 'fa-house',
            'fa-file-o' => 'fa-file',
            'fa-repeat' => 'fa-arrow-rotate-right',
            'fa-rotate-right' => 'fa-arrow-rotate-right',
            'fa-refresh' => 'fa-arrows-rotate',
            'fa-list-alt' => 'fa-rectangle-list',
            'fa-dedent' => 'fa-outdent',
            'fa-video-camera' => 'fa-video',
            'fa-photo' => 'fa-image',
            'fa-picture-o' => 'fa-image',
            'fa-pencil' => 'fa-pencil-alt',
            'fa-map-marker' => 'fa-location-dot',
            'fa-adjust' => 'fa-circle-half-stroke',
            'fa-tint' => 'fa-droplet',
            'fa-edit' => 'fa-pen-to-square',
            'fa-share-square-o' => 'fa-share-from-square',
            'fa-times' => 'fa-xmark',
            'fa-times-circle' => 'fa-circle-xmark',
            'fa-th' => 'fa-table-cells',
            'fa-th-large' => 'fa-table-cells-large',
            'fa-th-list' => 'fa-list',
            'fa-sign-out' => 'fa-right-from-bracket',
            'fa-sign-in' => 'fa-right-to-bracket',
            'fa-github-alt' => 'fa-github',
            'fa-mail-forward' => 'fa-share',
            'fa-expand' => 'fa-up-right-and-down-left-from-center',
            'fa-compress' => 'fa-down-left-and-up-right-to-center',
        ];

        // Fusion des mappings
        $this->iconMappings = array_merge($outlinedIcons, $renamedIcons);

        // Icônes dépréciées (supprimées en FA5)
        $this->deprecatedIcons = [
            'fa-glass',
            'fa-remove',
            'fa-close',
            'fa-gear',
            'fa-home',
            'fa-file-o',
            'fa-clock-o',
            // Icônes -o qui deviennent regular
            'fa-envelope-o',
            'fa-star-o',
            'fa-heart-o',
            'fa-folder-o',
            'fa-trash-o',
            'fa-square-o',
            'fa-circle-o',
        ];

        // FA5 Pro styles (nouveaux en FA5)
        $this->proOnlyIcons = [
            // Styles Pro FA5
            'fa-light',
            'fa-duotone',
            // Icônes Pro spécifiques FA5
            'fa-abacus',
            'fa-acorn',
            'fa-ad',
            'fa-album',
            'fa-album-collection',
            'fa-analytics',
        ];

        // Nouvelles icônes introduites en FA5
        $this->newIcons = [
            // Nouveaux styles
            'fa-light',
            'fa-duotone',
            // Nouvelles icônes FA5
            'fa-table-cells',
            'fa-table-cells-large',
            'fa-face-smile',
            'fa-face-frown',
            'fa-face-meh',
            'fa-circle-arrow-down',
            'fa-circle-arrow-up',
            'fa-circle-play',
            'fa-square-check',
            'fa-square-plus',
            'fa-square-minus',
            'fa-circle-xmark',
            'fa-right-from-bracket',
            'fa-right-to-bracket',
            'fa-up-right-and-down-left-from-center',
            'fa-down-left-and-up-right-to-center',
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
        if (! empty($result['warnings'])) {
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
            if (str_contains($old, $searchTerm) || str_contains($new, $searchTerm)) {
                $similar[] = [
                    'icon' => $new,
                    'reason' => 'Migration FA4→FA5',
                    'confidence' => 0.9,
                ];
            }
        }

        // Rechercher dans les nouvelles icônes FA5
        foreach ($this->newIcons as $newIcon) {
            if (str_contains($newIcon, $searchTerm)) {
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
        if (\in_array($iconName, $this->deprecatedIcons)) {
            return false;
        }

        // Pour les autres, on assume qu'elles existent (à vérifier)
        return true;
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
