<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Contracts;

interface FileScannerInterface
{
    /**
     * Scanner les chemins spécifiés et retourner la liste des fichiers à traiter
     */
    public function scanPaths(array $paths, ?callable $progressCallback = null): array;

    /**
     * Analyser un fichier spécifique pour détecter les icônes Font Awesome
     */
    public function analyzeFile(string $filePath): array;
}
