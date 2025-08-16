<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Commands;

use FontAwesome\Migrator\Services\Core\VersionConfigurationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use RuntimeException;

use function Laravel\Prompts\info;

class CommandValidationService
{
    public function __construct(
        private readonly VersionConfigurationService $versionConfigService
    ) {}

    /**
     * Valider les options en mode non-interactif
     */
    public function validateNonInteractiveOptions(array $migrationOptions, Command $command): void
    {
        $errors = [];

        if (! $migrationOptions['source_version']) {
            $errors[] = 'Option --from requise en mode non-interactif';
        }

        if (! $migrationOptions['target_version']) {
            $errors[] = 'Option --to requise en mode non-interactif';
        }

        if ($errors !== []) {
            foreach ($errors as $error) {
                $command->error($error);
            }

            throw new RuntimeException('Configuration incomplète pour le mode non-interactif');
        }
    }

    /**
     * Valider la configuration finale
     */
    public function validateConfiguration(array &$migrationOptions): void
    {
        $versionConfig = $this->versionConfigService->configureVersions(
            $migrationOptions['source_version'],
            $migrationOptions['target_version']
        );

        $migrationOptions['source_version'] = $versionConfig['source_version'];
        $migrationOptions['target_version'] = $versionConfig['target_version'];

        info(\sprintf('✅ Configuration validée : FontAwesome %s → %s', $versionConfig['source_version'], $versionConfig['target_version']));
    }

    /**
     * Configurer versions depuis CLI en mode non-interactif
     */
    public function configureVersionsFromCliOptions(array &$migrationOptions): void
    {
        $versionConfig = $this->versionConfigService->configureVersions(
            $migrationOptions['source_version'],
            $migrationOptions['target_version']
        );

        $migrationOptions['source_version'] = $versionConfig['source_version'];
        $migrationOptions['target_version'] = $versionConfig['target_version'];

        info(\sprintf('✅ Migration : FontAwesome %s → %s', $versionConfig['source_version'], $versionConfig['target_version']));
    }

    /**
     * Configurer backups depuis CLI en mode non-interactif
     */
    public function configureBackupsFromCliOptions(array &$migrationOptions): void
    {
        if ($migrationOptions['dry_run']) {
            $migrationOptions['create_backups'] = false;
        } elseif ($migrationOptions['no_backup']) {
            $migrationOptions['create_backups'] = false;
        } elseif ($migrationOptions['backup']) {
            $migrationOptions['create_backups'] = true;
        } else {
            $migrationOptions['create_backups'] = Config::boolean('fontawesome-migrator.backup_files', true);
        }

        $status = $migrationOptions['create_backups'] ? 'activées' : 'désactivées';
        info('💾 Sauvegardes '.$status);
    }
}
