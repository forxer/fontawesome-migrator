<?php

namespace FontAwesome\Migrator\Services;

class IconMapper
{
    protected array $config;
    protected array $renamedIcons;
    protected array $deprecatedIcons;
    protected array $proOnlyIcons;
    protected array $newIcons;

    public function __construct()
    {
        $this->config = config('fontawesome-migrator');
        $this->loadMappings();
    }

    /**
     * Charger tous les mappings d'icônes
     */
    protected function loadMappings(): void
    {
        // Icônes renommées en FA6
        $this->renamedIcons = [
            'fa-external-link' => 'fa-external-link-alt',
            'fa-sort-alpha-down' => 'fa-arrow-down-a-z',
            'fa-sort-alpha-up' => 'fa-arrow-up-a-z',
            'fa-sort-numeric-down' => 'fa-arrow-down-1-9',
            'fa-sort-numeric-up' => 'fa-arrow-up-1-9',
            'fa-sort-amount-down' => 'fa-arrow-down-short-wide',
            'fa-sort-amount-up' => 'fa-arrow-up-short-wide',
            'fa-sort-amount-down-alt' => 'fa-arrow-down-wide-short',
            'fa-sort-amount-up-alt' => 'fa-arrow-up-wide-short',
            'fa-ad' => 'fa-rectangle-ad',
            'fa-glass' => 'fa-martini-glass-empty',
            'fa-envelope-o' => 'fa-envelope',
            'fa-star-o' => 'fa-star',
            'fa-close' => 'fa-xmark',
            'fa-remove' => 'fa-xmark',
            'fa-gear' => 'fa-cog',
            'fa-trash-o' => 'fa-trash-can',
            'fa-file-o' => 'fa-file',
            'fa-clock-o' => 'fa-clock',
            'fa-arrow-circle-o-down' => 'fa-circle-arrow-down',
            'fa-arrow-circle-o-up' => 'fa-circle-arrow-up',
            'fa-play-circle-o' => 'fa-circle-play',
            'fa-repeat' => 'fa-arrow-rotate-right',
            'fa-rotate-right' => 'fa-arrow-rotate-right',
            'fa-refresh' => 'fa-arrows-rotate',
            'fa-list-alt' => 'fa-rectangle-list',
            'fa-dedent' => 'fa-outdent',
            'fa-video-camera' => 'fa-video',
            'fa-picture-o' => 'fa-image',
            'fa-photo' => 'fa-image',
            'fa-image' => 'fa-image',
            'fa-pencil' => 'fa-pencil-alt',
            'fa-map-marker' => 'fa-location-dot',
            'fa-adjust' => 'fa-circle-half-stroke',
            'fa-tint' => 'fa-droplet',
            'fa-edit' => 'fa-pen-to-square',
            'fa-share-square-o' => 'fa-share-from-square',
            'fa-check-square-o' => 'fa-square-check',
            'fa-times' => 'fa-xmark',
            'fa-times-circle' => 'fa-circle-xmark',
            'fa-times-circle-o' => 'fa-circle-xmark',
            'fa-plus-square-o' => 'fa-square-plus',
            'fa-minus-square-o' => 'fa-square-minus',
            'fa-th' => 'fa-table-cells',
            'fa-th-large' => 'fa-table-cells-large',
            'fa-th-list' => 'fa-list',
            'fa-heart-o' => 'fa-heart',
            'fa-sign-out' => 'fa-right-from-bracket',
            'fa-sign-in' => 'fa-right-to-bracket',
            'fa-github-alt' => 'fa-github',
            'fa-folder-o' => 'fa-folder',
            'fa-folder-open-o' => 'fa-folder-open',
            'fa-smile-o' => 'fa-face-smile',
            'fa-frown-o' => 'fa-face-frown',
            'fa-meh-o' => 'fa-face-meh',
            'fa-keyboard-o' => 'fa-keyboard',
            'fa-flag-o' => 'fa-flag',
            'fa-mail-forward' => 'fa-share',
            'fa-expand' => 'fa-up-right-and-down-left-from-center',
            'fa-compress' => 'fa-down-left-and-up-right-to-center',
        ];

        // Icônes dépréciées (supprimées en FA6)
        $this->deprecatedIcons = [
            'fa-glass',
            'fa-meetup',
            'fa-star-o',
            'fa-close',
            'fa-remove',
            'fa-gear',
            'fa-trash-o',
            'fa-home',
            'fa-file-o',
            'fa-clock-o',
        ];

        // Icônes disponibles uniquement en Pro
        $this->proOnlyIcons = [
            'fa-analytics',
            'fa-apple-pay',
            'fa-aws',
            'fa-circle-1',
            'fa-circle-2',
            'fa-circle-3',
            'fa-circle-4',
            'fa-circle-5',
            'fa-circle-6',
            'fa-circle-7',
            'fa-circle-8',
            'fa-circle-9',
            'fa-gallery-thumbnails',
            'fa-house-laptop',
            'fa-input-numeric',
            'fa-input-text',
            'fa-keynote',
            'fa-lamp-desk',
            'fa-monitor-waveform',
            'fa-object-group',
            'fa-object-ungroup',
            'fa-page-break',
            'fa-presentation-screen',
            'fa-scanner-keyboard',
            'fa-tablet-rugged',
            'fa-users-crown',
            'fa-wifi-1',
            'fa-wifi-2',
            'fa-wifi-fair',
            'fa-wifi-weak',
        ];

        // Nouvelles icônes FA6
        $this->newIcons = [
            'fa-house',
            'fa-magnifying-glass',
            'fa-user-group',
            'fa-arrow-trend-up',
            'fa-arrow-trend-down',
            'fa-fingerprint',
            'fa-face-laugh-beam',
            'fa-face-laugh-wink',
            'fa-face-laugh-squint',
            'fa-handshake-simple',
            'fa-location-crosshairs',
            'fa-mountain-city',
            'fa-right-left',
            'fa-up-down',
            'fa-up-down-left-right',
        ];
    }

