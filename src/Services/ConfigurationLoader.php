<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;

/**
 * Service de chargement de configuration avancée pour les mappings FontAwesome
 */
class ConfigurationLoader
{
    private array $cache = [];

    private readonly string $configPath;

    public function __construct(?string $configPath = null)
    {
        $this->configPath = $configPath ?? $this->getDefaultConfigPath();
    }

    /**
     * Charger tous les mappings pour une migration donnée
     */
    public function loadMigrationConfig(string $fromVersion, string $toVersion): array
    {
        $migrationKey = \sprintf('%s-to-%s', $fromVersion, $toVersion);

        if (isset($this->cache[$migrationKey])) {
            return $this->cache[$migrationKey];
        }

        $migrationPath = $this->configPath.('/mappings/'.$migrationKey);

        if (! File::isDirectory($migrationPath)) {
            throw new InvalidArgumentException('Configuration non trouvée pour la migration '.$migrationKey);
        }

        $config = [
            'version' => $migrationKey,
            'from' => $fromVersion,
            'to' => $toVersion,
            'styles' => $this->loadJsonFile($migrationPath.'/styles.json'),
            'icons' => $this->loadJsonFile($migrationPath.'/icons.json'),
            'deprecated' => $this->loadJsonFile($migrationPath.'/deprecated.json', []),
            'pro_only' => $this->loadJsonFile($migrationPath.'/pro-only.json', []),
            'new_icons' => $this->loadJsonFile($migrationPath.'/new-icons.json', []),
        ];

        $this->cache[$migrationKey] = $config;

        return $config;
    }

    /**
     * Charger les mappings de styles pour une migration
     */
    public function loadStyleMappings(string $fromVersion, string $toVersion): array
    {
        $config = $this->loadMigrationConfig($fromVersion, $toVersion);

        return $config['styles']['mappings'] ?? [];
    }

    /**
     * Charger les mappings d'icônes pour une migration
     */
    public function loadIconMappings(string $fromVersion, string $toVersion): array
    {
        $config = $this->loadMigrationConfig($fromVersion, $toVersion);
        $iconConfig = $config['icons'];

        $mappings = [];

        // Fusionner tous les types de mappings d'icônes
        if (isset($iconConfig['outlined_icons']['mappings'])) {
            $mappings = array_merge($mappings, $iconConfig['outlined_icons']['mappings']);
        }

        if (isset($iconConfig['renamed_icons'])) {
            return array_merge($mappings, $iconConfig['renamed_icons']);
        }

        return $mappings;
    }

    /**
     * Charger les icônes dépréciées pour une migration
     */
    public function loadDeprecatedIcons(string $fromVersion, string $toVersion): array
    {
        $config = $this->loadMigrationConfig($fromVersion, $toVersion);

        return array_merge(
            $config['deprecated']['deprecated_icons'] ?? [],
            $config['deprecated']['deprecated_classes'] ?? []
        );
    }

    /**
     * Charger les icônes Pro uniquement pour une migration
     */
    public function loadProOnlyIcons(string $fromVersion, string $toVersion): array
    {
        $config = $this->loadMigrationConfig($fromVersion, $toVersion);

        return array_merge(
            $config['pro_only']['pro_styles'] ?? [],
            $config['pro_only']['pro_icons'] ?? []
        );
    }

    /**
     * Charger les nouvelles icônes introduites dans une version
     */
    public function loadNewIcons(string $fromVersion, string $toVersion): array
    {
        $config = $this->loadMigrationConfig($fromVersion, $toVersion);

        return array_merge(
            $config['new_icons']['new_styles'] ?? [],
            $config['new_icons']['new_icons'] ?? []
        );
    }

    /**
     * Obtenir toutes les migrations disponibles
     */
    public function getAvailableMigrations(): array
    {
        $mappingsPath = $this->configPath.'/mappings';

        if (! File::isDirectory($mappingsPath)) {
            return [];
        }

        $migrations = [];
        $directories = File::directories($mappingsPath);

        foreach ($directories as $directory) {
            $migrationName = basename((string) $directory);

            if (preg_match('/^(\d+)-to-(\d+)$/', $migrationName, $matches)) {
                $migrations[] = [
                    'key' => $migrationName,
                    'from' => $matches[1],
                    'to' => $matches[2],
                    'path' => $directory,
                ];
            }
        }

        return $migrations;
    }

    /**
     * Vider le cache
     */
    public function clearCache(): void
    {
        $this->cache = [];
    }

    /**
     * Charger un fichier JSON
     */
    private function loadJsonFile(string $path, ?array $default = null): array
    {
        if (! File::exists($path)) {
            if ($default !== null) {
                return $default;
            }

            throw new InvalidArgumentException('Fichier de configuration non trouvé : '.$path);
        }

        $content = File::get($path);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException(\sprintf('Erreur de parsing JSON dans %s : ', $path).json_last_error_msg());
        }

        return $data;
    }

    /**
     * Obtenir le chemin de configuration par défaut
     */
    private function getDefaultConfigPath(): string
    {
        return base_path('config/fontawesome-migrator');
    }

    /**
     * Valider la structure d'une configuration de migration
     */
    public function validateMigrationConfig(string $fromVersion, string $toVersion): array
    {
        $errors = [];
        $migrationKey = \sprintf('%s-to-%s', $fromVersion, $toVersion);
        $migrationPath = $this->configPath.('/mappings/'.$migrationKey);

        $requiredFiles = ['styles.json', 'icons.json'];
        $optionalFiles = ['deprecated.json', 'pro-only.json', 'new-icons.json'];

        // Vérifier les fichiers requis
        foreach ($requiredFiles as $file) {
            $filePath = $migrationPath.'/'.$file;

            if (! File::exists($filePath)) {
                $errors[] = 'Fichier requis manquant : '.$file;
            } else {
                try {
                    $this->loadJsonFile($filePath);
                } catch (InvalidArgumentException $e) {
                    $errors[] = \sprintf('Erreur dans %s : ', $file).$e->getMessage();
                }
            }
        }

        // Vérifier les fichiers optionnels (s'ils existent)
        foreach ($optionalFiles as $file) {
            $filePath = $migrationPath.'/'.$file;

            if (File::exists($filePath)) {
                try {
                    $this->loadJsonFile($filePath);
                } catch (InvalidArgumentException $e) {
                    $errors[] = \sprintf('Erreur dans %s : ', $file).$e->getMessage();
                }
            }
        }

        return $errors;
    }
}
