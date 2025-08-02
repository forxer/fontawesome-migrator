<?php

namespace FontAwesome\Migrator\Services;

use FontAwesome\Migrator\Contracts\VersionMapperInterface;

/**
 * Service de mapping d'icônes FontAwesome
 * Adapté pour utiliser l'architecture multi-versions
 */
class IconMapper
{
    protected array $config;

    protected MigrationVersionManager $versionManager;

    protected ?VersionMapperInterface $currentMapper = null;

    protected string $sourceVersion = '5';

    protected string $targetVersion = '6';

    public function __construct()
    {
        $this->config = config('fontawesome-migrator');
        $this->versionManager = new MigrationVersionManager();

        // Déterminer les versions depuis la config si disponible
        $this->sourceVersion = $this->config['source_version'] ?? '5';
        $this->targetVersion = $this->config['target_version'] ?? '6';

        // Initialiser le mapper par défaut (5→6 pour compatibilité)
        $this->initializeMapper();
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
     * Définir les versions de migration
     */
    public function setVersions(string $sourceVersion, string $targetVersion): self
    {
        $this->sourceVersion = $sourceVersion;
        $this->targetVersion = $targetVersion;
        $this->initializeMapper();

        return $this;
    }

    /**
     * Détecter automatiquement la version source depuis le contenu
     */
    public function detectSourceVersion(string $content): string
    {
        return $this->versionManager->detectVersion($content);
    }

    /**
     * Mapper une icône simple (compatibilité API existante)
     */
    public function mapIcon(string $iconName): string
    {
        $result = $this->currentMapper->mapIcon($iconName);

        return $result['new_name'];
    }

    /**
     * Mapper une icône avec détails complets
     */
    public function mapIconDetailed(string $iconName, string $style = 'fas'): array
    {
        return $this->currentMapper->mapIcon($iconName, $style);
    }

    /**
     * Vérifier si un style est Pro uniquement
     */
    public function isProOnly(string $style): bool
    {
        return $this->currentMapper->isProStyle($style);
    }

    /**
     * Obtenir le style de fallback pour un style Pro
     */
    public function getFallbackStyle(string $proStyle): string
    {
        $fallback = $this->currentMapper->mapStyle($proStyle, true);

        return str_replace(['fas', 'far', 'fab'], ['fa-solid', 'fa-regular', 'fa-brands'], $fallback);
    }

    /**
     * Rechercher des icônes similaires
     */
    public function findSimilarIcons(string $iconName): array
    {
        return $this->currentMapper->findSimilarIcons($iconName);
    }

    /**
     * Valider qu'une icône existe en FA6
     */
    public function iconExistsInFA6(string $iconName): bool
    {
        return $this->currentMapper->iconExistsInTarget($iconName);
    }

    /**
     * Obtenir des statistiques sur les mappings
     */
    public function getMappingStats(): array
    {
        return $this->currentMapper->getMappingStats();
    }

    /**
     * Méthodes de compatibilité pour accès direct aux données
     * (utilisées dans l'application existante)
     */

    /**
     * Obtenir une alternative pour une icône dépréciée
     */
    protected function getAlternativeIcon(string $deprecatedIcon): ?string
    {
        $fallback = $this->currentMapper->getFreeAlternative($deprecatedIcon);

        if ($fallback !== null) {
            return $fallback;
        }

        // Si pas de fallback, essayer le mapping direct
        $result = $this->currentMapper->mapIcon($deprecatedIcon);

        return $result['renamed'] ? $result['new_name'] : null;
    }

    /**
     * Obtenir une alternative Free pour une icône Pro (compatibilité FontAwesome)
     */
    protected function getFreeAlternative(string $iconName): ?string
    {
        return $this->currentMapper->getFreeAlternative($iconName);
    }

    /**
     * Propriétés pour compatibilité (accès direct)
     * Ces getters permettent l'accès aux données pour les tests existants
     */
    public function getRenamedIcons(): array
    {
        return $this->currentMapper->getIconMappings();
    }

    public function getDeprecatedIcons(): array
    {
        return $this->currentMapper->getDeprecatedIcons();
    }

    public function getProOnlyIcons(): array
    {
        return $this->currentMapper->getProOnlyIcons();
    }

    public function getNewIcons(): array
    {
        return $this->currentMapper->getNewIcons();
    }

    /**
     * Méthode utilitaire pour obtenir le mapper actuel
     * (utile pour les tests ou extensions)
     */
    public function getCurrentMapper(): VersionMapperInterface
    {
        return $this->currentMapper;
    }

    /**
     * Obtenir les versions de migration actuelles
     */
    public function getMigrationVersions(): array
    {
        return [
            'source' => $this->sourceVersion,
            'target' => $this->targetVersion,
            'detected' => null, // Peut être rempli après détection
        ];
    }

    /**
     * Obtenir un rapport de compatibilité pour la migration actuelle
     */
    public function getCompatibilityReport(): array
    {
        return $this->versionManager->getCompatibilityReport(
            $this->sourceVersion,
            $this->targetVersion
        );
    }
}