    /**
     * Mapper une icône FA5 vers FA6
     */
    public function mapIcon(string $iconName, string $style = 'fas'): array
    {
        $result = [
            'new_name' => $iconName,
            'found' => true,
            'deprecated' => false,
            'pro_only' => false,
            'renamed' => false,
            'warnings' => []
        ];

        // Vérifier si l'icône est renommée
        if (isset($this->renamedIcons[$iconName])) {
            $result['new_name'] = $this->renamedIcons[$iconName];
            $result['renamed'] = true;
            $result['warnings'][] = "Icône renommée: {$iconName} → {$result['new_name']}";
        }

        // Vérifier si l'icône est dépréciée
        if (in_array($iconName, $this->deprecatedIcons)) {
            $result['deprecated'] = true;
            $result['warnings'][] = "Icône dépréciée: {$iconName}";

            // Proposer une alternative si disponible
            $alternative = $this->getAlternativeIcon($iconName);
            if ($alternative) {
                $result['new_name'] = $alternative;
                $result['warnings'][] = "Alternative suggérée: {$alternative}";
            }
        }

        // Vérifier si l'icône est Pro uniquement
        if (in_array($iconName, $this->proOnlyIcons) || in_array($result['new_name'], $this->proOnlyIcons)) {
            $result['pro_only'] = true;

            if ($this->config['license_type'] === 'free') {
                $result['warnings'][] = "Icône Pro uniquement: {$iconName}";
                $fallback = $this->getFreeFallback($iconName);
                if ($fallback) {
                    $result['new_name'] = $fallback;
                    $result['warnings'][] = "Fallback gratuit: {$fallback}";
                }
            }
        }

        return $result;
    }

    /**
     * Obtenir une alternative pour une icône dépréciée
     */
    protected function getAlternativeIcon(string $deprecatedIcon): ?string
    {
        $alternatives = [
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

        return $alternatives[$deprecatedIcon] ?? null;
    }

    /**
     * Obtenir une alternative gratuite pour une icône Pro
     */
    protected function getFreeFallback(string $proIcon): ?string
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
        ];

        return $fallbacks[$proIcon] ?? null;
    }

    /**
     * Rechercher des icônes similaires
     */
    public function findSimilarIcons(string $iconName): array
    {
        $similar = [];
        $searchTerm = str_replace('fa-', '', $iconName);

        // Rechercher dans les icônes renommées
        foreach ($this->renamedIcons as $old => $new) {
            if (str_contains($old, $searchTerm) || str_contains($new, $searchTerm)) {
                $similar[] = [
                    'icon' => $new,
                    'reason' => 'Renommage',
                    'confidence' => 0.9
                ];
            }
        }

        // Rechercher dans les nouvelles icônes FA6
        foreach ($this->newIcons as $newIcon) {
            if (str_contains($newIcon, $searchTerm)) {
                $similar[] = [
                    'icon' => $newIcon,
                    'reason' => 'Nouvelle icône FA6',
                    'confidence' => 0.7
                ];
            }
        }

        return $similar;
    }

    /**
     * Valider qu'une icône existe en FA6
     */
    public function iconExistsInFA6(string $iconName): bool
    {
        // Vérifier si l'icône est dans les mappings connus
        if (isset($this->renamedIcons[$iconName])) {
            return true;
        }

        // Vérifier si c'est une nouvelle icône FA6
        if (in_array($iconName, $this->newIcons)) {
            return true;
        }

        // Vérifier si ce n'est pas une icône dépréciée
        if (in_array($iconName, $this->deprecatedIcons)) {
            return false;
        }

        // Pour les autres icônes, on assume qu'elles existent
        // (cette logique pourrait être améliorée avec une liste complète)
        return true;
    }

    /**
     * Obtenir des statistiques sur les mappings
     */
    public function getMappingStats(): array
    {
        return [
            'renamed_icons' => count($this->renamedIcons),
            'deprecated_icons' => count($this->deprecatedIcons),
            'pro_only_icons' => count($this->proOnlyIcons),
            'new_fa6_icons' => count($this->newIcons),
        ];
    }
}