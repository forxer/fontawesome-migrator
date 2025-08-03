<?php

namespace FontAwesome\Migrator\Commands\Traits;

use Exception;
use Illuminate\Support\Facades\File;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\info;
use function Laravel\Prompts\note;
use function Laravel\Prompts\text;

trait ConfigurationHelpers
{
    /**
     * Obtenir les chemins de scan par défaut
     */
    protected function getDefaultScanPaths(): array
    {
        return [
            'resources/views',
            'resources/js',
            'resources/css',
            'resources/scss',
            'resources/sass',
            'public/css',
            'public/js',
        ];
    }

    /**
     * Obtenir les extensions par défaut
     */
    protected function getDefaultFileExtensions(): array
    {
        return [
            'blade.php',
            'css',
            'js',
            'json',
            'scss',
            'ts',
            'tsx',
            'vue',
        ];
    }

    /**
     * Obtenir les patterns d'exclusion par défaut
     */
    protected function getDefaultExcludePatterns(): array
    {
        return [
            'node_modules',
            'vendor',
            '.git',
            'storage',
            'bootstrap/cache',
            '*.min.js',
            '*.min.css',
        ];
    }

    /**
     * Configurer les chemins de scan personnalisés de manière interactive
     */
    protected function configureScanPaths(): array
    {
        $defaultPaths = $this->getDefaultScanPaths();

        note(
            "📂 Chemins de scan par défaut :\n".
            collect($defaultPaths)->map(fn ($path): string => '  • '.$path)->join("\n")
        );

        $customPaths = [];
        $addCustomPaths = confirm('Voulez-vous ajouter des chemins personnalisés ?', false);

        if ($addCustomPaths) {
            note(
                "💡 Exemples de chemins :\n".
                "  • app/Views (dossier Views custom)\n".
                "  • resources/components (composants)\n".
                "  • public/assets/css (assets publics)\n".
                "  • resources/views/emails (templates emails)\n".
                '  • package.json (fichier spécifique)'
            );

            do {
                $path = text(
                    'Chemin supplémentaire',
                    placeholder: 'ex: app/Views, resources/components, package.json'
                );

                if ($path !== '' && $path !== '0') {
                    $customPaths[] = $path;
                    info('✅ Ajouté: '.$path);
                }

                $continueAdding = $path && confirm('Ajouter un autre chemin ?', false);
            } while ($continueAdding);
        }

        return array_merge($defaultPaths, $customPaths);
    }

    /**
     * Configurer les patterns d'exclusion de manière interactive
     */
    protected function configureExcludePatterns(): array
    {
        $defaultExcludes = $this->getDefaultExcludePatterns();

        note(
            "🚫 Patterns d'exclusion par défaut :\n".
            collect($defaultExcludes)->map(fn ($pattern): string => '  • '.$pattern)->join("\n")
        );

        $customExcludes = [];
        $addCustomExcludes = confirm('Voulez-vous ajouter des patterns d\'exclusion personnalisés ?', false);

        if ($addCustomExcludes) {
            note(
                "💡 Exemples de patterns :\n".
                "  • *.backup (fichiers de sauvegarde)\n".
                "  • tests/ (dossier de tests)\n".
                "  • legacy-* (fichiers legacy)\n".
                '  • temp (dossiers temporaires)'
            );

            do {
                $pattern = text(
                    "Pattern d'exclusion",
                    placeholder: 'ex: *.backup, tests/, legacy-*'
                );

                if ($pattern !== '' && $pattern !== '0') {
                    $customExcludes[] = $pattern;
                    info('✅ Ajouté: '.$pattern);
                }

                $continueAdding = $pattern && confirm('Ajouter un autre pattern ?', false);
            } while ($continueAdding);
        }

        return array_merge($defaultExcludes, $customExcludes);
    }

    /**
     * Mettre à jour une valeur de configuration
     */
    protected function updateConfigValue(string $key, mixed $value): void
    {
        $configPath = config_path('fontawesome-migrator.php');
        $config = include $configPath;
        $config[$key] = $value;

        // Écrire directement la configuration complète avec formatage propre
        $content = "<?php\n\nreturn [\n    /*\n    | Configuration FontAwesome Migrator\n    | Valeurs personnalisées\n    */\n\n";
        $content .= $this->arrayToString($config, 1)."\n];\n";

        File::put($configPath, $content);

        // Recharger la configuration
        config(['fontawesome-migrator' => $config]);
    }

