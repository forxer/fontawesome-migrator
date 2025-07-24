<?php

namespace FontAwesome\Migrator\Services;

use Illuminate\Support\Facades\File;

class MigrationReporter
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('fontawesome-migrator');
    }

    /**
     * Générer un rapport de migration
     */
    public function generateReport(array $results): string
    {
        $reportPath = $this->config['report_path'];

        if (!File::exists($reportPath)) {
            File::makeDirectory($reportPath, 0755, true);
        }

        $timestamp = date('Y-m-d_H-i-s');
        $filename = "fontawesome-migration-report-{$timestamp}.html";
        $fullPath = $reportPath . '/' . $filename;

        $htmlContent = $this->generateHtmlReport($results);

        File::put($fullPath, $htmlContent);

        // Générer aussi un rapport JSON pour l'automatisation
        $jsonFilename = "fontawesome-migration-report-{$timestamp}.json";
        $jsonPath = $reportPath . '/' . $jsonFilename;
        File::put($jsonPath, json_encode($this->generateJsonReport($results), JSON_PRETTY_PRINT));

        return $fullPath;
    }

    /**
     * Générer le contenu HTML du rapport
     */
    protected function generateHtmlReport(array $results): string
    {
        $stats = $this->calculateStats($results);
        $timestamp = date('Y-m-d H:i:s');

        $html = "<!DOCTYPE html>
<html lang='fr'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Rapport de Migration Font Awesome 5 → 6</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; }
        .header { background: white; padding: 30px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .stat-number { font-size: 2em; font-weight: bold; color: #2563eb; }
        .stat-label { color: #6b7280; margin-top: 5px; }
        .section { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .file-item { border-bottom: 1px solid #e5e7eb; padding: 15px 0; }
        .file-item:last-child { border-bottom: none; }
        .file-path { font-weight: 600; color: #1f2937; }
        .change-item { margin: 8px 0; padding: 8px; background: #f3f4f6; border-radius: 4px; font-family: monospace; }
        .change-from { color: #dc2626; }
        .change-to { color: #059669; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 10px; margin: 5px 0; }
        .success { color: #059669; }
        .error { color: #dc2626; }
        .summary { background: #dbeafe; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { text-align: left; padding: 12px; border-bottom: 1px solid #e5e7eb; }
        th { background: #f9fafb; font-weight: 600; }
        .change-type-style { background: #dbeafe; color: #1e40af; padding: 2px 6px; border-radius: 4px; font-size: 0.8em; }
        .change-type-deprecated { background: #fef3c7; color: #92400e; padding: 2px 6px; border-radius: 4px; font-size: 0.8em; }
        .change-type-manual { background: #fee2e2; color: #991b1b; padding: 2px 6px; border-radius: 4px; font-size: 0.8em; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🚀 Rapport de Migration Font Awesome 5 → 6</h1>
            <p>Généré le {$timestamp}</p>
            <p>Type de licence: <strong>" . ucfirst($this->config['license_type']) . "</strong></p>
        </div>

        <div class='summary'>
            <h2>📊 Résumé Exécutif</h2>
            <p><strong>{$stats['total_changes']}</strong> changements appliqués sur <strong>{$stats['modified_files']}</strong> fichiers (sur {$stats['total_files']} analysés)</p>
            " . ($stats['warnings'] > 0 ? "<p class='error'><strong>{$stats['warnings']}</strong> avertissements nécessitent votre attention</p>" : "") . "
        </div>

        <div class='stats-grid'>
            <div class='stat-card'>
                <div class='stat-number'>{$stats['total_files']}</div>
                <div class='stat-label'>Fichiers analysés</div>
            </div>
            <div class='stat-card'>
                <div class='stat-number'>{$stats['modified_files']}</div>
                <div class='stat-label'>Fichiers modifiés</div>
            </div>
            <div class='stat-card'>
                <div class='stat-number'>{$stats['total_changes']}</div>
                <div class='stat-label'>Total des changements</div>
            </div>
            <div class='stat-card'>
                <div class='stat-number'>{$stats['warnings']}</div>
                <div class='stat-label'>Avertissements</div>
            </div>
        </div>

        <div class='section'>
            <h2>📈 Répartition des changements</h2>
            <table>
                <tr><th>Type de changement</th><th>Nombre</th><th>Pourcentage</th></tr>";

        foreach ($stats['changes_by_type'] as $type => $count) {
            $percentage = $stats['total_changes'] > 0 ? round(($count / $stats['total_changes']) * 100, 1) : 0;
            $typeLabel = $this->getChangeTypeLabel($type);
            $typeClass = $this->getChangeTypeClass($type);

            $html .= "<tr>
                <td><span class='{$typeClass}'>{$typeLabel}</span></td>
                <td>{$count}</td>
                <td>{$percentage}%</td>
            </tr>";
        }

        $html .= "</table>
        </div>";

        // Section des fichiers modifiés
        $modifiedFiles = array_filter($results, fn($result) => !empty($result['changes']));

        if (!empty($modifiedFiles)) {
            $html .= "<div class='section'>
                <h2>📝 Fichiers modifiés (" . count($modifiedFiles) . ")</h2>";

            foreach ($modifiedFiles as $result) {
                $html .= "<div class='file-item'>
                    <div class='file-path'>📄 {$result['file']}</div>
                    <div style='margin-top: 10px;'>";

                foreach ($result['changes'] as $change) {
                    $typeClass = $this->getChangeTypeClass($change['type'] ?? 'style_update');
                    $typeLabel = $this->getChangeTypeLabel($change['type'] ?? 'style_update');

                    $html .= "<div class='change-item'>
                        <span class='{$typeClass}'>{$typeLabel}</span>
                        <br>
                        <span class='change-from'>{$change['from']}</span>
                        →
                        <span class='change-to'>{$change['to']}</span>
                        <small style='color: #6b7280;'> (ligne {$change['line']})</small>
                    </div>";
                }

                $html .= "</div>";

                // Afficher les avertissements pour ce fichier
                if (!empty($result['warnings'])) {
                    foreach ($result['warnings'] as $warning) {
                        $html .= "<div class='warning'>⚠️ {$warning}</div>";
                    }
                }

                $html .= "</div>";
            }

            $html .= "</div>";
        }

        // Section des avertissements
        $allWarnings = [];
        foreach ($results as $result) {
            if (!empty($result['warnings'])) {
                foreach ($result['warnings'] as $warning) {
                    $allWarnings[] = [
                        'file' => $result['file'],
                        'warning' => $warning
                    ];
                }
            }
        }

        if (!empty($allWarnings)) {
            $html .= "<div class='section'>
                <h2>⚠️ Avertissements (" . count($allWarnings) . ")</h2>
                <p>Ces éléments nécessitent une attention particulière :</p>";

            foreach ($allWarnings as $warning) {
                $html .= "<div class='warning'>
                    <strong>{$warning['file']}</strong>: {$warning['warning']}
                </div>";
            }

            $html .= "</div>";
        }

        // Section des recommandations
        $html .= "<div class='section'>
            <h2>💡 Recommandations</h2>
            <ul>
                <li><strong>Testez votre application</strong> après la migration pour vous assurer que toutes les icônes s'affichent correctement</li>
                <li><strong>Mettez à jour vos dépendances</strong> Font Awesome vers la version 6</li>";

        if ($stats['warnings'] > 0) {
            $html .= "<li><strong>Vérifiez les avertissements</strong> listés ci-dessus et corrigez manuellement si nécessaire</li>";
        }

        if ($this->config['license_type'] === 'pro') {
            $html .= "<li><strong>Explorez les nouveaux styles</strong> Font Awesome 6 Pro comme fa-thin et fa-sharp</li>";
        }

        $html .= "<li><strong>Consultez la documentation</strong> Font Awesome 6 pour découvrir les nouvelles fonctionnalités</li>
            </ul>
        </div>

        <div class='section'>
            <h2>🔗 Liens utiles</h2>
            <ul>
                <li><a href='https://fontawesome.com/docs/web/setup/upgrade/' target='_blank'>Guide de migration officiel Font Awesome</a></li>
                <li><a href='https://fontawesome.com/v6/docs/web/setup/upgrade/whats-changed' target='_blank'>Liste des changements FA6</a></li>
                <li><a href='https://fontawesome.com/search' target='_blank'>Recherche d'icônes FA6</a></li>
            </ul>
        </div>
    </div>
</body>
</html>";

        return $html;
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
                'package_version' => '1.0.0', // À récupérer dynamiquement
            ],
            'summary' => $stats,
            'files' => array_map(function ($result) {
                return [
                    'file' => $result['file'],
                    'success' => $result['success'] ?? true,
                    'changes_count' => count($result['changes'] ?? []),
                    'warnings_count' => count($result['warnings'] ?? []),
                    'changes' => $result['changes'] ?? [],
                    'warnings' => $result['warnings'] ?? [],
                ];
            }, $results)
        ];
    }

    /**
     * Calculer les statistiques du rapport
     */
    protected function calculateStats(array $results): array
    {
        $stats = [
            'total_files' => count($results),
            'modified_files' => 0,
            'total_changes' => 0,
            'changes_by_type' => [],
            'warnings' => 0,
            'errors' => 0
        ];

        foreach ($results as $result) {
            if (!empty($result['changes'])) {
                $stats['modified_files']++;
                $stats['total_changes'] += count($result['changes']);

                foreach ($result['changes'] as $change) {
                    $type = $change['type'] ?? 'style_update';
                    $stats['changes_by_type'][$type] = ($stats['changes_by_type'][$type] ?? 0) + 1;
                }
            }

            if (!empty($result['warnings'])) {
                $stats['warnings'] += count($result['warnings']);
            }

            if (isset($result['success']) && !$result['success']) {
                $stats['errors']++;
            }
        }

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
            'renamed_icon' => 'Icône renommée'
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
            'renamed_icon' => 'change-type-style'
        ];

        return $classes[$type] ?? 'change-type-style';
    }

    /**
     * Générer un rapport de comparaison avant/après
     */
    public function generateComparisonReport(array $beforeStats, array $afterStats): string
    {
        $reportPath = $this->config['report_path'];
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "fontawesome-comparison-{$timestamp}.json";
        $fullPath = $reportPath . '/' . $filename;

        $comparison = [
            'meta' => [
                'generated_at' => date('c'),
                'type' => 'comparison_report'
            ],
            'before' => $beforeStats,
            'after' => $afterStats,
            'improvements' => [
                'fa6_compliance' => $afterStats['fa6_icons'] ?? 0,
                'deprecated_removed' => ($beforeStats['deprecated_icons'] ?? 0) - ($afterStats['deprecated_icons'] ?? 0),
                'styles_updated' => $afterStats['modern_styles'] ?? 0
            ]
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

        if (!File::exists($reportPath)) {
            return 0;
        }

        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
        $deleted = 0;

        $files = File::files($reportPath);

        foreach ($files as $file) {
            if ($file->getMTime() < $cutoffTime) {
                if (File::delete($file->getRealPath())) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }
}