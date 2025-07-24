<?php

namespace FontAwesome\Migrator;

use FontAwesome\Migrator\Commands\MigrateFontAwesomeCommand;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Override;

class ServiceProvider extends BaseServiceProvider
{
    #[Override]
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/fontawesome-migrator.php',
            'fontawesome-migrator'
        );
    }

    public function boot(): void
    {
        // Publier la configuration
        $this->publishes([
            __DIR__.'/config/fontawesome-migrator.php' => config_path('fontawesome-migrator.php'),
        ], 'fontawesome-migrator-config');

        // Publier les fichiers de mapping
        $this->publishes([
            __DIR__.'/Mappers' => resource_path('fontawesome-migrator/mappers'),
        ], 'fontawesome-migrator-mappers');

        // Enregistrer les commandes
        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrateFontAwesomeCommand::class,
            ]);
        }
    }
}
