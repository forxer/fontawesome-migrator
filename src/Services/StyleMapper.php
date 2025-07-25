<?php

namespace FontAwesome\Migrator\Services;

class StyleMapper
{
    protected array $config;

    protected array $styleMapping;

    public function __construct()
    {
        $this->config = config('fontawesome-migrator');
        $this->initializeStyleMapping();
    }

    /**
     * Initialiser le mapping des styles FA5 vers FA6
     */
    protected function initializeStyleMapping(): void
    {
        $this->styleMapping = [
            // Formats courts FA5 → FA6
            'fas' => 'fa-solid',
            'far' => 'fa-regular',
            'fab' => 'fa-brands',

            // Formats longs (déjà corrects en FA5 Pro)
            'fa-solid' => 'fa-solid',
            'fa-regular' => 'fa-regular',
            'fa-brands' => 'fa-brands',
        ];

        // Ajouter les styles Pro si disponibles
        if ($this->config['license_type'] === 'pro') {
            $this->styleMapping = array_merge($this->styleMapping, [
                'fal' => 'fa-light',
                'fad' => 'fa-duotone',
                'fa-light' => 'fa-light',
                'fa-duotone' => 'fa-duotone',
            ]);

            // Nouveaux styles FA6 Pro si activés
            if ($this->config['pro_styles']['thin'] ?? false) {
                $this->styleMapping['fa-thin'] = 'fa-thin';
            }

            if ($this->config['pro_styles']['sharp'] ?? false) {
                $this->styleMapping['fa-sharp'] = 'fa-sharp';
            }
        }
    }

    /**
     * Mapper un style FA5 vers FA6
     */
    public function mapStyle(string $fa5Style): string
    {
        // Retourner le style mappé ou appliquer la stratégie de fallback
        if (isset($this->styleMapping[$fa5Style])) {
            return $this->styleMapping[$fa5Style];
        }

        // Gestion des styles Pro non disponibles
        if ($this->config['license_type'] === 'free') {
            $proStyles = ['fal', 'fad', 'fa-light', 'fa-duotone', 'fa-thin', 'fa-sharp'];

            if (\in_array($fa5Style, $proStyles)) {
                return $this->getFallbackStyle();
            }
        }

        // Style non reconnu, retourner tel quel avec avertissement
        return $fa5Style;
    }

    /**
     * Obtenir le style de fallback configuré
     */
    protected function getFallbackStyle(): string
    {
        $fallback = $this->config['fallback_strategy'] ?? 'solid';

        $fallbackMapping = [
            'solid' => 'fa-solid',
            'regular' => 'fa-regular',
            'brands' => 'fa-brands',
        ];

        return $fallbackMapping[$fallback] ?? 'fa-solid';
    }

    /**
     * Vérifier si un style est disponible dans la licence actuelle
     */
    public function isStyleAvailable(string $style): bool
    {
        return isset($this->styleMapping[$style]);
    }

    /**
     * Obtenir tous les styles disponibles
     */
    public function getAvailableStyles(): array
    {
        return array_values($this->styleMapping);
    }

    /**
     * Détecter le type de licence basé sur les styles utilisés
     */
    public function detectLicenseType(array $stylesFound): string
    {
        $proOnlyStyles = ['fal', 'fad', 'fa-light', 'fa-duotone', 'fa-thin', 'fa-sharp'];

        foreach ($stylesFound as $style) {
            if (\in_array($style, $proOnlyStyles)) {
                return 'pro';
            }
        }

        return 'free';
    }

    /**
     * Obtenir les recommandations de style pour FA6
     */
    public function getStyleRecommendations(string $currentStyle): array
    {
        $recommendations = [];

        switch ($currentStyle) {
            case 'fas':
            case 'fa-solid':
                $recommendations[] = [
                    'style' => 'fa-solid',
                    'reason' => 'Style par défaut, le plus lisible',
                    'priority' => 1,
                ];
                break;

            case 'far':
            case 'fa-regular':
                $recommendations[] = [
                    'style' => 'fa-regular',
                    'reason' => 'Plus léger visuellement',
                    'priority' => 1,
                ];
                break;

            case 'fal':
            case 'fa-light':
                if ($this->config['license_type'] === 'pro') {
                    $recommendations[] = [
                        'style' => 'fa-light',
                        'reason' => 'Style fin et élégant (Pro)',
                        'priority' => 1,
                    ];

                    if ($this->config['pro_styles']['thin'] ?? false) {
                        $recommendations[] = [
                            'style' => 'fa-thin',
                            'reason' => 'Nouveau style encore plus fin (FA6 Pro)',
                            'priority' => 2,
                        ];
                    }
                }

                break;

            case 'fad':
            case 'fa-duotone':
                if ($this->config['license_type'] === 'pro') {
                    $recommendations[] = [
                        'style' => 'fa-duotone',
                        'reason' => 'Style à deux couleurs (Pro)',
                        'priority' => 1,
                    ];
                }

                break;
        }

        return $recommendations;
    }

    /**
     * Vérifier si un style est Pro uniquement
     */
    public function isProStyle(string $style): bool
    {
        $proStyles = ['fal', 'fad', 'fa-light', 'fa-duotone', 'fa-thin', 'fa-sharp'];

        return \in_array($style, $proStyles);
    }

    /**
     * Convertir une classe CSS complète
     */
    public function convertFullClass(string $cssClass): string
    {
        // Pattern pour matcher les classes Font Awesome
        $pattern = '/\b(fa[slrbad]|fas|far|fal|fab|fad|fa-solid|fa-regular|fa-light|fa-brands|fa-duotone)\s+(fa-[a-zA-Z0-9-]+)\b/';

        return preg_replace_callback($pattern, function (array $matches): string {
            $oldStyle = $matches[1];
            $iconName = $matches[2];
            $newStyle = $this->mapStyle($oldStyle);

            return $newStyle.' '.$iconName;
        }, $cssClass);
    }
}
