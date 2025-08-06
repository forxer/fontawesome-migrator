<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services;

use Exception;
use Illuminate\Support\Facades\File;

/**
 * Service centralisé pour la gestion des remplacements d'assets FontAwesome
 * Évite la duplication massive dans AssetMigrator
 */
class AssetReplacementService
{
    private array $replacements = [];

    private array $excludedPatterns = [];

    public function __construct()
    {
        $this->loadReplacements();
    }

    /**
     * Charger les remplacements depuis la configuration JSON
     */
    private function loadReplacements(): void
    {
        try {
            $configPath = __DIR__.'/../../config/fontawesome-migrator/assets/replacements.json';

            if (! File::exists($configPath)) {
                throw new Exception("Configuration des remplacements d'assets non trouvée: {$configPath}");
            }

            $config = json_decode(File::get($configPath), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Configuration JSON invalide: '.json_last_error_msg());
            }

            $this->replacements = $config['replacements'] ?? [];
            $this->excludedPatterns = $config['excluded_patterns'] ?? [];

        } catch (Exception $e) {
            // En cas d'erreur, utiliser des remplacements vides pour éviter les crashes
            $this->replacements = [];
            $this->excludedPatterns = [];
        }
    }

    /**
     * Obtenir les remplacements pour les feuilles de style
     */
    public function getStylesheetReplacements(bool $isPro = false): array
    {
        $replacements = [];

        // Ajouter les remplacements Free
        if (isset($this->replacements['stylesheets']['free'])) {
            foreach ($this->replacements['stylesheets']['free'] as $category) {
                $replacements = array_merge($replacements, $category);
            }
        }

        // Ajouter les remplacements Pro si nécessaire
        if ($isPro && isset($this->replacements['stylesheets']['pro'])) {
            foreach ($this->replacements['stylesheets']['pro'] as $category) {
                $replacements = array_merge($replacements, $category);
            }
        }

        return $this->filterExcludedPatterns($replacements);
    }

    /**
     * Obtenir les remplacements pour JavaScript
     */
    public function getJavaScriptReplacements(bool $isPro = false): array
    {
        $replacements = [];

        // Ajouter les remplacements Free
        if (isset($this->replacements['javascript']['free'])) {
            foreach ($this->replacements['javascript']['free'] as $category) {
                $replacements = array_merge($replacements, $category);
            }
        }

        // Ajouter les remplacements Pro si nécessaire
        if ($isPro && isset($this->replacements['javascript']['pro'])) {
            foreach ($this->replacements['javascript']['pro'] as $category) {
                $replacements = array_merge($replacements, $category);
            }
        }

        return $this->filterExcludedPatterns($replacements);
    }

    /**
     * Obtenir les remplacements pour HTML
     */
    public function getHtmlReplacements(bool $isPro = false): array
    {
        $replacements = [];

        // Ajouter les remplacements Free
        if (isset($this->replacements['html']['free'])) {
            foreach ($this->replacements['html']['free'] as $category) {
                $replacements = array_merge($replacements, $category);
            }
        }

        // Ajouter les remplacements Pro si nécessaire
        if ($isPro && isset($this->replacements['html']['pro'])) {
            foreach ($this->replacements['html']['pro'] as $category) {
                $replacements = array_merge($replacements, $category);
            }
        }

        return $this->filterExcludedPatterns($replacements);
    }

    /**
     * Obtenir les remplacements pour package.json
     */
    public function getPackageJsonReplacements(): array
    {
        return $this->replacements['package_json']['dependencies'] ?? [];
    }

    /**
     * Obtenir les remplacements pour Vue.js
     */
    public function getVueReplacements(bool $isPro = false): array
    {
        // Vue combine JavaScript et HTML, on merge les deux
        return array_merge(
            $this->getJavaScriptReplacements($isPro),
            $this->getHtmlReplacements($isPro)
        );
    }

    /**
     * Filtrer les patterns exclus (remplacements identiques)
     */
    private function filterExcludedPatterns(array $replacements): array
    {
        return array_filter($replacements, function ($replacement, $pattern) {
            // Exclure si le pattern est dans la liste d'exclusion
            if (\in_array($pattern, $this->excludedPatterns)) {
                return false;
            }

            // Exclure si le remplacement est identique à l'original
            return $pattern !== $replacement;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Appliquer les remplacements sur un contenu
     */
    public function applyReplacements(string $content, array $replacements): string
    {
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }

    /**
     * Obtenir les statistiques des remplacements chargés
     */
    public function getReplacementStats(): array
    {
        $stats = [
            'total_categories' => 0,
            'total_replacements' => 0,
            'excluded_patterns' => \count($this->excludedPatterns),
            'by_type' => [],
        ];

        foreach ($this->replacements as $type => $categories) {
            $typeTotal = 0;

            if ($type === 'package_json') {
                $typeTotal = \count($categories['dependencies'] ?? []);
            } else {
                foreach ($categories as $license => $licenseCategories) {
                    foreach ($licenseCategories as $category => $patterns) {
                        $typeTotal += \count($patterns);
                    }
                }
            }

            $stats['by_type'][$type] = $typeTotal;
            $stats['total_replacements'] += $typeTotal;
        }

        $stats['total_categories'] = \count($this->replacements);

        return $stats;
    }

    /**
     * Vérifier si les remplacements sont chargés correctement
     */
    public function isConfigurationLoaded(): bool
    {
        return ! empty($this->replacements);
    }

    /**
     * Obtenir la liste des patterns exclus pour debug
     */
    public function getExcludedPatterns(): array
    {
        return $this->excludedPatterns;
    }
}
