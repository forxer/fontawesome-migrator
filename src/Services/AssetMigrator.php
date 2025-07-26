<?php

namespace FontAwesome\Migrator\Services;

use Exception;
use Illuminate\Support\Facades\File;

class AssetMigrator
{
    public function __construct()
    {
        // Configuration chargée dynamiquement pour les tests
    }

    /**
     * Obtenir la configuration actuelle
     */
    protected function getConfig(): array
    {
        try {
            return config('fontawesome-migrator', []);
        } catch (Exception) {
            // Fallback pour les tests ou environnements sans configuration
            return [
                'license_type' => 'free',
                'scan_paths' => [],
                'generate_report' => true,
                'backup_files' => true,
            ];
        }
    }

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
        $config = $this->getConfig();
        $isPro = ($config['license_type'] ?? 'free') === 'pro';

        $replacements = [
            // CDN URLs - Free (patterns plus spécifiques)
            'font-awesome/5.' => 'font-awesome/6.',
            '/font-awesome/5.' => '/font-awesome/6.',
            'https://use.fontawesome.com/releases/v5.' => 'https://use.fontawesome.com/releases/v6.',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.',
            'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.' => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.',
            'https://maxcdn.bootstrapcdn.com/font-awesome/5.' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.',

            // Local asset paths
            '/fontawesome-free-5.' => '/fontawesome-free-6.',
            '/font-awesome-5.' => '/font-awesome-6.',

            // SCSS imports - Free
            '@import "~@fortawesome/fontawesome-free/scss/fontawesome";' => '@import "~@fortawesome/fontawesome-free/scss/fontawesome";',
            '@import "~@fortawesome/fontawesome-free/scss/solid";' => '@import "~@fortawesome/fontawesome-free/scss/solid";',
            '@import "~@fortawesome/fontawesome-free/scss/regular";' => '@import "~@fortawesome/fontawesome-free/scss/regular";',
            '@import "~@fortawesome/fontawesome-free/scss/brands";' => '@import "~@fortawesome/fontawesome-free/scss/brands";',

            // Node modules paths
            'node_modules/@fortawesome/fontawesome-free-' => 'node_modules/@fortawesome/fontawesome-free/',
        ];

        // Ajout des remplacements Pro si activé
        if ($isPro) {
            $proReplacements = [
                // Pro CDN (si existe)
                'https://pro.fontawesome.com/releases/v5.' => 'https://pro.fontawesome.com/releases/v6.',

                // Pro SCSS imports
                '@import "~@fortawesome/fontawesome-pro/scss/fontawesome";' => '@import "~@fortawesome/fontawesome-pro/scss/fontawesome";',
                '@import "~@fortawesome/fontawesome-pro/scss/solid";' => '@import "~@fortawesome/fontawesome-pro/scss/solid";',
                '@import "~@fortawesome/fontawesome-pro/scss/regular";' => '@import "~@fortawesome/fontawesome-pro/scss/regular";',
                '@import "~@fortawesome/fontawesome-pro/scss/light";' => '@import "~@fortawesome/fontawesome-pro/scss/light";',
                '@import "~@fortawesome/fontawesome-pro/scss/duotone";' => '@import "~@fortawesome/fontawesome-pro/scss/duotone";',
                '@import "~@fortawesome/fontawesome-pro/scss/thin";' => '@import "~@fortawesome/fontawesome-pro/scss/thin";',
                '@import "~@fortawesome/fontawesome-pro/scss/brands";' => '@import "~@fortawesome/fontawesome-pro/scss/brands";',

                // Pro package paths
                'node_modules/@fortawesome/fontawesome-pro-' => 'node_modules/@fortawesome/fontawesome-pro/',
                '/fontawesome-pro-5.' => '/fontawesome-pro-6.',
            ];

            $replacements = array_merge($replacements, $proReplacements);
        }

