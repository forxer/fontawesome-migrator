<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Core;

use FontAwesome\Migrator\Contracts\BackupManagerInterface;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use FontAwesome\Migrator\Services\Configuration\FontAwesomePatternService;

use function Laravel\Prompts\info;

class MigrationProcessor
{
    public function __construct(
        private readonly IconReplacer $replacer,
        private readonly AssetMigrator $assetMigrator,
        private readonly MigrationVersionManager $versionManager,
        private readonly MetadataManagerInterface $metadata,
        private readonly BackupManagerInterface $backupManager,
        private readonly FontAwesomePatternService $patternService
    ) {}

    /**
     * Traiter la migration des icônes
     */
    public function processIcons(array $files, array $options): array
    {
        // Skip si assets-only
        if ($options['assets_only']) {
            info('⏩ Migration des icônes ignorée (mode assets-only)');

            return [];
        }

        if ($files === []) {
            return [];
        }

        $dryRun = $options['dry_run'];
        $sourceVersion = $options['source_version'];
        $targetVersion = $options['target_version'];

        info(\sprintf('🔄 Migration des icônes FontAwesome %s → %s', $sourceVersion, $targetVersion).($dryRun ? ' (dry-run)' : ''));

        // Traiter les fichiers
        $fileResults = $this->replacer->processFiles($files, $dryRun, $sourceVersion, $targetVersion);

        // Calculer les statistiques
        $totalChanges = 0;
        $modifiedFiles = [];

        foreach ($fileResults as $result) {
            if ($result['success'] && $result['changes_count'] > 0) {
                $totalChanges += $result['changes_count'];
                $modifiedFiles[] = $result['file'];
            }
        }

        // Afficher le résumé
        if ($totalChanges > 0) {
            info(\sprintf('✨ %s icône(s) migrée(s) dans ', $totalChanges).\count($modifiedFiles).' fichier(s)');
        } else {
            info('ℹ️ Aucune icône FontAwesome trouvée nécessitant une migration');
        }

        return [
            'file_results' => $fileResults,
            'total_changes' => $totalChanges,
            'modified_files' => $modifiedFiles,
        ];
    }

    /**
     * Traiter la migration des assets
     */
    public function processAssets(array $files, array $options): array
    {
        // Skip si icons-only
        if ($options['icons_only']) {
            info('⏩ Migration des assets ignorée (mode icons-only)');

            return [];
        }

        $dryRun = $options['dry_run'];
        $sourceVersion = $options['source_version'];
        $targetVersion = $options['target_version'];

        info(\sprintf('🎨 Migration des assets FontAwesome %s → %s', $sourceVersion, $targetVersion).($dryRun ? ' (dry-run)' : ''));

        // Configurer la version cible dans AssetMigrator
        $this->assetMigrator->setTargetVersion($targetVersion);

        // Traiter les assets dans chaque fichier
        $results = [
            'total_assets' => 0,
            'modified_files' => [],
        ];

        foreach ($files as $fileData) {
            $filePath = $fileData['path'];

            $content = file_get_contents($filePath);

            $migratedContent = $this->assetMigrator->migrateAssets($filePath, $content);

            if ($migratedContent !== $content) {
                $results['modified_files'][] = $filePath;
                $results['total_assets']++;

                if (! $dryRun) {
                    file_put_contents($filePath, $migratedContent);
                }
            }
        }

        // Afficher le résumé
        if ($results['total_assets'] > 0) {
            info(\sprintf('📦 %d fichier(s) avec assets migrés', $results['total_assets']));
        } else {
            info('ℹ️ Aucun asset FontAwesome trouvé nécessitant une migration');
        }

        return $results;
    }

    /**
     * Traiter la migration complète
     */
    public function process(array $files, array $options): array
    {
        // Réinitialiser le compteur de backups pour cette migration
        $this->backupManager->resetBackupCount();

        // Valider les prérequis de migration et obtenir la séquence progressive si nécessaire
        $migrationSequence = $this->validateMigrationPrerequisites($files, $options);

        // Si une séquence progressive est détectée et activée (par défaut)
        if ($migrationSequence !== [] && ! ($options['no_progressive'] ?? false)) {
            return $this->executeProgressiveMigration($files, $migrationSequence, $options);
        }

        // Configurer le mapper pour cette migration
        $mapper = $this->versionManager->createMapper(
            $options['source_version'],
            $options['target_version']
        );
        $this->replacer->setMapper($mapper);

        // Traiter les icônes sauf si --assets-only
        $iconResults = [];

        if (! ($options['assets_only'] ?? false)) {
            $iconResults = $this->processIcons($files, $options);
        }

        // Traiter les assets sauf si --icons-only
        $assetResults = [];

        if (! ($options['icons_only'] ?? false)) {
            $assetResults = $this->processAssets($files, $options);
        }

        // Consolider tous les file_results
        $allFileResults = array_merge(
            $iconResults['file_results'] ?? []
        );

        $results = [
            'icons' => $iconResults,
            'assets' => $assetResults,
            'file_results' => $allFileResults,
            'total_files_processed' => \count($files),
            'total_files_modified' => \count(array_unique(array_merge(
                $iconResults['modified_files'] ?? [],
                $assetResults['modified_files'] ?? []
            ))),
        ];

        // Finaliser la migration
        $this->finalizeMigration($results, $options);

        return $results;
    }

