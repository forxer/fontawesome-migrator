<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Mappers;

/**
 * Mapper pour la migration FontAwesome 5 → 6
 * Basé sur les données de recherche documentées
 */
class FontAwesome5To6Mapper extends BaseVersionMapper
{
    public function getSourceVersion(): string
    {
        return '5';
    }

    public function getTargetVersion(): string
    {
        return '6';
    }

    protected function getSpecificWarnings(string $iconName, string $style): array
    {
        $warnings = [];

        // Logique spécifique FA5→FA6
        // Seuls les styles Pro nécessitent un avertissement (changement de licence potentiel)
        if ($this->requiresWarningForStyleChange($style)) {
            $newStyle = $this->mapStyle($style);
            $warnings[] = \sprintf("Style Pro FA5 '%s' → '%s' en FA6 - Vérifier licence", $style, $newStyle);
        }

        // Icônes avec changements majeurs
        if (\in_array($iconName, ['fa-home', 'fa-search'], true)) {
            $warnings[] = \sprintf("Icône '%s' renommée en FA6, vérifier contexte d'usage", $iconName);
        }

        return $warnings;
    }

    /**
     * Vérifier si un changement de style nécessite un avertissement
     * Seuls les styles Pro ou les cas problématiques devraient générer des warnings
     */
    private function requiresWarningForStyleChange(string $style): bool
    {
        // Avertir seulement pour les styles Pro (problème de licence potentiel)
        return \in_array($style, ['fal', 'fad'], true);
    }
}
