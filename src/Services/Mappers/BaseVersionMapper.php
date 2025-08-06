<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Mappers;

use Exception;
use FontAwesome\Migrator\Contracts\VersionMapperInterface;
use FontAwesome\Migrator\Services\ConfigurationLoader;
use RuntimeException;

abstract class BaseVersionMapper implements VersionMapperInterface
{
    protected array $styleMappings = [];

    protected array $iconMappings = [];

    protected array $deprecatedIcons = [];

    protected array $newIcons = [];

    protected array $alternativeIcons = [];

    protected array $proOnlyIcons = [];

    protected bool $mappingsLoaded = false;

    public function __construct(
        protected ConfigurationLoader $configLoader
    ) {}

    abstract public function getSourceVersion(): string;

    abstract public function getTargetVersion(): string;

    /**
     * Chargement standardisé des mappings depuis ConfigurationLoader
     */
    protected function loadMappings(): void
    {
        if ($this->mappingsLoaded) {
            return;
        }

        try {
            $sourceVersion = $this->getSourceVersion();
            $targetVersion = $this->getTargetVersion();

            $this->styleMappings = $this->configLoader->loadStyleMappings($sourceVersion, $targetVersion);
            $this->iconMappings = $this->configLoader->loadIconMappings($sourceVersion, $targetVersion);
            $this->deprecatedIcons = $this->configLoader->loadDeprecatedIcons($sourceVersion, $targetVersion);
            $this->newIcons = $this->configLoader->loadNewIcons($sourceVersion, $targetVersion);
            $this->alternativeIcons = $this->configLoader->loadAlternatives($sourceVersion, $targetVersion);
            $this->proOnlyIcons = $this->configLoader->loadProOnlyIcons($sourceVersion, $targetVersion);

            $this->mappingsLoaded = true;
        } catch (Exception $exception) {
            throw new RuntimeException(
                'Configuration JSON manquante pour migration '.$sourceVersion.'→'.$targetVersion.': '.$exception->getMessage(),
                0,
                $exception
            );
        }
    }

    public function getIconMappings(): array
    {
        $this->loadMappings();

        return $this->iconMappings;
    }

    public function getStyleMappings(): array
    {
        $this->loadMappings();

        return $this->styleMappings;
    }

    public function getDeprecatedIcons(): array
    {
        $this->loadMappings();

        return $this->deprecatedIcons;
    }

    public function getNewIcons(): array
    {
        $this->loadMappings();

        return $this->newIcons;
    }

    public function getAlternativeIcons(): array
    {
        $this->loadMappings();

        return $this->alternativeIcons;
    }

    public function getProOnlyIcons(): array
    {
        $this->loadMappings();

        return $this->proOnlyIcons;
    }

    /**
     * Mapping d'icône avec informations détaillées
     */
    public function mapIcon(string $iconName, string $style = ''): array
    {
        $this->loadMappings();

        $newName = $this->iconMappings[$iconName] ?? $iconName;
        $isRenamed = $newName !== $iconName;
        $isDeprecated = isset($this->deprecatedIcons[$iconName]);
        $isProOnly = isset($this->proOnlyIcons[$iconName]);

        $warnings = [];

        // Logique spécifique à implémenter dans chaque mapper concret
        $warnings = array_merge($warnings, $this->getSpecificWarnings($iconName, $style));

        return [
            'new_name' => $newName,
            'renamed' => $isRenamed,
            'deprecated' => $isDeprecated,
            'pro_only' => $isProOnly,
            'warnings' => $warnings,
        ];
    }

    /**
     * Obtenir l'alternative gratuite pour une icône Pro
     */
    public function getFreeAlternative(string $iconName): ?string
    {
        $this->loadMappings();

        return $this->alternativeIcons[$iconName] ?? null;
    }

    /**
     * Warnings spécifiques à chaque version - à implémenter dans chaque mapper
     */
    abstract protected function getSpecificWarnings(string $iconName, string $style): array;

    public function mapStyle(string $style, bool $withFallback = true): string
    {
        $this->loadMappings();

        return $this->styleMappings[$style] ?? $style;
    }

    public function isProStyle(string $style): bool
    {
        $proStyles = ['fal', 'fad', 'fa-light', 'fa-duotone', 'fa-thin', 'fa-sharp'];

        return \in_array($style, $proStyles, true);
    }

    public function findSimilarIcons(string $iconName): array
    {
        $this->loadMappings();
        $similar = [];
        $iconName = str_replace('fa-', '', $iconName);

        // Recherche par correspondances partielles
        foreach (array_keys($this->iconMappings) as $existingIcon) {
            $existingName = str_replace('fa-', '', $existingIcon);
            similar_text($iconName, $existingName, $percent);

            if ($percent > 60) {
                $similar[] = [
                    'icon' => $existingIcon,
                    'reason' => 'Similar name',
                    'confidence' => $percent / 100,
                ];
            }
        }

        // Trier par confiance décroissante
        usort($similar, fn ($a, $b): int => $b['confidence'] <=> $a['confidence']);

        return \array_slice($similar, 0, 5);
    }

    public function getMappingStats(): array
    {
        $this->loadMappings();

        return [
            'renamed_icons' => \count(array_filter($this->iconMappings, fn ($new, $old): bool => $new !== $old, ARRAY_FILTER_USE_BOTH)),
            'deprecated_icons' => \count($this->deprecatedIcons),
            'pro_only_icons' => \count($this->proOnlyIcons),
            'new_icons' => \count($this->newIcons),
        ];
    }

    public function iconExistsInTarget(string $iconName): bool
    {
        $this->loadMappings();
        $mappedIcon = $this->iconMappings[$iconName] ?? $iconName;

        // L'icône existe si elle est dans les mappings ou les nouvelles icônes
        return isset($this->iconMappings[$iconName]) || \in_array($mappedIcon, $this->newIcons, true);
    }

    public function getDetectionPatterns(): array
    {
        $sourceVersion = $this->getSourceVersion();

        return match ($sourceVersion) {
            '4' => [
                '/\bfa\s+fa-[a-zA-Z0-9-]+\b/',
                '/fontawesome\.com\/font-awesome-4/',
                '/fa-envelope-o\b/',
                '/fa-star-o\b/',
            ],
            '5' => [
                '/\bfas\s+fa-[a-zA-Z0-9-]+\b/',
                '/\bfar\s+fa-[a-zA-Z0-9-]+\b/',
                '/\bfal\s+fa-[a-zA-Z0-9-]+\b/',
                '/\bfad\s+fa-[a-zA-Z0-9-]+\b/',
                '/fontawesome\.com\/releases\/v5/',
            ],
            '6' => [
                '/\bfa-solid\s+fa-[a-zA-Z0-9-]+\b/',
                '/\bfa-regular\s+fa-[a-zA-Z0-9-]+\b/',
                '/\bfa-light\s+fa-[a-zA-Z0-9-]+\b/',
                '/\bfa-duotone\s+fa-[a-zA-Z0-9-]+\b/',
                '/fontawesome\.com\/releases\/v6/',
            ],
            default => [],
        };
    }
}
