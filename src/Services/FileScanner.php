<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services;

use Exception;
use FontAwesome\Migrator\Contracts\ConfigurationInterface;
use FontAwesome\Migrator\Contracts\FileScannerInterface;
use Illuminate\Support\Facades\File;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class FileScanner implements FileScannerInterface
{
    public function __construct(
        protected ConfigurationInterface $config,
        protected MigrationVersionManager $versionManager,
        protected ConfigurationLoader $configLoader,
        protected FontAwesomePatternService $patternService
    ) {}

    /**
     * Scanner les chemins spécifiés et retourner la liste des fichiers à traiter
     */
    public function scanPaths(array $paths, ?callable $progressCallback = null): array
    {
        $files = [];
        $totalFiles = 0;
        $currentFile = 0;

        // Séparer les fichiers et répertoires
        $filePaths = [];
        $directoryPaths = [];

        foreach ($paths as $path) {
            $fullPath = base_path($path);

            if (! File::exists($fullPath)) {
                continue;
            }

            if (is_file($fullPath)) {
                $filePaths[] = $path;
            } else {
                $directoryPaths[] = $path;
            }
        }

        // Première passe pour compter les fichiers
        // Compter les fichiers individuels
        $totalFiles = \count($filePaths);

        // Compter les fichiers dans les répertoires
        foreach ($directoryPaths as $path) {
            $finder = $this->createFinder($path);
            $totalFiles += iterator_count($finder);
        }

        // Deuxième passe pour collecter les fichiers
        // Traiter les fichiers individuels
        foreach ($filePaths as $path) {
            $currentFile++;

            if ($progressCallback !== null) {
                $progressCallback($currentFile, $totalFiles);
            }

            $fullPath = base_path($path);
            $fileInfo = new SplFileInfo($fullPath);

            // Vérifier si l'extension est acceptée
            if ($this->isFileExtensionAllowed($fileInfo->getExtension())) {
                $files[] = [
                    'path' => $fullPath,
                    'relative_path' => $path,
                    'extension' => $fileInfo->getExtension(),
                    'size' => $fileInfo->getSize(),
                ];
            }
        }

        // Traiter les répertoires
        foreach ($directoryPaths as $path) {
            $finder = $this->createFinder($path);

            foreach ($finder as $file) {
                $currentFile++;

                if ($progressCallback !== null) {
                    $progressCallback($currentFile, $totalFiles);
                }

                $files[] = [
                    'path' => $file->getRealPath(),
                    'relative_path' => $file->getRelativePathname(),
                    'extension' => $file->getExtension(),
                    'size' => $file->getSize(),
                ];
            }
        }

        return $files;
    }

    /**
     * Créer un finder configuré pour un chemin donné
     */
    protected function createFinder(string $path): Finder
    {
        $finder = new Finder();
        $finder->files()->in(base_path($path));

        // Ajouter les extensions de fichiers
        $extensions = $this->config->getFileExtensions();

        if ($extensions !== []) {
            $patterns = array_map(fn ($ext): string => '*.'.$ext, $extensions);
            $finder->name($patterns);
        }

        // Exclure les patterns configurés
        $excludePatterns = $this->config->getExcludePatterns();

        foreach ($excludePatterns as $pattern) {
            if (str_contains((string) $pattern, '/') || str_contains((string) $pattern, '\\')) {
                // Pattern de chemin
                $finder->notPath($pattern);
            } elseif (str_contains((string) $pattern, '*')) {
                // Pattern avec wildcards pour nom de fichier
                $finder->notName($pattern);
            } else {
                // Pattern simple - exclure les dossiers et fichiers
                $finder->notPath($pattern)->notName($pattern);
            }
        }

        return $finder;
    }

    /**
     * Vérifier si une extension de fichier est autorisée
     */
    protected function isFileExtensionAllowed(string $extension): bool
    {
        $extensions = $this->config->getFileExtensions();

        if ($extensions === []) {
            return true; // Si aucune extension configurée, accepter tous les fichiers
        }

        return \in_array($extension, $extensions);
    }

    /**
     * Analyser un fichier spécifique pour détecter les icônes Font Awesome
     */
    public function analyzeFile(string $filePath): array
    {
        if (! File::exists($filePath)) {
            return [
                'icons' => [],
                'error' => 'Fichier non trouvé',
            ];
        }

        $content = File::get($filePath);

        // Détecter la version FontAwesome via le PatternService centralisé
        $detectedVersion = $this->patternService->detectVersion($content);

        if ($detectedVersion === 'unknown') {
            return [
                'icons' => [],
                'content' => $content,
                'error' => null,
                'detected_version' => 'unknown',
            ];
        }

        // Utiliser la configuration existante pour analyser les icônes
        $icons = $this->extractIconsUsingConfiguration($content, $detectedVersion);

        return [
            'icons' => $icons,
            'content' => $content,
            'error' => null,
            'detected_version' => $detectedVersion,
        ];
    }

    /**
     * Extraire les icônes FontAwesome en utilisant la configuration existante
     */
    protected function extractIconsUsingConfiguration(string $content, string $version): array
    {
        $icons = [];

        try {
            // Utiliser le PatternService centralisé pour extraire les icônes
            $versionIcons = $this->patternService->extractVersionIcons($content, $version);

            // Enrichir avec les données de mapping si une version cible existe
            $targetVersion = $this->getNextVersion($version);

            if ($targetVersion !== null && $targetVersion !== '' && $targetVersion !== '0') {
                $styleMappings = $this->configLoader->loadStyleMappings($version, $targetVersion);

                foreach ($versionIcons as $iconData) {
                    $parsedIcon = $this->patternService->parseIconWithStyleMappings($iconData['full_match'], $styleMappings);

                    $icons[] = $parsedIcon !== null ? array_merge($iconData, $parsedIcon) : $iconData;
                }
            } else {
                $icons = $versionIcons;
            }
        } catch (Exception) {
            // Si erreur de configuration, utiliser fallback
            return [];
        }

        return array_unique($icons, SORT_REGULAR);
    }

    /**
     * Obtenir la version cible pour une version source donnée
     */
    protected function getNextVersion(string $version): ?string
    {
        return match ($version) {
            '4' => '5',
            '5' => '6',
            '6' => '7',
            default => null,
        };
    }

    /**
     * Vérifier si un fichier contient des icônes Font Awesome 5 (compatibilité interface)
     */
    public function hasFontAwesome5Icons(string $filePath): bool
    {
        if (! File::exists($filePath)) {
            return false;
        }

        $content = File::get($filePath);

        return $this->patternService->hasVersionIcons($content, '5');
    }

    /**
     * Vérifier si un fichier contient des icônes Font Awesome (générique)
     */
    public function hasFontAwesomeIcons(string $filePath): bool
    {
        $analysis = $this->analyzeFile($filePath);

        return ! empty($analysis['icons']);
    }
}
