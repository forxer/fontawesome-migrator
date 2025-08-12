<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Core;

use FontAwesome\Migrator\Contracts\FileScannerInterface;
use RuntimeException;

class VersionConfigurationService
{
    public function __construct(
        private readonly MigrationVersionManager $versionManager,
        private readonly FileScannerInterface $scanner
    ) {}

    /**
     * Configurer et valider les versions source et cible
     */
    /**
     * Détecter la version actuelle de FontAwesome en scannant les fichiers du projet
     */
    public function detectCurrentVersion(): ?string
    {
        $scanPaths = config('fontawesome-migrator.scan_paths', []);

        if (empty($scanPaths)) {
            return null;
        }

        // Scanner les fichiers pour détecter la version
        $files = $this->scanner->scanPaths($scanPaths);

        foreach ($files as $file) {
            if (is_file($file['path'])) {
                $content = file_get_contents($file['path']);

                if ($content !== false) {
                    $detected = $this->versionManager->detectVersion($content);

                    if ($detected !== 'unknown') {
                        return $detected;
                    }
                }
            }
        }

        return null;
    }

    /**
     * Obtenir les versions cibles disponibles pour une version source
     */
    public function getAvailableTargetVersions(string $sourceVersion): array
    {
        $supportedMigrations = $this->versionManager->getSupportedMigrations();

        return collect($supportedMigrations)
            ->where('from', $sourceVersion)
            ->pluck('to')
            ->toArray();
    }

    /**
     * Suggérer la meilleure version cible pour une version source
     */
    public function suggestTargetVersion(string $sourceVersion): ?string
    {
        $availableTargets = $this->getAvailableTargetVersions($sourceVersion);

        if ($availableTargets === []) {
            return null;
        }

        // Retourner la version la plus récente disponible
        return max($availableTargets);
    }

    public function configureVersions(?string $sourceVersion, ?string $targetVersion): array
    {
        // Détecter automatiquement la version source si non fournie
        if ($sourceVersion === null || $sourceVersion === '' || $sourceVersion === '0') {
            $sourceVersion = $this->detectCurrentVersion();

            if ($sourceVersion === null || $sourceVersion === '' || $sourceVersion === '0') {
                throw new RuntimeException('Impossible de détecter automatiquement la version FontAwesome actuelle.');
            }
        }

        // Suggérer version cible si non fournie
        if ($targetVersion === null || $targetVersion === '' || $targetVersion === '0') {
            $targetVersion = $this->suggestTargetVersion($sourceVersion);

            if ($targetVersion === null || $targetVersion === '' || $targetVersion === '0') {
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
