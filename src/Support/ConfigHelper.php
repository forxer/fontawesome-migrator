<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Support;

use Exception;

/**
 * Helper pour la gestion centralisée de la configuration
 */
class ConfigHelper
{
    private static ?array $config = null;

    private static ?array $defaultConfig = null;

    /**
     * Obtenir la configuration complète du package
     */
    public static function getConfig(): array
    {
        if (self::$config === null) {
            try {
                self::$config = config('fontawesome-migrator', []);

                // Si la config Laravel est vide, utiliser les défauts du package
                if (self::$config === []) {
                    self::$config = self::getDefaultConfig();
                }
            } catch (Exception) {
                // Fallback pour les tests ou environnements sans configuration
                self::$config = self::getDefaultConfig();
            }
        }

        return self::$config;
    }

    /**
     * Obtenir la configuration par défaut depuis le package
     */
    public static function getDefaultConfig(): array
    {
        if (self::$defaultConfig === null) {
            self::$defaultConfig = require __DIR__.'/../../config/fontawesome-migrator.php';
        }

        return self::$defaultConfig;
    }

    /**
     * Obtenir une valeur de configuration spécifique
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $config = self::getConfig();

        return data_get($config, $key, $default);
    }

    /**
     * Vérifier si une configuration est définie
     */
    public static function has(string $key): bool
    {
        $config = self::getConfig();

        return data_get($config, $key) !== null;
    }

    /**
     * Obtenir la licence (free/pro)
     */
    public static function getLicenseType(): string
    {
        return self::get('license_type', 'free');
    }

    /**
     * Vérifier si c'est une licence Pro
     */
    public static function isProLicense(): bool
    {
        return self::getLicenseType() === 'pro';
    }

    /**
     * Obtenir les chemins de scan
     */
    public static function getScanPaths(): array
    {
        return self::get('scan_paths', []);
    }

    /**
     * Obtenir les extensions de fichiers autorisées
     */
    public static function getFileExtensions(): array
    {
        return self::get('file_extensions', []);
    }

    /**
     * Obtenir les patterns d'exclusion
     */
    public static function getExcludePatterns(): array
    {
        return self::get('exclude_patterns', []);
    }

    /**
     * Vérifier si les sauvegardes sont activées
     */
    public static function isBackupEnabled(): bool
    {
        return self::get('backup_files', true);
    }

    /**
     * Obtenir le chemin des migrations
     */
    public static function getMigrationsPath(): string
    {
        return self::get('migrations_path', storage_path('app/fontawesome-migrator/migrations'));
    }

    /**
     * Réinitialiser le cache de configuration
     */
    public static function clearCache(): void
    {
        self::$config = null;
        self::$defaultConfig = null;
    }

    /**
     * Valider la configuration
     */
    public static function validate(): array
    {
        $errors = [];
        $config = self::getConfig();

        // Vérifier les champs requis
        $requiredFields = ['license_type', 'scan_paths', 'file_extensions'];

        foreach ($requiredFields as $field) {
            if (! isset($config[$field])) {
                $errors[] = "Configuration manquante : {$field}";
            }
        }

        // Valider le type de licence
        if (isset($config['license_type']) && ! \in_array($config['license_type'], ['free', 'pro'])) {
            $errors[] = "license_type doit être 'free' ou 'pro'";
        }

        // Valider que migrations_path est défini et accessible en écriture
        $migrationsPath = self::getMigrationsPath();

        if (! is_dir($migrationsPath) && ! mkdir($migrationsPath, 0755, true)) {
            $errors[] = "Le répertoire migrations_path n'est pas accessible en écriture : {$migrationsPath}";
        }

        return $errors;
    }
}
