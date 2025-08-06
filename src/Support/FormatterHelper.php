<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Support;

/**
 * Helper pour le formatage de données
 */
class FormatterHelper
{
    /**
     * Obtenir le nombre de lignes d'un contenu à partir d'un offset
     */
    public static function getLineNumber(string $content, int $offset): int
    {
        return substr_count(substr($content, 0, $offset), "\n") + 1;
    }

    /**
     * Générer un ID unique court
     */
    public static function generateShortId(string $prefix = ''): string
    {
        $uniqueId = uniqid($prefix, true);

        // Extraire les 8 premiers caractères après le préfixe
        if ($prefix !== '') {
            $position = strpos($uniqueId, '_') ?: \strlen($prefix);

            return substr($uniqueId, $position + 1, 8);
        }

        return substr($uniqueId, 0, 8);
    }

    /**
     * Normaliser un chemin relatif
     */
    public static function normalizePath(string $path, string $basePath = ''): string
    {
        if ($basePath !== '') {
            $path = str_replace($basePath.'/', '', $path);
        }

        return trim($path, '/');
    }
}
