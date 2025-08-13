<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Configuration;

use Exception;
use FontAwesome\Migrator\Support\JsonFileHelper;

/**
 * Service centralisé pour la gestion des remplacements d'assets FontAwesome
 * Évite la duplication massive dans AssetMigrator
 */
class AssetReplacementService
{
    private array $replacements = [];

    private array $excludedPatterns = [];

    private string $targetVersion = '6'; // Version par défaut

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
            // Utiliser config_path() pour Laravel ou chemin relatif au package
            $configPath = config_path('fontawesome-migrator/assets/replacements.json');

            if (! file_exists($configPath)) {
                // Fallback pour le développement du package
                $configPath = __DIR__.'/../../../config/fontawesome-migrator/assets/replacements.json';
            }

            $config = JsonFileHelper::loadJson($configPath);

            $this->replacements = $config['replacements'] ?? [];
            $this->excludedPatterns = $config['excluded_patterns'] ?? [];

        } catch (Exception) {
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
     * Filtrer les patterns exclus (remplacements identiques)
     */
    private function filterExcludedPatterns(array $replacements): array
    {
        return array_filter($replacements, function ($replacement, $pattern): bool {
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
            // Vérifier si c'est un pattern regex (commence par /)
            if (str_starts_with($search, '/') && str_ends_with($search, '/')) {
                // Remplacer {target_version} par la version cible configurée
                $processedReplace = str_replace('{target_version}', $this->getTargetVersion(), $replace);
                $content = preg_replace($search, $processedReplace, (string) $content);
            } else {
                // Utiliser str_replace classique pour les patterns exacts
                $content = str_replace($search, $replace, $content);
            }
        }

        return $content;
    }

    /**
     * Définir la version cible pour la migration
     */
    public function setTargetVersion(string $targetVersion): self
    {
        $this->targetVersion = $targetVersion;

        return $this;
    }

    /**
     * Obtenir la version cible
     */
    protected function getTargetVersion(): string
    {
        return $this->targetVersion;
    }
}