    /**
     * Écrire le fichier de configuration personnalisé
     */
    protected function writeCustomConfigFile(string $configPath, array $customConfig): void
    {
        // Template de base
        $template = "<?php\n\nreturn [\n    /*\n    |--------------------------------------------------------------------------\n    | Configuration FontAwesome Migrator\n    |--------------------------------------------------------------------------\n    |\n    | Ce fichier contient uniquement les paramètres personnalisés.\n    | Les valeurs par défaut sont définies dans le package.\n    | \n    | Pour voir toutes les options disponibles :\n    | php artisan vendor:publish --tag=fontawesome-migrator-config --force\n    |\n    */\n\n";

        // Si aucune configuration personnalisée, créer un fichier vide
        if ($customConfig === []) {
            $content = $template."    // Aucune configuration personnalisée\n    // Toutes les valeurs par défaut sont utilisées\n];\n";
        } else {
            $content = $template.$this->arrayToString($customConfig, 1)."\n];\n";
        }

        File::put($configPath, $content);
    }

    /**
     * Convertir un tableau en chaîne PHP formatée
     */
    protected function arrayToString(array $array, int $indent = 0): string
    {
        $spaces = str_repeat('    ', $indent);
        $result = '';

        foreach ($array as $key => $value) {
            $result .= $spaces;

            if (\is_string($key)) {
                $result .= \sprintf("'%s' => ", $key);
            }

            if (\is_array($value)) {
                $result .= "[\n".$this->arrayToString($value, $indent + 1)."\n".$spaces.']';
            } elseif (\is_bool($value)) {
                $result .= $value ? 'true' : 'false';
            } elseif (\is_string($value)) {
                if (str_contains($value, '(')) {
                    // Fonctions Laravel comme storage_path()
                    $result .= $value;
                } else {
                    $result .= \sprintf("'%s'", $value);
                }
            } else {
                $result .= $value;
            }

            $result .= ",\n";
        }

        return rtrim($result, ",\n");
    }

    /**
     * Afficher une section de configuration
     */
    protected function displayConfigSection(string $title, array $items): void
    {
        if ($this->option('no-interactive')) {
            $this->info($title);

            foreach ($items as $key => $value) {
                $this->line(\sprintf('  %s: %s', $key, $value));
            }

            $this->newLine();
        } else {
            $content = collect($items)
                ->map(fn ($value, $key): string => \sprintf('  • %s: %s', $key, $value))
                ->join("\n");
            note($title.PHP_EOL.$content);
        }
    }

    /**
     * Écrire la configuration personnalisée avec validation des différences
     */
    protected function writeConfiguration(string $licenseType, array $scanPaths, bool $enableBackups, array $excludePatterns = []): void
    {
        $configPath = config_path('fontawesome-migrator.php');

        // Charger la configuration par défaut depuis le package
        $defaultConfigPath = __DIR__.'/../../../config/fontawesome-migrator.php';

        if (! file_exists($defaultConfigPath)) {
            throw new Exception('Configuration par défaut introuvable : '.$defaultConfigPath);
        }

        $defaultConfig = include $defaultConfigPath;

        // Créer seulement les valeurs modifiées
        $customConfig = [];

        // Vérifier et ajouter seulement les valeurs différentes des défauts
        if ($licenseType !== $defaultConfig['license_type']) {
            $customConfig['license_type'] = $licenseType;
        }

        // N'écrire scan_paths que s'il y a vraiment des chemins personnalisés (non vides et différents des défauts)
        if ($scanPaths !== [] && $scanPaths !== $defaultConfig['scan_paths']) {
            $customConfig['scan_paths'] = $scanPaths;
        }

        if ($enableBackups !== $defaultConfig['backup_files']) {
            $customConfig['backup_files'] = $enableBackups;
        }

        // N'écrire exclude_patterns que s'ils sont différents des défauts
        if ($excludePatterns !== [] && $excludePatterns !== $defaultConfig['exclude_patterns']) {
            $customConfig['exclude_patterns'] = $excludePatterns;
        }

        // Si Pro, activer tous les styles seulement si différent du défaut
        if ($licenseType === 'pro') {
            $proStyles = [
                'light' => true,
                'duotone' => true,
                'thin' => true,
                'sharp' => true,
            ];

            if ($proStyles !== $defaultConfig['pro_styles']) {
                $customConfig['pro_styles'] = $proStyles;
            }
        }

        // Générer le contenu du fichier avec seulement les valeurs personnalisées
        $this->writeCustomConfigFile($configPath, $customConfig);
    }
}
