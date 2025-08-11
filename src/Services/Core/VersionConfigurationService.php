<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Core;

use RuntimeException;

class VersionConfigurationService
{
    public function __construct(
        private readonly MigrationVersionManager $versionManager
    ) {}

    /**
     * Configurer et valider les versions source et cible
     */
    public function configureVersions(?string $sourceVersion, ?string $targetVersion): array
    {
        // Détecter automatiquement la version source si non fournie
        if ($sourceVersion === null || $sourceVersion === '' || $sourceVersion === '0') {
            $sourceVersion = $this->versionManager->detectCurrentVersion();

            if (! $sourceVersion) {
                throw new RuntimeException('Impossible de détecter automatiquement la version FontAwesome actuelle.');
            }
        }

        // Suggérer version cible si non fournie
        if ($targetVersion === null || $targetVersion === '' || $targetVersion === '0') {
            $targetVersion = $this->versionManager->suggestTargetVersion($sourceVersion);

            if (! $targetVersion) {
                throw new RuntimeException(\sprintf('Aucune version cible disponible pour FontAwesome %s.', $sourceVersion));
            }
        }

        // Valider que la migration est supportée
        if (! $this->versionManager->isMigrationSupported($sourceVersion, $targetVersion)) {
            $supportedMigrations = $this->versionManager->getSupportedMigrations();
            $availableTargets = collect($supportedMigrations)
                ->where('from', $sourceVersion)
                ->pluck('to')
                ->join(', ');

            throw new RuntimeException(
                \sprintf('Migration FontAwesome %s→%s non supportée. ', $sourceVersion, $targetVersion).
                \sprintf('Versions cibles disponibles pour FA%s: %s', $sourceVersion, $availableTargets)
            );
        }

        return [
            'source_version' => $sourceVersion,
            'target_version' => $targetVersion,
        ];
    }
}
