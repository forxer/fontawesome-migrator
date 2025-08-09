<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Metadata;

use FontAwesome\Migrator\Contracts\ConfigurationInterface;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;

class MigrationReporter
{
    public function __construct(
        protected MetadataManagerInterface $metadata,
        protected ConfigurationInterface $config
    ) {}

    /**
     * Générer les métadonnées de migration
     */
    public function generateMetadata(array $results): array
    {
        // Calculer les statistiques
        $stats = $this->calculateStats($results);

        // Enrichir les avertissements
        $enrichedWarnings = $this->extractWarnings($results);

        // Stocker les résultats dans metadata.json
        $this->metadata->storeMigrationResults($results, $stats, $enrichedWarnings);

        return [
            'success' => true,
            'migration_id' => $this->metadata->getAll()['short_id'] ?? 'unknown',
            'metadata_path' => $this->metadata->saveToFile(),
            'web_url' => url('/fontawesome-migrator/migrations'),
            'filename' => 'metadata.json', // Source unique des données
        ];
    }

    /**
     * Calculer les statistiques du rapport
     */
    protected function calculateStats(array $results): array
    {
        $stats = [
            'total_files' => \count($results),
            'modified_files' => 0,
            'total_changes' => 0,
            'changes_by_type' => [],
            'warnings' => 0,
            'errors' => 0,
            'assets_migrated' => 0,
            'icons_migrated' => 0,
            'asset_types' => [],
        ];

        foreach ($results as $result) {
            if (! empty($result['changes'])) {
                $stats['modified_files']++;
                $stats['total_changes'] += \count($result['changes']);

                foreach ($result['changes'] as $change) {
                    $type = $change['type'] ?? 'style_update';
                    $stats['changes_by_type'][$type] = ($stats['changes_by_type'][$type] ?? 0) + 1;

                    // Compter les types de changements
                    if ($type === 'asset') {
                        $stats['assets_migrated']++;
                    } else {
                        $stats['icons_migrated']++;
                    }
                }
            }

            // Compter les types d'assets analysés
            if (! empty($result['assets'])) {
                foreach ($result['assets'] as $asset) {
                    $assetType = $asset['type'] ?? 'unknown';
                    $stats['asset_types'][$assetType] = ($stats['asset_types'][$assetType] ?? 0) + 1;
                }
            }

            if (! empty($result['warnings'])) {
                $stats['warnings'] += \count($result['warnings']);
            }

            if (isset($result['success']) && ! $result['success']) {
                $stats['errors']++;
            }
        }

        // Calculer le succès de la migration
        $stats['migration_success'] = $stats['errors'] === 0;

        return $stats;
    }

    /**
     * Obtenir le libellé d'un type de changement
     */
    protected function getChangeTypeLabel(string $type): string
    {
        $labels = [
            'style_update' => 'Mise à jour de style',
            'deprecated_icon' => 'Icône dépréciée',
            'pro_fallback' => 'Fallback Pro→Free',
            'manual_review' => 'Révision manuelle',
            'renamed_icon' => 'Icône renommée',
            'asset' => 'Asset migré',
        ];

        return $labels[$type] ?? ucfirst(str_replace('_', ' ', $type));
    }

    /**
     * Obtenir la classe CSS d'un type de changement
     */
    protected function getChangeTypeClass(string $type): string
    {
        $classes = [
            'style_update' => 'change-type-style',
            'deprecated_icon' => 'change-type-deprecated',
            'pro_fallback' => 'change-type-deprecated',
            'manual_review' => 'change-type-manual',
            'renamed_icon' => 'change-type-style',
            'asset' => 'change-type-style',
        ];

        return $classes[$type] ?? 'change-type-style';
    }

    /**
     * Obtenir la description d'un type d'asset
     */
    protected function getAssetTypeDescription(string $type): string
    {
        $descriptions = [
            'cdn_url' => 'URLs CDN FontAwesome',
            'import' => 'Imports ES6/CommonJS',
            'require' => 'Modules CommonJS',
            'local_path' => 'Chemins locaux',
            'node_modules' => 'Références node_modules',
            'link_tag' => 'Balises <link> HTML',
            'script_tag' => 'Balises <script> HTML',
            'asset_helper' => 'Helpers Laravel asset()',
            'npm_package' => 'Packages NPM',
            'free_package' => 'Packages Free',
            'pro_package' => 'Packages Pro',
            'dynamic_import' => 'Imports dynamiques',
        ];

        return $descriptions[$type] ?? 'Type d\'asset détecté';
    }

    /**
     * Extraire et enrichir les avertissements depuis les résultats
     */
    public function extractWarnings(array $results): array
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
                                $from = $change['from'] ?? '';

                                // Extraire juste le nom de l'icône (fa-xxx) du changement complet
                                if (preg_match('/fa-[a-z0-9-]+/', $from, $matches)) {
                                    $iconName = $matches[0];

                                    if (str_contains((string) $warning, $iconName)) {
                                        $warningMessage = $warning;
                                        break;
                                    }
                                }

                                // Fallback : recherche avec le texte complet
                                if (str_contains((string) $warning, $from)) {
                                    $warningMessage = $warning;
                                    break;
                                }
                            }
                        }

                        if ($warningMessage) {
                            $enrichedWarnings[] = [
                                'file' => $filePath,
                                'line' => $change['line'] ?? null,
                                'message' => $warningMessage,
                                'change' => $change,
                            ];
                        }
                    }
                }
            }
        }

        return $enrichedWarnings;
    }
}
