<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services;

use FontAwesome\Migrator\Contracts\ConfigurationInterface;
use FontAwesome\Migrator\Contracts\FileScannerInterface;
use Illuminate\Support\Facades\File;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class FileScanner implements FileScannerInterface
{
    public function __construct(
        protected ConfigurationInterface $config,
        protected FileScanningService $scanningService
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
        $extensions = $this->scanningService->getSupportedExtensions();

        if ($extensions !== []) {
            $patterns = array_map(fn ($ext): string => '*.'.$ext, $extensions);
            $finder->name($patterns);
        }

        // Exclure les patterns configurés
        $excludePatterns = $this->scanningService->getExcludePatterns();

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
        $extensions = $this->scanningService->getSupportedExtensions();

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
        $analysisResult = $this->scanningService->analyzeFileForFontAwesome($filePath);

        if (isset($analysisResult['error'])) {
            return [
                'icons' => [],
                'error' => $analysisResult['error'],
            ];
        }

        return [
            'icons' => $analysisResult['icons'],
            'content' => file_exists($filePath) ? file_get_contents($filePath) : '',
            'error' => null,
            'detected_version' => $analysisResult['version'] ?? 'unknown',
        ];
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
