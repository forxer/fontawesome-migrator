<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Commands;

use FontAwesome\Migrator\Contracts\FileScannerInterface;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use FontAwesome\Migrator\Services\Core\MigrationProcessor;
use Illuminate\Console\Command;
use RuntimeException;

use function Laravel\Prompts\info;

class CommandFlowService
{
    public function __construct(
        private readonly FileScannerInterface $scanner,
        private readonly MigrationProcessor $processor,
        private readonly MetadataManagerInterface $metadata,
        private readonly InteractivePromptService $promptService,
        private readonly CommandDisplayService $displayService,
        private readonly CommandValidationService $validationService
    ) {}

    /**
     * Mode interactif : guide l'utilisateur avec des prompts
     */
    public function runInteractiveMode(array &$migrationOptions, Command $command): int
    {
        info('💬 Mode interactif activé');
        info('');

        // 1. Collecter les informations manquantes via prompts
        $this->promptService->collectVersionsInteractively($migrationOptions);
        $this->promptService->collectMigrationModeInteractively($migrationOptions);
        $this->promptService->collectMigrationScopeInteractively($migrationOptions);

        // Les backups ne sont demandés que si ce n'est pas un dry-run
        if (! $migrationOptions['dry_run']) {
            $this->promptService->collectBackupPreferenceInteractively($migrationOptions);
        }

        // 2. Valider la configuration finale
        $this->validationService->validateConfiguration($migrationOptions);

        // 3. Scanner les fichiers
        info('');
        info('🔍 Analyse des fichiers...');
        $files = $this->scanFiles();

        if (empty($files)) {
            $command->warn('Aucun fichier trouvé à migrer.');

            return Command::SUCCESS;
        }

        // 4. Afficher un résumé et demander confirmation
        $this->displayService->displayMigrationSummary($files, $migrationOptions);

        if (! $migrationOptions['dry_run']) {
            if (! $this->promptService->askForFinalConfirmation()) {
                info('');
                info('❌ Migration annulée par l\'utilisateur');

                return Command::SUCCESS;
            }
        }

        // 5. Sauvegarder configuration et exécuter
        $this->metadata->setMigrationOptions($migrationOptions);

        info('');
        info('🚀 Démarrage de la migration...');
        $results = $this->processor->process($files, $migrationOptions);

        // 6. Afficher les résultats
        $this->displayService->displayResults($results, $migrationOptions['dry_run']);

        // 7. Proposer les prochaines actions
        if (! $migrationOptions['dry_run']) {
            $this->displayService->suggestNextActions($results, $migrationOptions);
        }

        return Command::SUCCESS;
    }

    /**
     * Mode non-interactif : validation stricte et exécution directe
     */
    public function runNonInteractiveMode(array &$migrationOptions, Command $command): int
    {
        info('🤖 Mode non-interactif');

        // 1. Valider que toutes les options requises sont présentes
        $this->validationService->validateNonInteractiveOptions($migrationOptions, $command);

        // 2. Configurer les versions depuis les options CLI
        $this->validationService->configureVersionsFromCliOptions($migrationOptions);

        // 3. Configurer les backups depuis les options CLI
        $this->validationService->configureBackupsFromCliOptions($migrationOptions);

        // 4. Scanner les fichiers
        $files = $this->scanFiles();

        if (empty($files)) {
            // En mode non-interactif, on ne pose pas de questions
            return Command::SUCCESS;
        }

        // 5. Sauvegarder configuration et exécuter directement
        $this->metadata->setMigrationOptions($migrationOptions);

        $results = $this->processor->process($files, $migrationOptions);

        // 6. Afficher les résultats
        $this->displayService->displayResults($results, $migrationOptions['dry_run']);

        return Command::SUCCESS;
    }

    /**
     * Scanner les fichiers configurés
     */
    private function scanFiles(): array
    {
        // Récupérer les chemins configurés
        $paths = config('fontawesome-migrator.scan_paths', []);

        if (empty($paths)) {
            throw new RuntimeException('Aucun chemin configuré pour le scan. Vérifiez votre configuration fontawesome-migrator.scan_paths');
        }

        // Scanner les fichiers
        $files = $this->scanner->scanPaths($paths);

        if ($files === []) {
            info('📁 Aucun fichier trouvé dans les chemins configurés');

            return [];
        }

        info('📁 '.\count($files).' fichier(s) trouvé(s) à analyser');

        return $files;
    }
}
