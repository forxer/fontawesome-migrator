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

        // Récupérer les résultats par fichier (toujours à la racine maintenant)
        $fileResults = $results['file_results'] ?? [];

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

            // Collecter seulement les warnings réels (pas tous les changes)
            if (! empty($result['warnings'])) {
                foreach ($result['warnings'] as $warningIndex => $warningMessage) {
                    // Chercher le change correspondant si il existe
                    $correspondingChange = null;

                    if (! empty($result['changes']) && isset($result['changes'][$warningIndex])) {
                        $correspondingChange = $result['changes'][$warningIndex];
                    }

                    $enrichedWarnings[] = [
                        'file' => $filePath,
                        'type' => $correspondingChange['type'] ?? 'warning',
                        'from' => $correspondingChange['from'] ?? '',
                        'to' => $correspondingChange['to'] ?? '',
                        'message' => $warningMessage,
                        'line' => $correspondingChange['line'] ?? null,
                        'context' => $correspondingChange['context'] ?? '',
                    ];
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
        $consolidatedFileResults = [];
        $cumulativeStats = [
            'total_files' => 0,
            'modified_files' => 0,
            'total_changes' => 0,
            'icons_migrated' => 0,
            'assets_migrated' => 0,
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

            // Exécuter cette étape de migration (sans finaliser pour éviter les sauvegardes multiples)
            $stepResult = $this->processSingleMigrationWithoutFinalization($files, $stepOptions);

            $allResults[] = [
                'step' => \sprintf('%s → %s', $step['from'], $step['to']),
                'result' => $stepResult,
            ];

            // Consolider tous les file_results de cette étape en préservant les warnings de toutes les étapes
            if (! empty($stepResult['file_results'])) {
                $consolidatedFileResults = $this->consolidateFileResults($consolidatedFileResults, $stepResult['file_results'], $step);
            }

            // Accumuler les statistiques
            $cumulativeStats['total_files'] = max($cumulativeStats['total_files'], $stepResult['total_files_processed'] ?? 0);
            $cumulativeStats['modified_files'] += $stepResult['total_files_modified'] ?? 0;
            $cumulativeStats['total_changes'] += ($stepResult['icons']['total_changes'] ?? 0) + ($stepResult['assets']['total_assets'] ?? 0);
            $cumulativeStats['icons_migrated'] += $stepResult['icons']['total_changes'] ?? 0;
            $cumulativeStats['assets_migrated'] += $stepResult['assets']['total_assets'] ?? 0;
        }

        info('');
        info('✅ Migration progressive terminée !');
        info('   Total des modifications : '.$cumulativeStats['total_changes']);

        // Créer le résultat final consolidé avec tous les file_results
        $finalResult = [
            'progressive' => true,
            'steps' => $allResults,
            'cumulative' => $cumulativeStats,
            'total_files_processed' => $cumulativeStats['total_files'],
            'total_files_modified' => $cumulativeStats['modified_files'],
            'file_results' => $consolidatedFileResults,
            'icons' => [
                'total_changes' => $cumulativeStats['icons_migrated'],
            ],
            'assets' => [
                'total_assets' => $cumulativeStats['assets_migrated'],
            ],
        ];

        // Finaliser la migration progressive une seule fois avec tous les résultats consolidés
        $this->finalizeMigration($finalResult, $options);

        return $finalResult;
    }

    /**
     * Exécuter une migration simple sans finalisation (pour les migrations progressives)
     */
    private function processSingleMigrationWithoutFinalization(array $files, array $options): array
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

        return [
            'icons' => $iconResults,
            'assets' => $assetResults,
            'file_results' => $allFileResults,
            'total_files_processed' => \count($files),
            'total_files_modified' => \count(array_unique(array_merge(
                $iconResults['modified_files'] ?? [],
                $assetResults['modified_files'] ?? []
            ))),
        ];
    }

    /**
     * Consolider les file_results en préservant tous les warnings et changements de toutes les étapes
     */
    private function consolidateFileResults(array $existingResults, array $newResults, array $step): array
    {
        // Si pas de résultats existants, retourner les nouveaux avec annotation d'étape
        if ($existingResults === []) {
            return $this->annotateResultsWithStep($newResults, $step);
        }

        // Créer un index par fichier pour faciliter la consolidation
        $consolidatedByFile = [];

        // D'abord, indexer les résultats existants par fichier
        foreach ($existingResults as $result) {
            $fileKey = $result['file'] ?? 'unknown';

            if (! isset($consolidatedByFile[$fileKey])) {
                $consolidatedByFile[$fileKey] = $result;
            } else {
                // Si le fichier existe déjà, cumule les données
                $consolidatedByFile[$fileKey] = $this->mergeFileResults($consolidatedByFile[$fileKey], $result);
            }
        }

        // Ensuite, intégrer les nouveaux résultats
        foreach ($newResults as $newResult) {
            $fileKey = $newResult['file'] ?? 'unknown';

            // Annoter avec l'étape courante
            $annotatedResult = $this->annotateResultWithStep($newResult, $step);

            if (! isset($consolidatedByFile[$fileKey])) {
                $consolidatedByFile[$fileKey] = $annotatedResult;
            } else {
                // Fusionner avec les résultats existants
                $consolidatedByFile[$fileKey] = $this->mergeFileResults($consolidatedByFile[$fileKey], $annotatedResult);
            }
        }

        return array_values($consolidatedByFile);
    }

    /**
     * Fusionner deux résultats de fichier en cumulant changes et warnings
     */
    private function mergeFileResults(array $existing, array $new): array
    {
        $merged = [
            'file' => $existing['file'],
            'success' => $existing['success'] && $new['success'],
            'changes' => array_merge($existing['changes'] ?? [], $new['changes'] ?? []),
            'changes_count' => ($existing['changes_count'] ?? 0) + ($new['changes_count'] ?? 0),
            'warnings' => array_merge($existing['warnings'] ?? [], $new['warnings'] ?? []),
            'progressive_steps' => array_unique(array_merge(
                $existing['progressive_steps'] ?? [],
                $new['progressive_steps'] ?? []
            )),
        ];

        // Ajouter backup seulement si présent (garder la dernière sauvegarde)
        if (isset($new['backup'])) {
            $merged['backup'] = $new['backup'];
        } elseif (isset($existing['backup'])) {
            $merged['backup'] = $existing['backup'];
        }

        return $merged;
    }

    /**
     * Annoter un résultat avec l'étape de migration
     */
    private function annotateResultWithStep(array $result, array $step): array
    {
        $result['progressive_steps'] = [$step['from'].'→'.$step['to']];

        return $result;
    }

    /**
     * Annoter tous les résultats avec l'étape de migration
     */
    private function annotateResultsWithStep(array $results, array $step): array
    {
        return array_map(fn ($result): array => $this->annotateResultWithStep($result, $step), $results);
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
