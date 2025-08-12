<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Commands;

use function Laravel\Prompts\info;
use function Laravel\Prompts\table;

class CommandDisplayService
{
    /**
     * Afficher les informations de debug
     */
    public function displayDebugInfo(array $migrationOptions): void
    {
        info('');
        info('🔍 Debug Information');
        info('');

        // Environnement
        table(
            ['📦 Environment', 'Value'],
            [
                ['Laravel Version', app()->version()],
                ['PHP Version', PHP_VERSION],
                ['FontAwesome Migrator', config('fontawesome-migrator.version', 'v2.0')],
                ['Working Directory', getcwd()],
            ]
        );

        // Options de commande
        $commandOptions = [];

        foreach ($migrationOptions as $key => $value) {
            $displayValue = \is_bool($value) ? ($value ? '✅ true' : '❌ false') : ($value ?? '⚪ null');
            $commandOptions[] = [str_replace('_', ' ', ucfirst($key)), $displayValue];
        }

        table(
            ['⚙️  Command Option', 'Value'],
            $commandOptions
        );

        // Configuration
        table(
            ['📋 Configuration', 'Value'],
            [
                ['Scan Paths', implode(', ', config('fontawesome-migrator.scan_paths', []))],
                ['Migrations Path', config('fontawesome-migrator.migrations_path')],
                ['Backup Enabled', config('fontawesome-migrator.backup_files') ? '✅ Yes' : '❌ No'],
                ['Auto-detect Version', config('fontawesome-migrator.auto_detect_version') ? '✅ Yes' : '❌ No'],
            ]
        );

        // Versions supportées
        table(
            ['🔄 Migration', 'Description'],
            [
                ['4 → 5', 'FontAwesome 4 to 5'],
                ['5 → 6', 'FontAwesome 5 to 6'],
                ['6 → 7', 'FontAwesome 6 to 7'],
            ]
        );

        info('');
    }

    /**
     * Afficher le résumé avant migration
     */
    public function displayMigrationSummary(array $files, array $migrationOptions): void
    {
        info('');
        info('📋 Résumé de la migration :');
        info("   Version source : FontAwesome {$migrationOptions['source_version']}");
        info("   Version cible  : FontAwesome {$migrationOptions['target_version']}");
        info('   Fichiers       : '.\count($files).' fichiers à analyser');

        if ($migrationOptions['dry_run']) {
            info('   Mode           : 🔍 Dry-run (prévisualisation uniquement)');
        } else {
            $backupStatus = $migrationOptions['create_backups'] ? '💾 Avec sauvegardes' : '⚡ Sans sauvegardes';
            info("   Mode           : {$backupStatus}");
        }

        if ($migrationOptions['icons_only']) {
            info('   Portée         : Icônes uniquement');
        } elseif ($migrationOptions['assets_only']) {
            info('   Portée         : Assets uniquement');
        } else {
            info('   Portée         : Icônes et assets');
        }
    }

    /**
     * Afficher les résultats de migration
     */
    public function displayResults(array $results, bool $dryRun): void
    {
        info('');
        info('📊 Résumé de la migration'.($dryRun ? ' (dry-run)' : ''));
        info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Statistiques globales
        info('📁 Fichiers analysés : '.$results['total_files_processed']);
        info('✏️  Fichiers modifiés : '.$results['total_files_modified']);

        // Détails icônes
        $iconChanges = $results['icons']['total_changes'] ?? 0;

        if ($iconChanges > 0) {
            info('🔄 Icônes migrées : '.$iconChanges);
        }

        // Détails assets
        $assetChanges = $results['assets']['total_assets'] ?? 0;

        if ($assetChanges > 0) {
            info('📦 Assets migrés : '.$assetChanges);
        }

        if ($dryRun) {
            info('');
            info('💡 Mode dry-run : aucun fichier n\'a été modifié');
            info('   Pour appliquer les changements, relancez sans --dry-run');
        }
    }

    /**
     * Suggérer les prochaines actions après migration
     */
    public function suggestNextActions(array $results, array $migrationOptions): void
    {
        $totalChanges = $results['icons']['total_changes'] ?? 0;
        $totalAssets = $results['assets']['total_assets'] ?? 0;

        if ($totalChanges === 0 && $totalAssets === 0) {
            return;
        }

        info('');
        info('💡 Prochaines étapes recommandées :');
        info('   1. Tester votre application en local');
        info('   2. Vérifier l\'affichage des icônes');
        info('   3. Contrôler vos CSS personnalisés');

        if ($migrationOptions['create_backups']) {
            info('   4. Les sauvegardes sont dans le dossier migrations/');
        }
    }
}
