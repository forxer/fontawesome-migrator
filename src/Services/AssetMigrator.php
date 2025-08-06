<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services;

use FontAwesome\Migrator\Contracts\ConfigurationInterface;
use Illuminate\Support\Facades\File;

class AssetMigrator
{
    public function __construct(
        protected ConfigurationInterface $config,
        protected AssetReplacementService $replacementService,
    ) {}

    /**
     * Migrer les références d'assets FontAwesome dans un fichier
     */
    public function migrateAssets(string $filePath, string $content): string
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        // Appliquer les migrations selon le type de fichier
        return match ($extension) {
            'css', 'scss', 'sass' => $this->migrateStylesheetAssets($content),
            'js', 'ts' => $this->migrateJavaScriptAssets($content),
            'blade.php', 'php', 'html' => $this->migrateHtmlAssets($content),
            'vue' => $this->migrateVueAssets($content),
            'json' => $this->migratePackageJsonAssets($content),
            default => $content,
        };
    }

    /**
     * Migrer les assets dans les fichiers CSS/SCSS
     */
    protected function migrateStylesheetAssets(string $content): string
    {
        $isPro = $this->config->isProLicense();
        $replacements = $this->replacementService->getStylesheetReplacements($isPro);

        return $this->replacementService->applyReplacements($content, $replacements);
    }

    /**
     * Migrer les assets dans les fichiers JavaScript
     */
    protected function migrateJavaScriptAssets(string $content): string
    {
        $isPro = $this->config->isProLicense();
        $replacements = $this->replacementService->getJavaScriptReplacements($isPro);

        return $this->replacementService->applyReplacements($content, $replacements);
    }

    /**
     * Migrer les assets dans les fichiers HTML/Blade
     */
    protected function migrateHtmlAssets(string $content): string
    {
        $isPro = $this->config->isProLicense();
        $replacements = $this->replacementService->getHtmlReplacements($isPro);

        return $this->replacementService->applyReplacements($content, $replacements);
    }

    /**
     * Migrer les assets dans les fichiers Vue
     */
    protected function migrateVueAssets(string $content): string
    {
        $isPro = $this->config->isProLicense();
        $replacements = $this->replacementService->getVueReplacements($isPro);

        return $this->replacementService->applyReplacements($content, $replacements);
    }

    /**
     * Migrer les références dans package.json et autres gestionnaires de packages
     */
    protected function migratePackageJsonAssets(string $content): string
    {
        $isPro = $this->config->isProLicense();
        $replacements = $this->replacementService->getPackageJsonReplacements($isPro);

        return $this->replacementService->applyReplacements($content, $replacements);
    }

    /**
     * Analyser un fichier pour détecter les assets FontAwesome 5
     */
    public function analyzeAssets(string $filePath): array
    {
        if (! File::exists($filePath)) {
            return ['assets' => [], 'error' => 'Fichier non trouvé'];
        }

        $content = File::get($filePath);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        $assets = [];

        // Patterns pour détecter les assets FA5
        $patterns = $this->getAssetPatterns($extension);

        foreach ($patterns as $type => $pattern) {
            if (preg_match_all($pattern, $content, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
                foreach ($matches as $match) {
                    $assets[] = [
                        'type' => $type,
                        'original' => $match[0][0],
                        'offset' => $match[0][1],
                        'line' => substr_count(substr($content, 0, $match[0][1]), "\n") + 1,
                        'is_pro' => $this->isProAsset($match[0][0]),
                    ];
                }
            }
        }

        return ['assets' => $assets, 'error' => null];
    }

    /**
     * Détecter si un asset est une référence Pro
     */
    protected function isProAsset(string $assetString): bool
    {
        $proPatterns = [
            '/fontawesome-pro/',
            '/pro\.fontawesome\.com/',
            '/@fortawesome\/fontawesome-pro/',
            '/@fortawesome\/pro-/',
            '/fontawesome-pro-/',
        ];

        foreach ($proPatterns as $pattern) {
            if (preg_match($pattern, $assetString)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir les patterns de détection d'assets selon le type de fichier
     */
    protected function getAssetPatterns(string $extension): array
    {
        return match ($extension) {
            'css', 'scss', 'sass' => [
                'cdn_url' => '/https?:\/\/[^"\'\s]*font-?awesome[^"\'\s]*\/[v]?5\.[^"\'\s]*/i',
                'import' => '/@import\s+["\'][^"\']*(@fortawesome|font-?awesome)[^"\']*["\'];?/i',
                'local_path' => '/["\'][^"\']*font-?awesome-5[^"\']*["\']/',
                'node_modules' => '/node_modules\/@fortawesome\/[^"\'\s]*/i',
            ],
            'js', 'ts' => [
                'import' => '/from\s+["\'][^"\']*@fortawesome\/[^"\']*["\']/',
                'require' => '/require\s*\(\s*["\'][^"\']*@fortawesome\/[^"\']*["\']\s*\)/',
                'cdn_url' => '/https?:\/\/[^"\'\s]*font-?awesome[^"\'\s]*\/[v]?5\.[^"\'\s]*/i',
                'dynamic_import' => '/import\s*\(\s*["\'][^"\']*@fortawesome\/[^"\']*["\']\s*\)/',
            ],
            'html', 'blade.php', 'php' => [
                'link_tag' => '/<link[^>]*href=["\'][^"\']*font-?awesome[^"\']*\/[v]?5\.[^"\']*["\'][^>]*>/i',
                'script_tag' => '/<script[^>]*src=["\'][^"\']*font-?awesome[^"\']*\/[v]?5\.[^"\']*["\'][^>]*>/i',
                'cdn_url' => '/https?:\/\/[^"\'\s]*font-?awesome[^"\'\s]*\/[v]?5\.[^"\'\s]*/i',
                'asset_helper' => '/asset\s*\(\s*["\'][^"\']*font-?awesome-5[^"\']*["\']\s*\)/',
            ],
            'vue' => [
                'import' => '/from\s+["\'][^"\']*@fortawesome\/[^"\']*["\']/',
                'link_tag' => '/<link[^>]*href=["\'][^"\']*font-?awesome[^"\']*\/[v]?5\.[^"\']*["\'][^>]*>/i',
                'cdn_url' => '/https?:\/\/[^"\'\s]*font-?awesome[^"\'\s]*\/[v]?5\.[^"\'\s]*/i',
            ],
            'json' => [
                'npm_package' => '/"@fortawesome\/[^"]*":\s*"[^"]*5\.[^"]*"/',
                'free_package' => '/"@fortawesome\/fontawesome-free[^"]*":\s*"[^"]*"/',
                'pro_package' => '/"@fortawesome\/(fontawesome-)?pro[^"]*":\s*"[^"]*"/',
            ],
            default => [],
        };
    }

    /**
     * Vérifier si un fichier contient des assets FontAwesome 5
     */
    public function hasFA5Assets(string $filePath): bool
    {
        $analysis = $this->analyzeAssets($filePath);

        return ! empty($analysis['assets']);
    }

    /**
     * Obtenir les statistiques des assets détectés
     */
    public function getAssetStats(array $files): array
    {
        $stats = [
            'total_files_with_assets' => 0,
            'total_assets' => 0,
            'pro_assets' => 0,
            'free_assets' => 0,
            'by_type' => [],
            'by_extension' => [],
        ];

        foreach ($files as $file) {
            $analysis = $this->analyzeAssets($file['path']);

            if (! empty($analysis['assets'])) {
                $stats['total_files_with_assets']++;
                $extension = pathinfo((string) $file['path'], PATHINFO_EXTENSION);

                if (! isset($stats['by_extension'][$extension])) {
                    $stats['by_extension'][$extension] = 0;
                }

                $stats['by_extension'][$extension]++;

                foreach ($analysis['assets'] as $asset) {
                    $stats['total_assets']++;

                    if ($asset['is_pro']) {
                        $stats['pro_assets']++;
                    } else {
                        $stats['free_assets']++;
                    }

                    if (! isset($stats['by_type'][$asset['type']])) {
                        $stats['by_type'][$asset['type']] = 0;
                    }

                    $stats['by_type'][$asset['type']]++;
                }
            }
        }

        return $stats;
    }
}
