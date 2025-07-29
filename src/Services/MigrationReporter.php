<?php

namespace FontAwesome\Migrator\Services;

use FontAwesome\Migrator\Support\GitignoreHelper;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigrationReporter
{
    protected array $config;

    protected bool $isDryRun = false;

    protected array $migrationOptions = [];

    public function __construct()
    {
        $this->config = config('fontawesome-migrator');
    }

    /**
     * Définir le mode dry-run
     */
    public function setDryRun(bool $isDryRun): self
    {
        $this->isDryRun = $isDryRun;

        return $this;
    }

    /**
     * Définir les options de migration utilisées
     */
    public function setMigrationOptions(array $options): self
    {
        $this->migrationOptions = $options;

        return $this;
    }

    /**
     * Générer un rapport de migration
     */
    public function generateReport(array $results): array
    {
        $reportPath = $this->config['report_path'];

        // S'assurer que le répertoire et le .gitignore existent
        GitignoreHelper::ensureDirectoryWithGitignore($reportPath);

        $timestamp = date('Y-m-d_H-i-s');
        $filename = \sprintf('fontawesome-migration-report-%s.html', $timestamp);
        $fullPath = $reportPath.'/'.$filename;

        $htmlContent = $this->generateHtmlReport($results);

        File::put($fullPath, $htmlContent);

        // Générer aussi un rapport JSON pour l'automatisation
        $jsonFilename = \sprintf('fontawesome-migration-report-%s.json', $timestamp);
        $jsonPath = $reportPath.'/'.$jsonFilename;
        File::put($jsonPath, json_encode($this->generateJsonReport($results), JSON_PRETTY_PRINT));

        // Générer les URLs d'accès web
        $relativePath = str_replace(storage_path('app/public'), '', $fullPath);
        $jsonRelativePath = str_replace(storage_path('app/public'), '', $jsonPath);

        return [
            'html_path' => $fullPath,
            'json_path' => $jsonPath,
            'html_url' => Storage::url($relativePath),
            'json_url' => Storage::url($jsonRelativePath),
            'filename' => $filename,
            'timestamp' => $timestamp,
        ];
    }

    /**
     * Générer le contenu HTML du rapport
     */
    protected function generateHtmlReport(array $results): string
    {
        $stats = $this->calculateStats($results);
        $timestamp = date('Y-m-d H:i:s');

        // Préparer les données pour la vue
        $viewData = [
            'results' => $results,
            'stats' => $stats,
            'timestamp' => $timestamp,
            'isDryRun' => $this->isDryRun ?? false,
            'migrationOptions' => $this->migrationOptions,
            'configuration' => [
                'license_type' => $this->config['license_type'],
                'scan_paths' => $this->config['scan_paths'] ?? [],
                'file_extensions' => $this->config['file_extensions'] ?? [],
                'backup_enabled' => $this->config['backup']['enabled'] ?? true,
            ],
            'packageVersion' => $this->getPackageVersion(),
        ];

        return view('fontawesome-migrator::reports.migration', $viewData)->render();
    }

    /**
     * Générer le rapport JSON
     */
    protected function generateJsonReport(array $results): array
    {
        $stats = $this->calculateStats($results);

        return [
            'meta' => [
                'generated_at' => date('c'),
                'license_type' => $this->config['license_type'],
                'package_version' => $this->getPackageVersion(),
                'dry_run' => $this->isDryRun,
                'migration_options' => $this->migrationOptions,
                'configuration' => [
                    'scan_paths' => $this->config['scan_paths'] ?? [],
                    'file_extensions' => $this->config['file_extensions'] ?? [],
                    'backup_enabled' => $this->config['backup']['enabled'] ?? true,
                ],
            ],
            'summary' => $stats,
            'files' => array_map(fn ($result): array => [
                'file' => $result['file'],
                'success' => $result['success'] ?? true,
                'changes_count' => \count($result['changes'] ?? []),
                'warnings_count' => \count($result['warnings'] ?? []),
                'assets_count' => \count($result['assets'] ?? []),
                'changes' => $result['changes'] ?? [],
                'warnings' => $result['warnings'] ?? [],
                'assets' => $result['assets'] ?? [],
            ], $results),
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

    /**
     * Générer un rapport de comparaison avant/après
     */
    public function generateComparisonReport(array $beforeStats, array $afterStats): string
    {
        $reportPath = $this->config['report_path'];
        $timestamp = date('Y-m-d_H-i-s');
        $filename = \sprintf('fontawesome-comparison-%s.json', $timestamp);
        $fullPath = $reportPath.'/'.$filename;

        $comparison = [
            'meta' => [
                'generated_at' => date('c'),
                'type' => 'comparison_report',
            ],
            'before' => $beforeStats,
            'after' => $afterStats,
            'improvements' => [
                'fa6_compliance' => $afterStats['fa6_icons'] ?? 0,
                'deprecated_removed' => ($beforeStats['deprecated_icons'] ?? 0) - ($afterStats['deprecated_icons'] ?? 0),
                'styles_updated' => $afterStats['modern_styles'] ?? 0,
            ],
        ];

        File::put($fullPath, json_encode($comparison, JSON_PRETTY_PRINT));

        return $fullPath;
    }

    /**
     * Nettoyer les anciens rapports
     */
    public function cleanOldReports(int $daysToKeep = 30): int
    {
        $reportPath = $this->config['report_path'];

        if (! File::exists($reportPath)) {
            return 0;
        }

        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
        $deleted = 0;

        $files = File::files($reportPath);

        foreach ($files as $file) {
            if ($file->getMTime() >= $cutoffTime) {
                continue;
            }

            if (! File::delete($file->getRealPath())) {
                continue;
            }

            $deleted++;
        }

        return $deleted;
    }

    /**
     * Obtenir la version du package depuis le CHANGELOG.md
     */
    protected function getPackageVersion(): string
    {
        $changelogPath = __DIR__.'/../../CHANGELOG.md';

        if (file_exists($changelogPath)) {
            $content = file_get_contents($changelogPath);

            // Chercher le premier titre de niveau 2 : format ## ou souligné avec ---
            if (preg_match('/^(\d+\.\d+\.\d+).*\n-+/m', $content, $matches) ||
                preg_match('/^## (\d+\.\d+\.\d+)/m', $content, $matches)) {
                return $matches[1];
            }
        }

        // Fallback version
        return '?';
    }
}