        return $this->applyReplacements($content, $replacements);
    }

    /**
     * Migrer les assets dans les fichiers JavaScript
     */
    protected function migrateJavaScriptAssets(string $content): string
    {
        $config = $this->getConfig();
        $isPro = ($config['license_type'] ?? 'free') === 'pro';

        $replacements = [
            // Package managers - Free packages
            'from "@fortawesome/fontawesome-free-solid"' => 'from "@fortawesome/free-solid-svg-icons"',
            'from "@fortawesome/fontawesome-free-regular"' => 'from "@fortawesome/free-regular-svg-icons"',
            'from "@fortawesome/fontawesome-free-brands"' => 'from "@fortawesome/free-brands-svg-icons"',
            'from "@fortawesome/fontawesome-free/js/all"' => 'from "@fortawesome/fontawesome-free/js/all"',
            'from "@fortawesome/fontawesome-free/js/fontawesome"' => 'from "@fortawesome/fontawesome-free/js/fontawesome"',

            // Free webpack.mix.js et autres bundlers - Fichiers JS individuels
            '@fortawesome/fontawesome-free/js/brands.js' => '@fortawesome/fontawesome-free/js/brands.js',
            '@fortawesome/fontawesome-free/js/solid.js' => '@fortawesome/fontawesome-free/js/solid.js',
            '@fortawesome/fontawesome-free/js/regular.js' => '@fortawesome/fontawesome-free/js/regular.js',
            '@fortawesome/fontawesome-free/js/fontawesome.js' => '@fortawesome/fontawesome-free/js/fontawesome.js',

            // Free webpack.mix.js avec concaténation de variables
            "'@fortawesome/fontawesome-free/js/brands.js'" => "'@fortawesome/fontawesome-free/js/brands.js'",
            "'@fortawesome/fontawesome-free/js/solid.js'" => "'@fortawesome/fontawesome-free/js/solid.js'",
            "'@fortawesome/fontawesome-free/js/regular.js'" => "'@fortawesome/fontawesome-free/js/regular.js'",
            "'@fortawesome/fontawesome-free/js/fontawesome.js'" => "'@fortawesome/fontawesome-free/js/fontawesome.js'",

            // Dynamic imports
            'import("@fortawesome/fontawesome-free-solid")' => 'import("@fortawesome/free-solid-svg-icons")',
            'import("@fortawesome/fontawesome-free-regular")' => 'import("@fortawesome/free-regular-svg-icons")',
            'import("@fortawesome/fontawesome-free-brands")' => 'import("@fortawesome/free-brands-svg-icons")',

            // CommonJS require
            'require("@fortawesome/fontawesome-free-solid")' => 'require("@fortawesome/free-solid-svg-icons")',
            'require("@fortawesome/fontawesome-free-regular")' => 'require("@fortawesome/free-regular-svg-icons")',
            'require("@fortawesome/fontawesome-free-brands")' => 'require("@fortawesome/free-brands-svg-icons")',
            'require("@fortawesome/fontawesome-free/js/all")' => 'require("@fortawesome/fontawesome-free/js/all")',

            // CDN imports
            'https://use.fontawesome.com/releases/v5.' => 'https://use.fontawesome.com/releases/v6.',
            'https://kit.fontawesome.com/' => 'https://kit.fontawesome.com/', // Kit URLs restent identiques
        ];

        // Ajout des remplacements Pro si activé
        if ($isPro) {
            $proReplacements = [
                // Pro packages ES6 imports
                'from "@fortawesome/fontawesome-pro-solid"' => 'from "@fortawesome/pro-solid-svg-icons"',
                'from "@fortawesome/fontawesome-pro-regular"' => 'from "@fortawesome/pro-regular-svg-icons"',
                'from "@fortawesome/fontawesome-pro-light"' => 'from "@fortawesome/pro-light-svg-icons"',
                'from "@fortawesome/fontawesome-pro-duotone"' => 'from "@fortawesome/pro-duotone-svg-icons"',
                'from "@fortawesome/fontawesome-pro-thin"' => 'from "@fortawesome/pro-thin-svg-icons"',
                'from "@fortawesome/fontawesome-pro-brands"' => 'from "@fortawesome/free-brands-svg-icons"', // Brands reste free
                'from "@fortawesome/fontawesome-pro/js/all"' => 'from "@fortawesome/fontawesome-pro/js/all"',

                // Pro webpack.mix.js et autres bundlers - Fichiers JS individuels
                '@fortawesome/fontawesome-pro/js/brands.js' => '@fortawesome/fontawesome-pro/js/brands.js', // Brands reste identique
                '@fortawesome/fontawesome-pro/js/solid.js' => '@fortawesome/fontawesome-pro/js/solid.js',
                '@fortawesome/fontawesome-pro/js/regular.js' => '@fortawesome/fontawesome-pro/js/regular.js',
                '@fortawesome/fontawesome-pro/js/light.js' => '@fortawesome/fontawesome-pro/js/light.js',
                '@fortawesome/fontawesome-pro/js/duotone.js' => '@fortawesome/fontawesome-pro/js/duotone.js',
                '@fortawesome/fontawesome-pro/js/thin.js' => '@fortawesome/fontawesome-pro/js/thin.js',
                '@fortawesome/fontawesome-pro/js/fontawesome.js' => '@fortawesome/fontawesome-pro/js/fontawesome.js',

                // Pro webpack.mix.js avec concaténation de variables (vendor + '@fortawesome/...')
                "'@fortawesome/fontawesome-pro/js/brands.js'" => "'@fortawesome/fontawesome-pro/js/brands.js'",
                "'@fortawesome/fontawesome-pro/js/solid.js'" => "'@fortawesome/fontawesome-pro/js/solid.js'",
                "'@fortawesome/fontawesome-pro/js/regular.js'" => "'@fortawesome/fontawesome-pro/js/regular.js'",
                "'@fortawesome/fontawesome-pro/js/light.js'" => "'@fortawesome/fontawesome-pro/js/light.js'",
                "'@fortawesome/fontawesome-pro/js/duotone.js'" => "'@fortawesome/fontawesome-pro/js/duotone.js'",
                "'@fortawesome/fontawesome-pro/js/thin.js'" => "'@fortawesome/fontawesome-pro/js/thin.js'",
                "'@fortawesome/fontawesome-pro/js/fontawesome.js'" => "'@fortawesome/fontawesome-pro/js/fontawesome.js'",

                // Pro dynamic imports
                'import("@fortawesome/fontawesome-pro-solid")' => 'import("@fortawesome/pro-solid-svg-icons")',
                'import("@fortawesome/fontawesome-pro-regular")' => 'import("@fortawesome/pro-regular-svg-icons")',
                'import("@fortawesome/fontawesome-pro-light")' => 'import("@fortawesome/pro-light-svg-icons")',
                'import("@fortawesome/fontawesome-pro-duotone")' => 'import("@fortawesome/pro-duotone-svg-icons")',
                'import("@fortawesome/fontawesome-pro-thin")' => 'import("@fortawesome/pro-thin-svg-icons")',

                // Pro CommonJS require
                'require("@fortawesome/fontawesome-pro-solid")' => 'require("@fortawesome/pro-solid-svg-icons")',
                'require("@fortawesome/fontawesome-pro-regular")' => 'require("@fortawesome/pro-regular-svg-icons")',
                'require("@fortawesome/fontawesome-pro-light")' => 'require("@fortawesome/pro-light-svg-icons")',
                'require("@fortawesome/fontawesome-pro-duotone")' => 'require("@fortawesome/pro-duotone-svg-icons")',
                'require("@fortawesome/fontawesome-pro-thin")' => 'require("@fortawesome/pro-thin-svg-icons")',
                'require("@fortawesome/fontawesome-pro/js/all")' => 'require("@fortawesome/fontawesome-pro/js/all")',

                // Pro CDN
                'https://pro.fontawesome.com/releases/v5.' => 'https://pro.fontawesome.com/releases/v6.',
            ];

            $replacements = array_merge($replacements, $proReplacements);
        }

        return $this->applyReplacements($content, $replacements);
    }

    /**
     * Migrer les assets dans les fichiers HTML/Blade
     */
    protected function migrateHtmlAssets(string $content): string
    {
        $config = $this->getConfig();
        $isPro = ($config['license_type'] ?? 'free') === 'pro';

        $replacements = [
            // CDN links - Free
            'https://use.fontawesome.com/releases/v5.' => 'https://use.fontawesome.com/releases/v6.',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.',
            'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.' => 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.',
            'https://maxcdn.bootstrapcdn.com/font-awesome/5.' => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.',

            // Local asset references
            '/css/fontawesome.min.css' => '/css/fontawesome.min.css',
            '/css/all.min.css' => '/css/all.min.css',
            '/js/fontawesome.min.js' => '/js/fontawesome.min.js',
            '/js/all.min.js' => '/js/all.min.js',

            // Asset helper functions (Laravel)
            "asset('fontawesome-free-5" => "asset('fontawesome-free-6",
            "asset('font-awesome-5" => "asset('font-awesome-6",
            'public/fontawesome-free-5' => 'public/fontawesome-free-6',
            'public/font-awesome-5' => 'public/font-awesome-6',
        ];

        // Ajout des remplacements Pro si activé
        if ($isPro) {
            $proReplacements = [
                // Pro CDN links
                'https://pro.fontawesome.com/releases/v5.' => 'https://pro.fontawesome.com/releases/v6.',

                // Pro local assets
                "asset('fontawesome-pro-5" => "asset('fontawesome-pro-6",
                'public/fontawesome-pro-5' => 'public/fontawesome-pro-6',
                '/fontawesome-pro-5.' => '/fontawesome-pro-6.',
            ];

            $replacements = array_merge($replacements, $proReplacements);
        }

        return $this->applyReplacements($content, $replacements);
    }

    /**
     * Migrer les assets dans les fichiers Vue
     */
    protected function migrateVueAssets(string $content): string
    {
        // Combiner les migrations HTML et JavaScript pour Vue
        $content = $this->migrateHtmlAssets($content);

        return $this->migrateJavaScriptAssets($content);
    }

    /**
     * Migrer les références dans package.json et autres gestionnaires de packages
     */
    protected function migratePackageJsonAssets(string $content): string
    {
        $config = $this->getConfig();
        $isPro = ($config['license_type'] ?? 'free') === 'pro';

        $replacements = [
            // NPM packages - Free
            '"@fortawesome/fontawesome-free": "^5.' => '"@fortawesome/fontawesome-free": "^6.',
            '"@fortawesome/fontawesome-free-solid": "^5.' => '"@fortawesome/free-solid-svg-icons": "^6.',
            '"@fortawesome/fontawesome-free-regular": "^5.' => '"@fortawesome/free-regular-svg-icons": "^6.',
            '"@fortawesome/fontawesome-free-brands": "^5.' => '"@fortawesome/free-brands-svg-icons": "^6.',
            '"@fortawesome/fontawesome-svg-core": "^1.' => '"@fortawesome/fontawesome-svg-core": "^6.',
            '"@fortawesome/fontawesome-svg-core": "^5.' => '"@fortawesome/fontawesome-svg-core": "^6.',

            // Vue/React FontAwesome packages
            '"@fortawesome/vue-fontawesome": "^2.' => '"@fortawesome/vue-fontawesome": "^3.',
            '"@fortawesome/react-fontawesome": "^0.' => '"@fortawesome/react-fontawesome": "^0.',

            // Yarn/pnpm versions (sans ^)
            '"@fortawesome/fontawesome-free": "5.' => '"@fortawesome/fontawesome-free": "6.',
            '"@fortawesome/fontawesome-svg-core": "1.' => '"@fortawesome/fontawesome-svg-core": "6.',
        ];

        // Ajout des remplacements Pro si activé
        if ($isPro) {
            $proReplacements = [
                // Pro packages
                '"@fortawesome/fontawesome-pro": "^5.' => '"@fortawesome/fontawesome-pro": "^6.',
                '"@fortawesome/pro-solid-svg-icons": "^5.' => '"@fortawesome/pro-solid-svg-icons": "^6.',
                '"@fortawesome/pro-regular-svg-icons": "^5.' => '"@fortawesome/pro-regular-svg-icons": "^6.',
                '"@fortawesome/pro-light-svg-icons": "^5.' => '"@fortawesome/pro-light-svg-icons": "^6.',
                '"@fortawesome/pro-duotone-svg-icons": "^5.' => '"@fortawesome/pro-duotone-svg-icons": "^6.',
                '"@fortawesome/pro-thin-svg-icons": "^5.' => '"@fortawesome/pro-thin-svg-icons": "^6.',

                // Legacy Pro packages
                '"@fortawesome/fontawesome-pro-solid": "^5.' => '"@fortawesome/pro-solid-svg-icons": "^6.',
                '"@fortawesome/fontawesome-pro-regular": "^5.' => '"@fortawesome/pro-regular-svg-icons": "^6.',
                '"@fortawesome/fontawesome-pro-light": "^5.' => '"@fortawesome/pro-light-svg-icons": "^6.',
                '"@fortawesome/fontawesome-pro-duotone": "^5.' => '"@fortawesome/pro-duotone-svg-icons": "^6.',
                '"@fortawesome/fontawesome-pro-thin": "^5.' => '"@fortawesome/pro-thin-svg-icons": "^6.',

                // Sans version caret
                '"@fortawesome/fontawesome-pro": "5.' => '"@fortawesome/fontawesome-pro": "6.',
                '"@fortawesome/pro-solid-svg-icons": "5.' => '"@fortawesome/pro-solid-svg-icons": "6.',
                '"@fortawesome/pro-regular-svg-icons": "5.' => '"@fortawesome/pro-regular-svg-icons": "6.',
                '"@fortawesome/pro-light-svg-icons": "5.' => '"@fortawesome/pro-light-svg-icons": "6.',
                '"@fortawesome/pro-duotone-svg-icons": "5.' => '"@fortawesome/pro-duotone-svg-icons": "6.',
                '"@fortawesome/pro-thin-svg-icons": "5.' => '"@fortawesome/pro-thin-svg-icons": "6.',
            ];

            $replacements = array_merge($replacements, $proReplacements);
        }

        return $this->applyReplacements($content, $replacements);
    }

    /**
     * Appliquer un ensemble de remplacements à un contenu
     */
    protected function applyReplacements(string $content, array $replacements): string
    {
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return $content;
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
