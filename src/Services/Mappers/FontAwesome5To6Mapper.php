<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Mappers;

use Exception;
use FontAwesome\Migrator\Contracts\VersionMapperInterface;
use FontAwesome\Migrator\Services\ConfigurationLoader;
use RuntimeException;

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

    private array $alternatives;

    public function __construct(
        private array $config,
        private readonly ConfigurationLoader $configLoader,
    ) {
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
            $this->alternatives = $this->configLoader->loadAlternatives('5', '6');
        } catch (Exception $exception) {
            throw new RuntimeException('Configuration JSON manquante pour FontAwesome 5→6: '.$exception->getMessage(), $exception->getCode(), $exception);
        }
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

        }

        // Vérifier si l'icône est Pro uniquement
        if (\in_array($iconName, $this->proOnlyIcons) || \in_array($result['new_name'], $this->proOnlyIcons)) {
            $result['pro_only'] = true;

            if (($this->config['license_type'] ?? 'free') === 'free') {
                $result['warnings'][] = 'Icône Pro uniquement: '.$iconName;
            }
        }

        return $result;
    }

    public function mapStyle(string $style, bool $withFallback = true): string
    {
        return $this->styleMappings[$style] ?? $style;
    }

    public function isProStyle(string $style): bool
    {
        $proStyles = ['fal', 'fad', 'fa-light', 'fa-duotone', 'fa-thin', 'fa-sharp'];

        return \in_array($style, $proStyles);
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

    public function getFreeAlternative(string $iconName): ?string
    {
        // Alternative Free pour icônes Pro/dépréciées (depuis JSON config)
        return $this->alternatives[$iconName] ?? null;
    }
}
