<?php

namespace FontAwesome\Migrator\Commands;

use FontAwesome\Migrator\Services\FileScanner;
use FontAwesome\Migrator\Services\IconReplacer;
use FontAwesome\Migrator\Services\MigrationReporter;
use Illuminate\Console\Command;

class MigrateFontAwesomeCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fontawesome:migrate
                            {--dry-run : Prévisualiser les changements sans les appliquer}
                            {--path= : Chemin spécifique à analyser}
                            {--backup : Forcer la création de sauvegardes}
                            {--no-backup : Désactiver les sauvegardes}
                            {--verbose : Mode verbeux}
                            {--report : Générer un rapport détaillé}';

    /**
     * The console command description.
     */
    protected $description = 'Migrer Font Awesome 5 vers Font Awesome 6 dans votre application Laravel';

    public function __construct(
        protected FileScanner $scanner,
        protected IconReplacer $replacer,
        protected MigrationReporter $reporter,
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Démarrage de la migration Font Awesome 5 → 6');

        // Validation de la configuration
        if (! $this->validateConfiguration()) {
            return Command::FAILURE;
        }

        $isDryRun = $this->option('dry-run');
        $customPath = $this->option('path');

        if ($isDryRun) {
            $this->warn('Mode DRY-RUN activé - Aucune modification ne sera appliquée');
        }

        // Configuration du scanner
        $paths = $customPath ? [$customPath] : config('fontawesome-migrator.scan_paths');

        $this->info('📂 Analyse des fichiers...');
        $progressBar = $this->output->createProgressBar();

        // Scanner les fichiers
        $files = $this->scanner->scanPaths($paths, function ($current, $total) use ($progressBar): void {
            $progressBar->setMaxSteps($total);
            $progressBar->setProgress($current);
        });

        $progressBar->finish();
        $this->newLine();

        if ($files === []) {
            $this->warn('Aucun fichier trouvé à analyser.');

            return Command::SUCCESS;
        }

        $this->info(\sprintf('📋 %d fichiers trouvés', \count($files)));

        // Analyser et remplacer les icônes
        $this->info('🔍 Recherche des icônes Font Awesome 5...');
        $results = $this->replacer->processFiles($files, $isDryRun);

        // Afficher les résultats
        $this->displayResults($results, $isDryRun);

        // Générer le rapport si demandé
        if ($this->option('report') || config('fontawesome-migrator.generate_report')) {
            $this->reporter->generateReport($results);
            $this->info('📊 Rapport généré dans '.config('fontawesome-migrator.report_path'));
        }

        if ($isDryRun) {
            $this->info('✨ Prévisualisation terminée. Utilisez la commande sans --dry-run pour appliquer les changements.');
        } else {
            $this->info('✅ Migration terminée avec succès !');
        }

        return Command::SUCCESS;
    }

    protected function validateConfiguration(): bool
    {
        $licenseType = config('fontawesome-migrator.license_type');

        if (! \in_array($licenseType, ['free', 'pro'])) {
            $this->error('Type de licence invalide. Utilisez "free" ou "pro".');

            return false;
        }

        $scanPaths = config('fontawesome-migrator.scan_paths');

        if (empty($scanPaths)) {
            $this->error('Aucun chemin de scan configuré.');

            return false;
        }

        return true;
    }

    protected function displayResults(array $results, bool $isDryRun): void
    {
        $totalFiles = \count($results);
        $modifiedFiles = collect($results)->filter(fn ($result): bool => ! empty($result['changes']))->count();
        $totalChanges = collect($results)->sum(fn ($result): int => \count($result['changes']));

        $this->newLine();
        $this->info('📊 Résultats de la migration :');
        $this->line('   • Fichiers analysés : '.$totalFiles);
        $this->line('   • Fichiers modifiés : '.$modifiedFiles);
        $this->line('   • Total des changements : '.$totalChanges);

        if ($this->option('verbose') || $totalChanges < 20) {
            $this->newLine();
            $this->info('📝 Détail des changements :');

            foreach ($results as $result) {
                if (! empty($result['changes'])) {
                    $this->line(\sprintf('   📄 %s:', $result['file']));

                    foreach ($result['changes'] as $change) {
                        $status = $isDryRun ? '(DRY-RUN)' : '✓';
                        $this->line(\sprintf('      %s %s → %s', $status, $change['from'], $change['to']));
                    }
                }
            }
        }

        if (! empty($results[0]['warnings'] ?? [])) {
            $this->newLine();
            $this->warn('⚠️  Avertissements :');

            foreach ($results as $result) {
                foreach ($result['warnings'] ?? [] as $warning) {
                    $this->line('   • '.$warning);
                }
            }
        }
    }
}
