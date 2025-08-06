<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Configuration;

use Illuminate\Support\Facades\File;

/**
 * Service pour récupérer la version du package FontAwesome Migrator
 */
class PackageVersionService
{
    private ?string $cachedVersion = null;

    /**
     * Obtenir la version du package depuis le CHANGELOG.md
     */
    public function getVersion(): string
    {
        if ($this->cachedVersion !== null) {
            return $this->cachedVersion;
        }

        $changelogPath = __DIR__.'/../../CHANGELOG.md';

        if (File::exists($changelogPath)) {
            $content = File::get($changelogPath);

            // Chercher le premier titre de niveau 2 : format ## ou souligné avec ---
            if (preg_match('/^(\d+\.\d+\.\d+).*\n-+/m', $content, $matches) ||
                preg_match('/^## (\d+\.\d+\.\d+)/m', $content, $matches)) {
                $this->cachedVersion = $matches[1];

                return $this->cachedVersion;
            }
        }

        $this->cachedVersion = 'unknown';

        return $this->cachedVersion;
    }

    /**
     * Réinitialiser le cache (utile pour les tests)
     */
    public function clearCache(): void
    {
        $this->cachedVersion = null;
    }
}
