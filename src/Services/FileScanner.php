<?php

namespace FontAwesome\Migrator\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

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

        // Première passe pour compter les fichiers
        foreach ($paths as $path) {
            if (!File::exists(base_path($path))) {
                continue;
            }

            $finder = $this->createFinder($path);
            $totalFiles += iterator_count($finder);
        }

        // Deuxième passe pour collecter les fichiers
        foreach ($paths as $path) {
            if (!File::exists(base_path($path))) {
                continue;
            }

            $finder = $this->createFinder($path);

            foreach ($finder as $file) {
                $currentFile++;

                if ($progressCallback) {
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
        if (!empty($extensions)) {
            $patterns = array_map(fn($ext) => "*.$ext", $extensions);
            $finder->name($patterns);
        }

        // Exclure les patterns configurés
        $excludePatterns = $this->config['exclude_patterns'];
        foreach ($excludePatterns as $pattern) {
            if (str_contains($pattern, '/') || str_contains($pattern, '\\')) {
                // Pattern de chemin
                $finder->notPath($pattern);
            } else {
                // Pattern de nom de fichier
                $finder->notName($pattern);
            }
        }

        return $finder;
    }

    /**
     * Analyser un fichier spécifique pour détecter les icônes Font Awesome
     */
    public function analyzeFile(string $filePath): array
    {
        if (!File::exists($filePath)) {
            return [
                'icons' => [],
                'error' => 'Fichier non trouvé'
            ];
        }

        $content = File::get($filePath);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        return [
            'icons' => $this->extractFontAwesome5Icons($content, $extension),
            'content' => $content,
            'error' => null
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

                    if ($iconData) {
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

        switch ($extension) {
            case 'vue':
            case 'js':
            case 'ts':
                return array_merge($basePatterns, [
                    // Font Awesome Vue/React components
                    '/<FontAwesome[^>]*icon=["\']([^"\']+)["\'][^>]*>/',
                    '/icon:\s*["\']([^"\']+)["\']/',
                ]);

            case 'php':
            case 'blade.php':
                return array_merge($basePatterns, [
                    // Blade/PHP avec échappement
                    '/\{\{\s*["\']([^"\']*(?:fa[slrbad]|fas|far|fal|fab|fad)\s+fa-[a-zA-Z0-9-]+[^"\']*)["\']/',
                ]);

            default:
                return $basePatterns;
        }
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
        return !empty($analysis['icons']);
    }
}