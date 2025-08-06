<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Mappers;

/**
 * Mapper pour la migration FontAwesome 6 → 7
 * Basé sur les données de recherche documentées
 */
class FontAwesome6To7Mapper extends BaseVersionMapper
{
    public function getSourceVersion(): string
    {
        return '6';
    }

    public function getTargetVersion(): string
    {
        return '7';
    }

    protected function getSpecificWarnings(string $iconName, string $style): array
    {
        $warnings = [];

        // Icônes avec changements majeurs FA6→FA7
        if ($this->hasFA7Changes($iconName)) {
            $warnings[] = \sprintf("Icône '%s' modifiée en FA7, vérifier rendu visuel", $iconName);
        }

        // Fixed width par défaut en FA7
        if ($style === 'fa-fw') {
            $warnings[] = "Fixed width est maintenant par défaut en FA7, classe 'fa-fw' peut être supprimée";
        }

        return $warnings;
    }

    /**
     * Vérifier si une icône a des changements spécifiques FA7
     */
    private function hasFA7Changes(string $iconName): bool
    {
        $fa7ChangedIcons = [
            'fa-accessibility',
            'fa-universal-access',
            'fa-wheelchair',
            'fa-sign-language',
            'fa-assistive-listening-systems',
            'fa-audio-description',
            'fa-braille',
            'fa-closed-captioning',
        ];

        return \in_array($iconName, $fa7ChangedIcons, true);
    }
}