    /**
     * Finaliser et sauvegarder la migration
     */
    private function finalizeMigration(array $results, array $options): void
    {
        $dryRun = $options['dry_run'] ?? false;

        // Récupérer les résultats par fichier
        $fileResults = $results['icons']['file_results'] ?? [];

        // Préparer les statistiques pour les métadonnées
        $stats = [
            'total_files' => $results['total_files_processed'],
            'modified_files' => $results['total_files_modified'],
            'total_changes' => ($results['icons']['total_changes'] ?? 0) + ($results['assets']['total_assets'] ?? 0),
            'icons_migrated' => $results['icons']['total_changes'] ?? 0,
            'assets_migrated' => $results['assets']['total_assets'] ?? 0,
            'backups_count' => $this->backupManager->getBackupCount(),
            'migration_success' => true,
            'changes_by_type' => [
                'icons' => $results['icons']['total_changes'] ?? 0,
                'assets' => $results['assets']['total_assets'] ?? 0,
            ],
        ];

        // Enrichir les warnings avant stockage
        $enrichedWarnings = $this->extractWarnings($fileResults);

        // Stocker les résultats dans les métadonnées (avec le format attendu par MigrationResultsService)
        $this->metadata->storeMigrationResults($fileResults, $stats, $enrichedWarnings);
        $this->metadata->completeMigration();

        // Sauvegarder les métadonnées (première passe)
        $filePath = $this->metadata->saveToFile();

        // Calculer et ajouter la taille du répertoire de migration (deuxième passe)
        $this->metadata->updateMigrationSize();
        $this->metadata->saveToFile($filePath);

        // Afficher le message de succès si nécessaire
        if (! $dryRun && $results['total_files_modified'] > 0) {
            info('');
            info('✅ Migration terminée avec succès !');
            info('📄 Rapport de migration disponible dans le dossier des migrations');
        }
    }

    /**
     * Extraire et enrichir les warnings depuis les résultats
     */
    private function extractWarnings(array $results): array
    {
        $enrichedWarnings = [];

        foreach ($results as $result) {
            $filePath = $result['file'] ?? 'Fichier inconnu';

            // Collecter les changements qui génèrent des avertissements
            if (! empty($result['changes'])) {
                foreach ($result['changes'] as $changeIndex => $change) {
                    // Seulement les types qui génèrent des avertissements
                    $warningTypes = ['pro_fallback', 'renamed_icon', 'deprecated_icon', 'manual_review'];

                    if (\in_array($change['type'] ?? '', $warningTypes)) {
                        // Chercher le warning correspondant dans la liste
                        $warningMessage = null;

                        if (! empty($result['warnings']) && isset($result['warnings'][$changeIndex])) {
                            $warningMessage = $result['warnings'][$changeIndex];
                        } else {
                            // Fallback si pas de correspondance exacte
                            foreach ($result['warnings'] ?? [] as $warning) {
                                if (str_contains((string) $warning, $change['from'] ?? '')) {
                                    $warningMessage = $warning;
                                    break;
                                }
                            }
                        }

                        $enrichedWarnings[] = [
                            'file' => $filePath,
                            'type' => $change['type'],
                            'from' => $change['from'] ?? '',
                            'to' => $change['to'] ?? '',
                            'message' => $warningMessage ?? 'Avertissement générique',
                            'line' => $change['line'] ?? null,
                            'context' => $change['context'] ?? '',
                        ];
                    }
                }
            }
        }

        return $enrichedWarnings;
    }

    /**
     * Valider les prérequis pour une migration
     * Retourne une séquence de migration progressive si nécessaire
     */
    private function validateMigrationPrerequisites(array $files, array $options): array
    {
        $sourceVersion = $options['source_version'];
        $targetVersion = $options['target_version'];

        info(\sprintf('🔍 Validation des prérequis pour migration %s → %s', $sourceVersion, $targetVersion));

        // Utiliser les services existants pour détecter les versions
        $detectedVersions = $this->detectVersionsInFiles($files);

        if ($detectedVersions !== []) {
            foreach ($detectedVersions as $version => $count) {
                info(\sprintf('   Détecté: FontAwesome %s (%s occurrences)', $version, $count));
            }

            // Vérifier si des versions antérieures sont présentes
            return $this->checkForEarlierVersions($detectedVersions, $sourceVersion, $targetVersion);
        }

        return []; // Pas de séquence progressive nécessaire
    }

