<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Core;

class VersionConfigurationService
{
    public function __construct(
        private MigrationVersionManager $versionManager
    ) {}

    /**
     * Configurer et valider les versions source et cible
     */
    public function configureVersions(?string $sourceVersion, ?string $targetVersion): array
    {
        // Détecter automatiquement la version source si non fournie
        if (! $sourceVersion) {
            $sourceVersion = $this->versionManager->detectCurrentVersion();

            if (! $sourceVersion) {
                throw new \RuntimeException('Impossible de détecter automatiquement la version FontAwesome actuelle.');
            }
        }

        // Suggérer version cible si non fournie
        if (! $targetVersion) {
            $targetVersion = $this->versionManager->suggestTargetVersion($sourceVersion);

            if (! $targetVersion) {
                throw new \RuntimeException("Aucune version cible disponible pour FontAwesome {$sourceVersion}.");
            }
        }

        // Valider que la migration est supportée
        if (! $this->versionManager->isMigrationSupported($sourceVersion, $targetVersion)) {
            $supportedMigrations = $this->versionManager->getSupportedMigrations();
            $availableTargets = collect($supportedMigrations)
                ->where('from', $sourceVersion)
                ->pluck('to')
                ->join(', ');

            throw new \RuntimeException(
                "Migration FontAwesome {$sourceVersion}→{$targetVersion} non supportée. ".
                "Versions cibles disponibles pour FA{$sourceVersion}: {$availableTargets}"
            );
        }

        return [
            'source_version' => $sourceVersion,
            'target_version' => $targetVersion,
        ];
    }
}
