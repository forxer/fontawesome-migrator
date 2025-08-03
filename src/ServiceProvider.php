<?php

namespace FontAwesome\Migrator;

use Carbon\Carbon;
use FontAwesome\Migrator\Commands\ConfigureCommand;
use FontAwesome\Migrator\Commands\InstallCommand;
use FontAwesome\Migrator\Commands\MigrateCommand;
use FontAwesome\Migrator\View\Components\PageHeader;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    private string $basePath = '';

    public function register(): void
    {
        $this->basePath = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;

        $this->mergeConfigFrom(
            $this->basePath.'/config/fontawesome-migrator.php',
            'fontawesome-migrator'
        );

        // Enregistrer les liaisons de services
        $this->registerBindings();
    }

    public function boot(): void
    {
        // Configuration de Carbon pour la localisation française
        Carbon::setLocale('fr');

        // Enregistrer les vues
        $this->loadViewsFrom($this->basePath.'resources/views', 'fontawesome-migrator');

        // Enregistrer les components Blade
        Blade::component('fontawesome-migrator::page-header', PageHeader::class);

        // Enregistrer les routes web
        $this->registerRoutes();

        require $this->basePath.'breadcrumbs/fontawesome-migrator.php';

        // Enregistrer les commandes (toujours disponibles pour Artisan::call)
        $this->commands([
            ConfigureCommand::class,
            InstallCommand::class,
            MigrateCommand::class,
        ]);

        if ($this->app->runningInConsole()) {
            $this->configurePublishing();
        }
    }

    /**
     * Enregistrer les routes du package
     */
    protected function registerRoutes(): void
    {
        Route::middleware(['web'])
            ->prefix('fontawesome-migrator')
            ->name('fontawesome-migrator.')
            ->group($this->basePath.'/routes/web.php');
    }

    /**
     * Configure the publishable resources offered by the package.
     */
    private function configurePublishing(): void
    {
        // Publier la configuration (stub pour une configuration minimale)
        $this->publishes([
            $this->basePath.'/config/fontawesome-migrator.stub' => config_path('fontawesome-migrator.php'),
        ], 'fontawesome-migrator-config');

        // Publier la configuration complète (pour référence)
        $this->publishes([
            $this->basePath.'/config/fontawesome-migrator.php' => config_path('fontawesome-migrator-full.php'),
        ], 'fontawesome-migrator-config-full');

    }

    /**
     * Enregistrer les liaisons de services
     */
    protected function registerBindings(): void
    {
        // === Services singleton (état partagé) ===

        // MetadataManager en singleton pour partager la session
        $this->app->singleton(\FontAwesome\Migrator\Services\MetadataManager::class);

        // MigrationVersionManager en singleton
        $this->app->singleton(\FontAwesome\Migrator\Services\MigrationVersionManager::class);

        // BackupManager en singleton qui utilise le MetadataManager singleton
        $this->app->singleton(
            \FontAwesome\Migrator\Services\BackupManager::class,
            function ($app) {
                return new \FontAwesome\Migrator\Services\BackupManager(
                    $app->make(\FontAwesome\Migrator\Services\MetadataManager::class)
                );
            }
        );

        // === Services avec injection automatique ===

        // ConfigurationLoader - pas de dépendances
        $this->app->bind(\FontAwesome\Migrator\Services\ConfigurationLoader::class);

        // FileScanner - pas de dépendances externes
        $this->app->bind(\FontAwesome\Migrator\Services\FileScanner::class);

        // PackageVersionService - service statique, pas besoin de binding

        // === Services avec dépendances spécifiques ===

        // MigrationReporter avec MetadataManager
        $this->app->bind(
            \FontAwesome\Migrator\Services\MigrationReporter::class,
            function ($app) {
                return new \FontAwesome\Migrator\Services\MigrationReporter(
                    $app->make(\FontAwesome\Migrator\Services\MetadataManager::class)
                );
            }
        );

        // AssetMigrator - injection automatique
        $this->app->bind(\FontAwesome\Migrator\Services\AssetMigrator::class);

        // IconReplacer avec dépendances complexes
        $this->app->bind(
            \FontAwesome\Migrator\Services\IconReplacer::class,
            function ($app) {
                // Obtenir le mapper par défaut (FA5→6) ou utiliser le contexte
                $versionManager = $app->make(\FontAwesome\Migrator\Services\MigrationVersionManager::class);
                $mapper = $versionManager->createMapper('5', '6'); // Par défaut

                return new \FontAwesome\Migrator\Services\IconReplacer(
                    $mapper,
                    $app->make(\FontAwesome\Migrator\Services\FileScanner::class),
                    $app->make(\FontAwesome\Migrator\Services\BackupManager::class)
                );
            }
        );

        // === Interface bindings ===

        // Liaison pour VersionMapperInterface - utiliser FA5→6 par défaut
        $this->app->bind(
            \FontAwesome\Migrator\Contracts\VersionMapperInterface::class,
            function ($app) {
                $versionManager = $app->make(\FontAwesome\Migrator\Services\MigrationVersionManager::class);

                return $versionManager->createMapper('5', '6');
            }
        );
    }
}
