<?php

namespace FontAwesome\Migrator\Services;

class StyleMapper
{
    protected array $styleMapping;

    public function __construct()
    {
        $this->initializeStyleMapping();
    }

    /**
     * Obtenir la configuration actuelle
     */
    protected function getConfig(): array
    {
        return config('fontawesome-migrator', []);
    }

    /**
     * Initialiser le mapping des styles FA5 vers FA6
     */
    protected function initializeStyleMapping(): void
    {
        // Mapping COMPLET de tous les styles FA5 → FA6
        // Peu importe la licence, tous les styles sont reconnus et convertis
        $this->styleMapping = [
            // Styles de base (Free)
            'fas' => 'fa-solid',
            'far' => 'fa-regular',
            'fab' => 'fa-brands',
            'fa-solid' => 'fa-solid',
            'fa-regular' => 'fa-regular',
            'fa-brands' => 'fa-brands',

            // Styles Pro (toujours mappés pour la conversion)
            'fal' => 'fa-light',
            'fad' => 'fa-duotone',
            'fa-light' => 'fa-light',
            'fa-duotone' => 'fa-duotone',

            // Nouveaux styles FA6 Pro
            'fa-thin' => 'fa-thin',
            'fa-sharp' => 'fa-sharp',
        ];
    }

    /**
     * Mapper un style FA5 vers FA6
     */
    public function mapStyle(string $fa5Style): string
    {
        // Style non reconnu, retourner tel quel
        return $this->styleMapping[$fa5Style] ?? $fa5Style;
    }

    /**
     * Mapper un style avec fallback selon la licence
     */
    public function mapStyleWithFallback(string $fa5Style): string
    {
        $mappedStyle = $this->mapStyle($fa5Style);

        // Appliquer le fallback seulement si licence Free ET style Pro
        $config = $this->getConfig();

        if (($config['license_type'] ?? 'free') === 'free' && $this->isProStyle($mappedStyle)) {
            return $this->getFallbackStyle();
        }

        return $mappedStyle;
    }

    /**
     * Obtenir le style de fallback configuré
     */
    protected function getFallbackStyle(): string
    {
        $config = $this->getConfig();
        $fallback = $config['fallback_strategy'] ?? 'solid';

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
                $config = $this->getConfig();

                if (($config['license_type'] ?? 'free') === 'pro') {
                    $recommendations[] = [
                        'style' => 'fa-light',
                        'reason' => 'Style fin et élégant (Pro)',
                        'priority' => 1,
                    ];

                    if ($config['pro_styles']['thin'] ?? false) {
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
                $config = $this->getConfig();

                if (($config['license_type'] ?? 'free') === 'pro') {
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
        $pattern = '/\b(fa[slrbad]|fas|far|fal|fab|fad|fa-solid|fa-regular|fa-light|fa-brands|fa-duotone|fa-thin|fa-sharp)\s+(fa-[a-zA-Z0-9-]+)\b/';

        return preg_replace_callback($pattern, function (array $matches): string {
            $oldStyle = $matches[1];
            $iconName = $matches[2];
            $newStyle = $this->mapStyleWithFallback($oldStyle);

            return $newStyle.' '.$iconName;
        }, $cssClass);
    }
}
