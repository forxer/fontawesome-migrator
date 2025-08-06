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
        $outlinedPatterns = [
            '/^fa-.*-o$/',          // fa-star-o, fa-heart-o
            '/^fa-envelope-o$/',    // Cas spécial
            '/^fa-file-.*-o$/',     // fa-file-text-o, etc.
        ];

        foreach ($outlinedPatterns as $pattern) {
            if (preg_match($pattern, $iconName)) {
                return true;
            }
        }

        return false;
    }
}
