<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services\Core;

use FontAwesome\Migrator\Contracts\ConfigurationInterface;
use FontAwesome\Migrator\Services\Configuration\AssetReplacementService;

class AssetMigrator
{
    public function __construct(
        protected ConfigurationInterface $config,
        protected AssetReplacementService $replacementService,
    ) {}

    /**
     * Configurer la version cible pour la migration
     */
    public function setTargetVersion(string $targetVersion): self
    {
        $this->replacementService->setTargetVersion($targetVersion);

        return $this;
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
     * Migrer les références dans package.json et autres gestionnaires de packages
     */
    protected function migratePackageJsonAssets(string $content): string
    {
        $replacements = $this->replacementService->getPackageJsonReplacements();

        return $this->replacementService->applyReplacements($content, $replacements);
    }
}