    /**
     * Vérifier et proposer une migration progressive si nécessaire
     * Retourne la séquence de migration ou un tableau vide
     */
    private function checkForEarlierVersions(array $detectedVersions, string $sourceVersion, string $targetVersion): array
    {
        $versions = ['4', '5', '6', '7'];
        $sourceIndex = array_search($sourceVersion, $versions, true);

        $hasEarlierVersions = false;
        $lowestVersion = $sourceVersion;
        $lowestVersionIndex = $sourceIndex;

        foreach ($detectedVersions as $version => $count) {
            if ($count > 0) {
                // Convertir la version en string si nécessaire
                $version = (string) $version;
                $versionIndex = array_search($version, $versions, true);

                if ($versionIndex !== false && $versionIndex < $sourceIndex) {
                    $hasEarlierVersions = true;

                    if ($versionIndex < $lowestVersionIndex) {
                        $lowestVersion = $version;
                        $lowestVersionIndex = $versionIndex;
                    }
                }
            }
        }

        if ($hasEarlierVersions) {
            info('');
            info(\sprintf('⚠️  ATTENTION: Des icônes FontAwesome %s ont été détectées !', $lowestVersion));
            info('   Migration progressive automatique :');

            // Construire la séquence de migrations
            $sequence = $this->buildMigrationSequence($lowestVersion, $targetVersion, $versions);

            foreach ($sequence as $step) {
                info(\sprintf('   → %s → %s', $step['from'], $step['to']));
            }

            info('   Utilisez --no-progressive pour désactiver cette fonctionnalité');

            return $sequence;
        }

        return [];
    }

    /**
     * Construire la séquence de migrations nécessaires
     */
    private function buildMigrationSequence(string $fromVersion, string $toVersion, array $versions): array
    {
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
     * Exécuter une migration progressive
     */
    private function executeProgressiveMigration(array $files, array $migrationSequence, array $options): array
    {
        $allResults = [];
        $cumulativeStats = [
            'total_files' => 0,
            'modified_files' => 0,
            'total_changes' => 0,
        ];

        foreach ($migrationSequence as $step) {
            info('');
            info(\sprintf('🔄 Étape : FontAwesome %s → %s', $step['from'], $step['to']));

            // Préparer les options pour cette étape
            $stepOptions = array_merge($options, [
                'source_version' => $step['from'],
                'target_version' => $step['to'],
                'no_progressive' => true, // Éviter la récursion
            ]);

            // Exécuter cette étape de migration
            $stepResult = $this->processSingleMigration($files, $stepOptions);

            $allResults[] = [
                'step' => \sprintf('%s → %s', $step['from'], $step['to']),
                'result' => $stepResult,
            ];

            // Accumuler les statistiques
            $cumulativeStats['total_files'] = max($cumulativeStats['total_files'], $stepResult['total_files_processed'] ?? 0);
            $cumulativeStats['modified_files'] += $stepResult['total_files_modified'] ?? 0;
            $cumulativeStats['total_changes'] += ($stepResult['icons']['total_changes'] ?? 0) + ($stepResult['assets']['total_assets'] ?? 0);
        }

        info('');
        info('✅ Migration progressive terminée !');
        info('   Total des modifications : '.$cumulativeStats['total_changes']);

        // Retourner un résultat consolidé
        return [
            'progressive' => true,
            'steps' => $allResults,
            'cumulative' => $cumulativeStats,
            'total_files_processed' => $cumulativeStats['total_files'],
            'total_files_modified' => $cumulativeStats['modified_files'],
        ];
    }

    /**
     * Exécuter une migration simple (une étape)
     */
    private function processSingleMigration(array $files, array $options): array
    {
        // Code de migration normale (extrait de la méthode process)
        // Configurer le mapper pour cette migration
        $mapper = $this->versionManager->createMapper(
            $options['source_version'],
            $options['target_version']
        );
        $this->replacer->setMapper($mapper);

        // Traiter les icônes sauf si --assets-only
        $iconResults = [];

        if (! ($options['assets_only'] ?? false)) {
            $iconResults = $this->processIcons($files, $options);
        }

        // Traiter les assets sauf si --icons-only
        $assetResults = [];

        if (! ($options['icons_only'] ?? false)) {
            $assetResults = $this->processAssets($files, $options);
        }

        // Consolider tous les file_results
        $allFileResults = array_merge(
            $iconResults['file_results'] ?? []
        );

        $results = [
            'icons' => $iconResults,
            'assets' => $assetResults,
            'file_results' => $allFileResults,
            'total_files_processed' => \count($files),
            'total_files_modified' => \count(array_unique(array_merge(
                $iconResults['modified_files'] ?? [],
                $assetResults['modified_files'] ?? []
            ))),
        ];

        // Finaliser la migration
        $this->finalizeMigration($results, $options);

        return $results;
    }

    /**
     * Détecter toutes les versions FontAwesome présentes dans les fichiers
     */
    private function detectVersionsInFiles(array $files): array
    {
        $allVersionCounts = [];

        foreach ($files as $fileInfo) {
            $filePath = $fileInfo['path'];

            if (! file_exists($filePath)) {
                continue;
            }

            $content = file_get_contents($filePath);

            if ($content === false) {
                continue;
            }

            $versionCounts = $this->patternService->detectAllVersionsInContent($content);

            foreach ($versionCounts as $version => $count) {
                $allVersionCounts[$version] = ($allVersionCounts[$version] ?? 0) + $count;
            }
        }

        return $allVersionCounts;
    }
}
