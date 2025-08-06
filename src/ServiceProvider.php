<?php

declare(strict_types=1);

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
use FontAwesome\Migrator\Services\AssetReplacementService;
use FontAwesome\Migrator\Services\BackupManager;
use FontAwesome\Migrator\Services\ConfigurationLoader;
use FontAwesome\Migrator\Services\FileScanner;
use FontAwesome\Migrator\Services\FileScanningService;
use FontAwesome\Migrator\Services\FontAwesomePatternService;
use FontAwesome\Migrator\Services\IconReplacer;
use FontAwesome\Migrator\Services\MetadataBuilder;
use FontAwesome\Migrator\Services\MetadataManager;
use FontAwesome\Migrator\Services\MigrationReporter;
use FontAwesome\Migrator\Services\MigrationResultsService;
use FontAwesome\Migrator\Services\MigrationSessionService;
use FontAwesome\Migrator\Services\MigrationStorageService;
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
        // Configuration en singleton
        $this->app->singleton(ConfigurationInterface::class, ConfigHelper::class);

        // MetadataManager en singleton pour partager la session
        $this->app->singleton(MetadataManagerInterface::class, MetadataManager::class);

        // MigrationVersionManager en singleton
        $this->app->singleton(MigrationVersionManager::class);

        // BackupManager en singleton
        $this->app->singleton(BackupManagerInterface::class, BackupManager::class);

        // ConfigurationLoader en singleton (chemin de configuration par défaut)
        $this->app->singleton(ConfigurationLoader::class, function (): ConfigurationLoader {
            return new ConfigurationLoader(); // Utilise le chemin par défaut
        });

        // FontAwesomePatternService en singleton pour réutiliser les patterns
        $this->app->singleton(FontAwesomePatternService::class);

        // AssetReplacementService en singleton pour réutiliser les configurations
        $this->app->singleton(AssetReplacementService::class);

        // Services spécialisés pour MetadataManager - séparation des responsabilités
        $this->app->singleton(MigrationSessionService::class);
        $this->app->singleton(MigrationResultsService::class);
        $this->app->singleton(MigrationStorageService::class);

        // Services de refactoring Phase 2 - nouvelles extractions
        $this->app->singleton(FileScanningService::class);
        $this->app->singleton(MetadataBuilder::class);

        // Services core manquants - CRITIQUES pour fonctionnement
        $this->app->singleton(AssetMigrator::class);
        $this->app->singleton(IconReplacer::class);
        $this->app->singleton(MigrationReporter::class);

        // FileScanner avec interface (singleton pour performance scanning)
        $this->app->singleton(FileScannerInterface::class, FileScanner::class);

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
