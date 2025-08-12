<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Commands;

use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use FontAwesome\Migrator\Services\Commands\CommandDisplayService;
use FontAwesome\Migrator\Services\Commands\CommandFlowService;
use Illuminate\Console\Command;
use RuntimeException;

use function Laravel\Prompts\intro;

class MigrateCommand extends Command
{
    protected MetadataManagerInterface $metadata;

    protected CommandFlowService $flowService;

    protected CommandDisplayService $displayService;

    protected array $migrationOptions = [];

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fontawesome:migrate
                            {--from= : Version source de FontAwesome (4, 5, 6)}
                            {--to= : Version cible de FontAwesome (5, 6, 7)}
                            {--dry-run : Prévisualiser les changements sans les appliquer}
                            {--backup : Forcer la création de sauvegardes}
                            {--no-backup : Désactiver les sauvegardes}
                            {--icons-only : Migrer uniquement les classes d\'icônes}
                            {--assets-only : Migrer uniquement les assets (CSS, JS, CDN)}
                            {--no-interactive : Désactiver le mode interactif}
                            {--debug : Afficher les informations de debug de l\'environnement}
                            {--web-interface : Marquer que la migration provient de l\'interface web}
                            ';

    /**
     * The console command description.
     */
    protected $description = 'Migrer FontAwesome vers une version plus récente dans votre application Laravel';

    /**
     * Execute the console command.
     */
    public function handle(
        MetadataManagerInterface $metadata,
        CommandFlowService $flowService,
        CommandDisplayService $displayService
    ): int {
        // Assigner les services aux propriétés de classe
        $this->metadata = $metadata;
        $this->flowService = $flowService;
        $this->displayService = $displayService;

        intro('🚀 FontAwesome Migrator v2.0');

        // Initialiser les métadonnées
        $this->metadata->initialize();

        // Capturer les options de commande
        $this->captureCommandOptions();

        // Afficher debug si demandé
        if ($this->migrationOptions['debug']) {
            $this->displayService->displayDebugInfo($this->migrationOptions);
        }

        // Router vers le bon workflow selon le mode
        try {
            if ($this->migrationOptions['no_interactive']) {
                return $this->flowService->runNonInteractiveMode($this->migrationOptions, $this);
            }

            return $this->flowService->runInteractiveMode($this->migrationOptions, $this);
        } catch (RuntimeException $runtimeException) {
            $this->error($runtimeException->getMessage());

            return Command::FAILURE;
        }
    }

    private function captureCommandOptions(): void
    {
        // Stocker les options dans la commande
        $this->migrationOptions = [
            'source_version' => $this->option('from'),
            'target_version' => $this->option('to'),
            'dry_run' => $this->option('dry-run'),
            'backup' => $this->option('backup'),
            'no_backup' => $this->option('no-backup'),
            'icons_only' => $this->option('icons-only'),
            'assets_only' => $this->option('assets-only'),
            'no_interactive' => $this->option('no-interactive'),
            'debug' => $this->option('debug'),
            'web_interface' => $this->option('web-interface'),
        ];
    }
}
