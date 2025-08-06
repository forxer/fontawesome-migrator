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
        if ($this->requiresStyleChange($style)) {
            $newStyle = $this->mapStyle($style);
            $warnings[] = \sprintf("Style FA5 '%s' → '%s' en FA6", $style, $newStyle);
        }

        // Icônes avec changements majeurs
        if (\in_array($iconName, ['fa-home', 'fa-search'], true)) {
            $warnings[] = \sprintf("Icône '%s' renommée en FA6, vérifier contexte d'usage", $iconName);
        }

        return $warnings;
    }

    /**
     * Vérifier si un style nécessite un changement FA5→FA6
     */
    private function requiresStyleChange(string $style): bool
    {
        return \in_array($style, ['fas', 'far', 'fal', 'fab', 'fad'], true);
    }
}
