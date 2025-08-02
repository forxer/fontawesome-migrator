<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Contracts;

/**
 * Interface pour les mappers de versions FontAwesome
 */
interface VersionMapperInterface
{
    /**
     * Obtenir les mappings d'icônes pour cette migration
     *
     * @return array<string, string> Tableau associatif [ancien_nom => nouveau_nom]
     */
    public function getIconMappings(): array;

    /**
     * Obtenir les mappings de styles pour cette migration
     *
     * @return array<string, string> Tableau associatif [ancien_style => nouveau_style]
     */
    public function getStyleMappings(): array;

    /**
     * Obtenir les icônes dépréciées dans cette migration
     *
     * @return array<string> Liste des icônes supprimées/dépréciées
     */
    public function getDeprecatedIcons(): array;

    /**
     * Obtenir les icônes disponibles uniquement en Pro
     *
     * @return array<string> Liste des icônes Pro uniquement
     */
    public function getProOnlyIcons(): array;

    /**
     * Obtenir les nouvelles icônes introduites dans cette version
     *
     * @return array<string> Liste des nouvelles icônes
     */
    public function getNewIcons(): array;

    /**
     * Mapper une icône avec informations détaillées
     *
     * @param  string  $iconName  Nom de l'icône à mapper
     * @param  string  $style  Style de l'icône (optionnel)
     * @return array{
     *   new_name: string,
     *   found: bool,
     *   deprecated: bool,
     *   pro_only: bool,
     *   renamed: bool,
     *   warnings: array<string>
     * }
     */
    public function mapIcon(string $iconName, string $style = 'fas'): array;

    /**
     * Mapper un style avec gestion des fallbacks
     *
     * @param  string  $style  Style à mapper
     * @param  bool  $withFallback  Appliquer les fallbacks selon la licence
     * @return string Style mappé
     */
    public function mapStyle(string $style, bool $withFallback = true): string;

    /**
     * Vérifier si un style est Pro uniquement
     *
     * @param  string  $style  Style à vérifier
     * @return bool True si le style est Pro uniquement
     */
    public function isProStyle(string $style): bool;

    /**
     * Obtenir le fallback gratuit pour une icône Pro
     *
     * @param  string  $proIcon  Icône Pro
     * @return string|null Fallback gratuit ou null si aucun
     */
    public function getFreeFallback(string $proIcon): ?string;

    /**
     * Rechercher des icônes similaires
     *
     * @param  string  $iconName  Nom de l'icône recherchée
     * @return array<array{icon: string, reason: string, confidence: float}>
     */
    public function findSimilarIcons(string $iconName): array;

    /**
     * Obtenir des statistiques sur les mappings
     *
     * @return array{
     *   renamed_icons: int,
     *   deprecated_icons: int,
     *   pro_only_icons: int,
     *   new_icons: int
     * }
     */
    public function getMappingStats(): array;

    /**
     * Obtenir la version source supportée par ce mapper
     *
     * @return string Version source (ex: "4", "5", "6")
     */
    public function getSourceVersion(): string;

    /**
     * Obtenir la version cible supportée par ce mapper
     *
     * @return string Version cible (ex: "5", "6", "7")
     */
    public function getTargetVersion(): string;

    /**
     * Valider qu'une icône existe dans la version cible
     *
     * @param  string  $iconName  Nom de l'icône
     * @return bool True si l'icône existe dans la version cible
     */
    public function iconExistsInTarget(string $iconName): bool;

    /**
     * Obtenir les patterns de détection pour cette version source
     *
     * @return array<string> Patterns regex pour détecter cette version
     */
    public function getDetectionPatterns(): array;
}
