<?php

namespace FontAwesome\Migrator\Services;

use FontAwesome\Migrator\Contracts\VersionMapperInterface;

/**
 * Service de mapping des styles FontAwesome
 * Adapté pour utiliser l'architecture multi-versions
 */
class StyleMapper
{
    protected array $styleMapping;

    protected MigrationVersionManager $versionManager;

    protected ?VersionMapperInterface $currentMapper = null;

    protected string $sourceVersion = '5';

    protected string $targetVersion = '6';

    public function __construct()
    {
        $this->versionManager = new MigrationVersionManager();

        // Déterminer les versions depuis la config si disponible
        $config = $this->getConfig();
        $this->sourceVersion = $config['source_version'] ?? '5';
        $this->targetVersion = $config['target_version'] ?? '6';

        // Initialiser le mapper et le mapping pour compatibilité
        $this->initializeMapper();
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
     * Initialiser le mapper pour les versions configurées
     */
    protected function initializeMapper(): void
    {
        $this->currentMapper = $this->versionManager->createMapper(
            $this->sourceVersion,
            $this->targetVersion
        );
    }

    /**
     * Initialiser le mapping des styles FA5 vers FA6
     */
    protected function initializeStyleMapping(): void
    {
        // Utiliser les mappings du mapper multi-versions
        $this->styleMapping = $this->currentMapper->getStyleMappings();
    }

    /**
     * Définir les versions de migration
     */
    public function setVersions(string $sourceVersion, string $targetVersion): self
    {
        $this->sourceVersion = $sourceVersion;
        $this->targetVersion = $targetVersion;
        $this->initializeMapper();
        $this->initializeStyleMapping();

        return $this;
    }

    /**
     * Mapper un style FA5 vers FA6
     */
    public function mapStyle(string $fa5Style): string
    {
        return $this->currentMapper->mapStyle($fa5Style, false);
    }

    /**
     * Mapper un style avec fallback selon la licence
     */
    public function mapStyleWithFallback(string $fa5Style): string
    {
        return $this->currentMapper->mapStyle($fa5Style, true);
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
        return $this->currentMapper->isProStyle($style);
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

    /**
     * Obtenir les versions de migration actuelles
     */
    public function getMigrationVersions(): array
    {
        return [
            'source' => $this->sourceVersion,
            'target' => $this->targetVersion,
        ];
    }

    /**
     * Propriétés pour compatibilité (accès direct)
     */
    public function getStyleMapping(): array
    {
        return $this->styleMapping;
    }
}
