<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Support;

use Exception;
use FontAwesome\Migrator\Contracts\ConfigurationInterface;
use Illuminate\Support\Facades\File;

/**
 * Helper pour la gestion centralisée de la configuration
 */
class ConfigHelper implements ConfigurationInterface
{
    private ?array $config = null;

    private ?array $defaultConfig = null;

    /**
     * Obtenir la configuration complète du package
     */
    public function getConfig(): array
    {
        if ($this->config === null) {
            try {
                $this->config = config('fontawesome-migrator', []);

                // Si la config Laravel est vide, utiliser les défauts du package
                if ($this->config === []) {
                    $this->config = $this->getDefaultConfig();
                }
            } catch (Exception) {
                // Fallback pour les tests ou environnements sans configuration
                $this->config = $this->getDefaultConfig();
            }
        }

        return $this->config;
    }

    /**
     * Obtenir la configuration par défaut depuis le package
     */
    public function getDefaultConfig(): array
    {
        if ($this->defaultConfig === null) {
            $this->defaultConfig = require __DIR__.'/../../config/fontawesome-migrator.php';
        }

        return $this->defaultConfig;
    }

    /**
     * Obtenir une valeur de configuration spécifique
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $config = $this->getConfig();

        return data_get($config, $key, $default);
    }

    /**
     * Vérifier si une configuration est définie
     */
    public function has(string $key): bool
    {
        $config = $this->getConfig();

        return data_get($config, $key) !== null;
    }

    /**
     * Obtenir la licence (free/pro)
     */
    public function getLicenseType(): string
    {
        return $this->get('license_type', 'free');
    }

    /**
     * Vérifier si c'est une licence Pro
     */
    public function isProLicense(): bool
    {
        return $this->getLicenseType() === 'pro';
    }

    /**
     * Obtenir les chemins de scan
     */
    public function getScanPaths(): array
    {
        return $this->get('scan_paths', []);
    }

    /**
     * Obtenir les extensions de fichiers autorisées
     */
    public function getFileExtensions(): array
    {
        return $this->get('file_extensions', []);
    }

    /**
     * Obtenir les patterns d'exclusion
     */
    public function getExcludePatterns(): array
    {
        return $this->get('exclude_patterns', []);
    }

    /**
     * Vérifier si les sauvegardes sont activées
     */
    public function isBackupEnabled(): bool
    {
        return $this->get('backup_files', true);
    }

    /**
     * Obtenir le chemin des migrations
     */
    public function getMigrationsPath(): string
    {
        return $this->get('migrations_path', storage_path('app/fontawesome-migrator/migrations'));
    }

    /**
     * Réinitialiser le cache de configuration
     */
    public function clearCache(): void
    {
        $this->config = null;
        $this->defaultConfig = null;
    }

    /**
     * Valider la configuration
     */
    public function validate(): array
    {
        $errors = [];
        $config = $this->getConfig();

        // Vérifier les champs requis
        $requiredFields = ['license_type', 'scan_paths', 'file_extensions'];

        foreach ($requiredFields as $field) {
            if (! isset($config[$field])) {
                $errors[] = 'Configuration manquante : '.$field;
            }
        }

        // Valider le type de licence
        if (isset($config['license_type']) && ! \in_array($config['license_type'], ['free', 'pro'])) {
            $errors[] = "license_type doit être 'free' ou 'pro'";
        }

        // Valider que migrations_path est défini et accessible en écriture
        $migrationsPath = $this->getMigrationsPath();

        if (! File::isDirectory($migrationsPath)) {
            File::makeDirectory($migrationsPath, 0755, true, true);
        }

        if (! File::isWritable($migrationsPath)) {
            $errors[] = 'Le répertoire migrations_path n\'est pas accessible en écriture : '.$migrationsPath;
        }

        return $errors;
    }
}
