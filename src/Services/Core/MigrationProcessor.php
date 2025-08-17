<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Core;

use FontAwesome\Migrator\Contracts\BackupManagerInterface;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;

use function Laravel\Prompts\info;

class MigrationProcessor
{
    public function __construct(
        private readonly IconReplacer $replacer,
        private readonly AssetMigrator $assetMigrator,
        private readonly MigrationVersionManager $versionManager,
        private readonly MetadataManagerInterface $metadata,
        private readonly BackupManagerInterface $backupManager
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
}
