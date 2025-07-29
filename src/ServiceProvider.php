<?php

namespace FontAwesome\Migrator;

use FontAwesome\Migrator\Commands\BackupCommand;
use FontAwesome\Migrator\Commands\ConfigureCommand;
use FontAwesome\Migrator\Commands\InstallCommand;
use FontAwesome\Migrator\Commands\MigrateCommand;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/fontawesome-migrator.php',
            'fontawesome-migrator'
        );
    }

    public function boot(): void
    {
        // Publier la configuration (stub pour une configuration minimale)
        $this->publishes([
            __DIR__.'/../config/fontawesome-migrator.stub' => config_path('fontawesome-migrator.php'),
        ], 'fontawesome-migrator-config');

        // Publier la configuration complète (pour référence)
        $this->publishes([
            __DIR__.'/../config/fontawesome-migrator.php' => config_path('fontawesome-migrator-full.php'),
        ], 'fontawesome-migrator-config-full');

        // Publier les fichiers de mapping
        $this->publishes([
            __DIR__.'/Mappers' => resource_path('fontawesome-migrator/mappers'),
        ], 'fontawesome-migrator-mappers');

        // Enregistrer les vues
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'fontawesome-migrator');

        // Enregistrer les routes web
        $this->registerRoutes();

        // Enregistrer les commandes (toujours disponibles pour Artisan::call)
        $this->commands([
            BackupCommand::class,
            ConfigureCommand::class,
            InstallCommand::class,
            MigrateCommand::class,
        ]);
    }

    /**
     * Enregistrer les routes du package
     */
    protected function registerRoutes(): void
    {
        Route::middleware(['web'])
            ->prefix('fontawesome-migrator')
            ->name('fontawesome-migrator.')
            ->group(__DIR__.'/../routes/web.php');
    }
}
