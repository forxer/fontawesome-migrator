<?php

namespace FontAwesome\Migrator;

use Carbon\Carbon;
use FontAwesome\Migrator\Commands\ConfigureCommand;
use FontAwesome\Migrator\Commands\InstallCommand;
use FontAwesome\Migrator\Commands\MigrateCommand;
use FontAwesome\Migrator\Contracts\BackupManagerInterface;
use FontAwesome\Migrator\Contracts\ConfigurationInterface;
use FontAwesome\Migrator\Contracts\FileScannerInterface;
use FontAwesome\Migrator\Contracts\MetadataManagerInterface;
use FontAwesome\Migrator\Contracts\VersionMapperInterface;
use FontAwesome\Migrator\Services\AssetMigrator;
use FontAwesome\Migrator\Services\BackupManager;
use FontAwesome\Migrator\Services\ConfigurationLoader;
use FontAwesome\Migrator\Services\FileScanner;
use FontAwesome\Migrator\Services\IconReplacer;
use FontAwesome\Migrator\Services\MetadataManager;
use FontAwesome\Migrator\Services\MigrationReporter;
use FontAwesome\Migrator\Services\MigrationVersionManager;
use FontAwesome\Migrator\Support\ConfigHelper;
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
        // === Interfaces Core ===

        // Configuration en singleton
        $this->app->singleton(ConfigurationInterface::class, ConfigHelper::class);

        // === Services singleton (état partagé) ===

        // MetadataManager en singleton pour partager la session
        $this->app->singleton(MetadataManagerInterface::class, MetadataManager::class);
        $this->app->singleton(MetadataManager::class, MetadataManager::class);

        // MigrationVersionManager en singleton
        $this->app->singleton(MigrationVersionManager::class);

        // BackupManager en singleton
        $this->app->singleton(BackupManagerInterface::class, BackupManager::class);
        $this->app->singleton(BackupManager::class, BackupManager::class);

        // === Services avec injection automatique ===

        // ConfigurationLoader - pas de dépendances
        $this->app->bind(ConfigurationLoader::class);

        // FileScanner avec interface
        $this->app->bind(FileScannerInterface::class, FileScanner::class);
        $this->app->bind(FileScanner::class, FileScanner::class);

        // AssetMigrator
        $this->app->bind(AssetMigrator::class, AssetMigrator::class);

        // PackageVersionService - service statique, pas besoin de binding

        // === Services avec dépendances spécifiques ===

        // MigrationReporter avec interfaces
        $this->app->bind(MigrationReporter::class, MigrationReporter::class);

        // IconReplacer avec injection automatique
        $this->app->bind(IconReplacer::class, IconReplacer::class);

        // === Interface bindings ===

        // Liaison pour VersionMapperInterface - utiliser FA5→6 par défaut
        $this->app->bind(
            VersionMapperInterface::class,
            function ($app) {
                $versionManager = $app->make(MigrationVersionManager::class);

                return $versionManager->createMapper('5', '6');
            }
        );
    }
}
