<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Core;

use FontAwesome\Migrator\Services\Configuration\FontAwesomePatternService;

class ProgressiveMigrationService
{
    public function __construct(
        private readonly MigrationProcessor $migrationProcessor,
        private readonly FontAwesomePatternService $patternService,
        private readonly FileScanner $fileScanner
    ) {}

    /**
     * Déterminer la séquence de migrations nécessaires
     */
    public function determineMigrationPath(array $files, string $sourceVersion, string $targetVersion): array
    {
        // Détecter toutes les versions présentes dans les fichiers
        $detectedVersions = $this->detectAllVersions($files);

        // Si des versions antérieures à la source sont détectées
        $earlierVersions = $this->getEarlierVersions($sourceVersion, $detectedVersions);

        if ($earlierVersions !== []) {
            $lowestVersion = $this->getLowestVersion($earlierVersions);

            return $this->buildMigrationSequence($lowestVersion, $targetVersion);
        }

        // Sinon, migration simple
        return [
            ['from' => $sourceVersion, 'to' => $targetVersion],
        ];
    }

    /**
     * Exécuter une séquence de migrations progressives
     */
    public function executeProgressiveMigration(array $files, array $migrationSequence, array $options): array
    {
        $results = [];
        $cumulativeStats = [
            'total_changes' => 0,
            'files_modified' => 0,
            'warnings' => [],
        ];

        foreach ($migrationSequence as $step) {
            info(\sprintf('📍 Étape de migration : %s → %s', $step['from'], $step['to']));

            // Mettre à jour les options avec les versions de cette étape
            $stepOptions = array_merge($options, [
                'source_version' => $step['from'],
                'target_version' => $step['to'],
            ]);

            // Exécuter la migration pour cette étape
            $stepResult = $this->migrationProcessor->process($files, $stepOptions);

            // Accumuler les résultats
            $results[] = [
                'step' => \sprintf('%s → %s', $step['from'], $step['to']),
                'result' => $stepResult,
            ];

            $cumulativeStats['total_changes'] += $stepResult['icons']['total_changes'] ?? 0;
            $cumulativeStats['files_modified'] += $stepResult['total_files_modified'] ?? 0;

            // Re-scanner les fichiers pour la prochaine étape
            // Car les fichiers ont été modifiés
            $files = $this->rescanFiles($options);
        }

        return [
            'sequence' => $migrationSequence,
            'steps' => $results,
            'cumulative' => $cumulativeStats,
        ];
    }

    /**
     * Détecter toutes les versions FontAwesome dans les fichiers
     */
    private function detectAllVersions(array $files): array
    {
        $allVersions = [];

        foreach ($files as $file) {
            $content = file_get_contents($file);
            $versions = $this->patternService->detectAllVersionsInContent($content);

            foreach ($versions as $version => $count) {
                if (! isset($allVersions[$version])) {
                    $allVersions[$version] = 0;
                }

                $allVersions[$version] += $count;
            }
        }

        return $allVersions;
    }

    /**
     * Obtenir les versions antérieures à la version source
     */
    private function getEarlierVersions(string $sourceVersion, array $detectedVersions): array
    {
        $versions = ['4', '5', '6', '7'];
        $sourceIndex = array_search($sourceVersion, $versions, true);

        $earlierVersions = [];

        foreach ($detectedVersions as $version => $count) {
            $versionIndex = array_search($version, $versions, true);

            if ($versionIndex !== false && $versionIndex < $sourceIndex && $count > 0) {
                $earlierVersions[$version] = $count;
            }
        }

        return $earlierVersions;
    }

    /**
     * Obtenir la version la plus basse détectée
     */
    private function getLowestVersion(array $versions): string
    {
        $allVersions = ['4', '5', '6', '7'];

        foreach ($allVersions as $version) {
            if (isset($versions[$version])) {
                return $version;
            }
        }

        return '5'; // Fallback
    }

    /**
     * Construire la séquence de migrations
     */
    private function buildMigrationSequence(string $fromVersion, string $toVersion): array
    {
        $versions = ['4', '5', '6', '7'];
        $fromIndex = array_search($fromVersion, $versions, true);
        $toIndex = array_search($toVersion, $versions, true);

        $sequence = [];

        for ($i = $fromIndex; $i < $toIndex; $i++) {
            $sequence[] = [
                'from' => $versions[$i],
                'to' => $versions[$i + 1],
            ];
        }

        return $sequence;
    }

    /**
     * Re-scanner les fichiers après modification
     */
    private function rescanFiles(array $options): array
    {
        $paths = $options['paths'] ?? ['.'];

        return $this->fileScanner->scanPaths($paths);
    }
}
