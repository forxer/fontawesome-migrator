<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Mappers;

/**
 * Mapper pour la migration FontAwesome 4 → 5
 * Basé sur les données de recherche documentées
 */
class FontAwesome4To5Mapper extends BaseVersionMapper
{
    public function getSourceVersion(): string
    {
        return '4';
    }

    public function getTargetVersion(): string
    {
        return '5';
    }

    protected function getSpecificWarnings(string $iconName, string $style): array
    {
        $warnings = [];

        // Logique spécifique FA4→FA5
        if ($this->isOutlinedIcon($iconName)) {
            $warnings[] = \sprintf("Icône outlined FA4 '%s' → style 'far' en FA5", $iconName);
        }

        // Détection changement style par défaut
        if ($iconName === 'fa-star' && $style === 'fa') {
            $warnings[] = "'fa-star' devient 'fas fa-star' (solid) par défaut en FA5";
        }

        return $warnings;
    }

    /**
     * Vérifier si une icône FA4 est "outlined" (suffixe -o)
     */
    private function isOutlinedIcon(string $iconName): bool
    {
        // Simplifier : toute icône finissant par -o est outlined
        return str_ends_with($iconName, '-o');
    }

    /**
     * Override mapStyle pour gérer les cas spéciaux FA4
     */
    public function mapStyle(string $style, bool $withFallback = true): string
    {
        // Pour FA4, le style par défaut est toujours 'fa' qui devient 'fas'
        return parent::mapStyle($style, $withFallback);
    }

    /**
     * Override mapIcon pour gérer les icons outlined
     */
    public function mapIcon(string $iconName, string $style = ''): array
    {
        $result = parent::mapIcon($iconName, $style);

        // Si c'est une icône outlined, elle doit utiliser le style 'far' (regular)
        if ($this->isOutlinedIcon($iconName)) {
            // L'icône renommée n'a plus le suffixe -o
            $result['style_override'] = 'far';
            $result['warnings'][] = "Icon outlined : utilise 'far' (regular) au lieu de 'fas' (solid)";
        }

        return $result;
    }
}
