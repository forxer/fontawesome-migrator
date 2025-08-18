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

        // Icônes avec changements majeurs (renommées/dépréciées)
        if (\in_array($iconName, ['fa-home', 'fa-search'], true)) {
            $warnings[] = \sprintf("Icône '%s' renommée en FA6, vérifier contexte d'usage", $iconName);
        }

        return $warnings;
    }
}
