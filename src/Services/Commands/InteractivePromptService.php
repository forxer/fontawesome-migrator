<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Commands;

use FontAwesome\Migrator\Services\Core\VersionConfigurationService;
use RuntimeException;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;

class InteractivePromptService
{
    public function __construct(
        private readonly VersionConfigurationService $versionConfigService
    ) {}

    /**
     * Collecter les versions via prompts interactifs
     */
    public function collectVersionsInteractively(array &$migrationOptions): void
    {
        // Version source
        if (! $migrationOptions['source_version']) {
            $migrationOptions['source_version'] = $this->promptForSourceVersion();
        } else {
            info("📌 Version source : FontAwesome {$migrationOptions['source_version']} (depuis CLI)");
        }

        // Version cible
        if (! $migrationOptions['target_version']) {
            $migrationOptions['target_version'] = $this->promptForTargetVersion(
                $migrationOptions['source_version']
            );
        } else {
            info("🎯 Version cible : FontAwesome {$migrationOptions['target_version']} (depuis CLI)");
        }
    }

    /**
     * Demander la version source
     */
    public function promptForSourceVersion(): string
    {
        $detected = $this->versionConfigService->detectCurrentVersion();

        if ($detected) {
            $result = select(
                label: "Version détectée : FontAwesome {$detected}. Est-ce correct ?",
                options: [
                    $detected => "✅ Oui, utiliser FontAwesome {$detected}",
                    '4' => 'FontAwesome 4',
                    '5' => 'FontAwesome 5',
                    '6' => 'FontAwesome 6',
                ],
                default: $detected,
                hint: 'La détection automatique a trouvé des indices de FontAwesome '.$detected
            );

            return (string) $result;
        }

        $result = select(
            label: 'Quelle version de FontAwesome utilisez-vous actuellement ?',
            options: [
                '4' => 'FontAwesome 4 (fa fa-*)',
                '5' => 'FontAwesome 5 (fas/far/fab fa-*)',
                '6' => 'FontAwesome 6 (fa-solid/fa-regular fa-*)',
            ],
            hint: 'Vérifiez vos fichiers CSS/HTML pour identifier la syntaxe utilisée'
        );

        return (string) $result;
    }

    /**
     * Demander la version cible
     */
    public function promptForTargetVersion(string $sourceVersion): string
    {
        $available = $this->versionConfigService->getAvailableTargetVersions($sourceVersion);

        if (empty($available)) {
            throw new RuntimeException("Aucune migration disponible depuis FontAwesome {$sourceVersion}");
        }

        $options = [];

        foreach ($available as $version) {
            $label = "FontAwesome {$version}";

            if ($version === '7') {
                $label .= ' (dernière version)';
            }
            $options[$version] = $label;
        }

        $result = select(
            label: 'Vers quelle version souhaitez-vous migrer ?',
            options: $options,
            default: array_key_last($options),
            hint: 'Version 7 recommandée pour les nouvelles fonctionnalités'
        );

        return (string) $result;
    }

    /**
     * Collecter le mode de migration (dry-run ou réel)
     */
    public function collectMigrationModeInteractively(array &$migrationOptions): void
    {
        // Si déjà défini par CLI, on affiche juste l'info
        if ($migrationOptions['dry_run']) {
            info('🔍 Mode dry-run activé (depuis CLI)');

            return;
        }

        // Demander le mode
        $migrationOptions['dry_run'] = confirm(
            label: 'Voulez-vous faire un dry-run (simulation sans modification) ?',
            default: true,
            yes: '🔍 Oui, simuler d\'abord',
            no: '✏️ Non, appliquer directement',
            hint: 'Le dry-run permet de voir les changements sans modifier les fichiers'
        );
    }

    /**
     * Collecter la portée de la migration (icônes/assets)
     */
    public function collectMigrationScopeInteractively(array &$migrationOptions): void
    {
        // Si déjà défini par CLI, on affiche juste l'info
        if ($migrationOptions['icons_only']) {
            info('🔤 Migration des icônes uniquement (depuis CLI)');

            return;
        }

        if ($migrationOptions['assets_only']) {
            info('📦 Migration des assets uniquement (depuis CLI)');

            return;
        }

        // Demander la portée
        $scope = (string) select(
            label: 'Que souhaitez-vous migrer ?',
            options: [
                'both' => '🔄 Icônes et assets',
                'icons' => '🔤 Icônes uniquement',
                'assets' => '📦 Assets uniquement',
            ],
            default: 'both',
            hint: 'Les icônes sont les classes CSS, les assets sont les fichiers CDN/JS'
        );

        $migrationOptions['icons_only'] = ($scope === 'icons');
        $migrationOptions['assets_only'] = ($scope === 'assets');
    }

    /**
     * Collecter les préférences de backup
     */
    public function collectBackupPreferenceInteractively(array &$migrationOptions): void
    {
        // Si déjà défini par CLI, on configure et affiche l'info
        if ($migrationOptions['backup'] || $migrationOptions['no_backup']) {
            if ($migrationOptions['backup']) {
                $migrationOptions['create_backups'] = true;
                info('💾 Sauvegardes activées (depuis CLI)');
            } else {
                $migrationOptions['create_backups'] = false;
                info('💾 Sauvegardes désactivées (depuis CLI)');
            }

            return;
        }

        // En dry-run, pas besoin de demander
        if ($migrationOptions['dry_run']) {
            $migrationOptions['create_backups'] = false;
            info('ℹ️  Mode dry-run : sauvegardes non nécessaires');

            return;
        }

        // Sinon on demande
        $migrationOptions['create_backups'] = confirm(
            label: 'Voulez-vous créer des sauvegardes avant modification ?',
            default: config('fontawesome-migrator.backup_files', true),
            yes: '💾 Oui, sauvegarder',
            no: '⚡ Non, modifier directement',
            hint: 'Recommandé pour pouvoir revenir en arrière si nécessaire'
        );
    }

    /**
     * Demander confirmation finale
     */
    public function askForFinalConfirmation(): bool
    {
        info('');

        return confirm(
            label: '🚀 Confirmer et démarrer la migration ?',
            default: true,
            yes: 'Oui, lancer la migration',
            no: 'Annuler',
            hint: 'Cette action va modifier vos fichiers'
        );
    }
}
