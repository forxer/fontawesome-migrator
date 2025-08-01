<?php

declare(strict_types=1);

namespace Konobit\FontAwesomeMigrator\Services;

/**
 * Service pour récupérer la version du package FontAwesome Migrator
 */
class PackageVersionService
{
    private static ?string $cachedVersion = null;

    /**
     * Obtenir la version du package depuis le CHANGELOG.md
     */
    public static function getVersion(): string
    {
        if (self::$cachedVersion !== null) {
            return self::$cachedVersion;
        }

        $changelogPath = __DIR__.'/../../CHANGELOG.md';

        if (file_exists($changelogPath)) {
            $content = file_get_contents($changelogPath);

            // Chercher le premier titre de niveau 2 : format ## ou souligné avec ---
            if (preg_match('/^(\d+\.\d+\.\d+).*\n-+/m', $content, $matches) ||
                preg_match('/^## (\d+\.\d+\.\d+)/m', $content, $matches)) {
                self::$cachedVersion = $matches[1];

                return self::$cachedVersion;
            }
        }

        self::$cachedVersion = 'unknown';

        return self::$cachedVersion;
    }

    /**
     * Réinitialiser le cache (utile pour les tests)
     */
    public static function clearCache(): void
    {
        self::$cachedVersion = null;
    }
}
