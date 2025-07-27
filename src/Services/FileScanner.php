<?php

namespace FontAwesome\Migrator\Services;

use Illuminate\Support\Facades\File;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class FileScanner
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('fontawesome-migrator');
    }

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
        $extensions = $this->config['file_extensions'];

        if (! empty($extensions)) {
            $patterns = array_map(fn ($ext): string => '*.'.$ext, $extensions);
            $finder->name($patterns);
        }

        // Exclure les patterns configurés
        $excludePatterns = $this->config['exclude_patterns'];

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
        $extensions = $this->config['file_extensions'];

        if (empty($extensions)) {
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
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return [
            'icons' => $this->extractFontAwesome5Icons($content, $extension),
            'content' => $content,
            'error' => null,
        ];
    }

    /**
     * Extraire les icônes Font Awesome 5 d'un contenu
     */
    protected function extractFontAwesome5Icons(string $content, string $extension): array
    {
        $icons = [];
        $patterns = $this->getFontAwesome5Patterns($extension);

        foreach ($patterns as $pattern) {
            if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
                foreach ($matches as $match) {
                    $fullMatch = $match[0][0];
                    $offset = $match[0][1];

                    // Extraire le style et le nom de l'icône
                    $iconData = $this->parseIconString($fullMatch, $extension);

                    if ($iconData !== null && $iconData !== []) {
                        $iconData['full_match'] = $fullMatch;
                        $iconData['offset'] = $offset;
                        $iconData['line'] = substr_count(substr($content, 0, $offset), "\n") + 1;

                        $icons[] = $iconData;
                    }
                }
            }
        }

        // Supprimer les doublons
        return array_unique($icons, SORT_REGULAR);
    }

    /**
     * Obtenir les patterns regex pour Font Awesome 5 selon le type de fichier
     */
    protected function getFontAwesome5Patterns(string $extension): array
    {
        $basePatterns = [
            // Classes CSS classiques (fa[s|r|l|b|d] fa-icon-name)
            '/\b(fa[slrbad])\s+(fa-[a-zA-Z0-9-]+)\b/',

            // Classes avec préfixes complets
            '/\b(fas|far|fal|fab|fad)\s+(fa-[a-zA-Z0-9-]+)\b/',

            // Dans les attributs class
            '/class=["\']([^"\']*\b(?:fa[slrbad]|fas|far|fal|fab|fad)\s+fa-[a-zA-Z0-9-]+[^"\']*)["\']/',
        ];

        return match ($extension) {
            'vue', 'js', 'ts' => array_merge($basePatterns, [
                // Font Awesome Vue/React components
                '/<FontAwesome[^>]*icon=["\']([^"\']+)["\'][^>]*>/',
                '/icon:\s*["\']([^"\']+)["\']/',
            ]),
            'php', 'blade.php' => array_merge($basePatterns, [
                // Blade/PHP avec échappement
                '/\{\{\s*["\']([^"\']*(?:fa[slrbad]|fas|far|fal|fab|fad)\s+fa-[a-zA-Z0-9-]+[^"\']*)["\']/',
            ]),
            default => $basePatterns,
        };
    }

    /**
     * Parser une chaîne d'icône pour extraire le style et le nom
     */
    protected function parseIconString(string $iconString, string $extension): ?array
    {
        // Pattern pour capturer style et nom d'icône
        if (preg_match('/\b(fa[slrbad]|fas|far|fal|fab|fad)\s+(fa-[a-zA-Z0-9-]+)\b/', $iconString, $matches)) {
            return [
                'style' => $matches[1],
                'name' => $matches[2],
                'original' => $iconString,
            ];
        }

        return null;
    }

    /**
     * Vérifier si un fichier contient des icônes Font Awesome 5
     */
    public function hasFontAwesome5Icons(string $filePath): bool
    {
        $analysis = $this->analyzeFile($filePath);

        return ! empty($analysis['icons']);
    }
}
