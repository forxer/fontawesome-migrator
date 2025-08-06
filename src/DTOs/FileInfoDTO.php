<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\DTOs;

/**
 * DTO pour les informations de fichier
 * Simplifie la structure des fichiers scannés
 */
readonly class FileInfoDTO
{
    public function __construct(
        public string $path,
        public string $relativePath,
        public string $extension,
        public int $size
    ) {}

    /**
     * Créer depuis un array de données de FileScanner
     */
    public static function fromArray(array $data): self
    {
        return new self(
            path: $data['path'] ?? '',
            relativePath: $data['relative_path'] ?? '',
            extension: $data['extension'] ?? '',
            size: $data['size'] ?? 0
        );
    }

    /**
     * Convertir en array (format compatible avec FileScanner)
     */
    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'relative_path' => $this->relativePath,
            'extension' => $this->extension,
            'size' => $this->size,
        ];
    }

    /**
     * Obtenir le nom du fichier
     */
    public function getFilename(): string
    {
        return basename($this->path);
    }

    /**
     * Vérifier si l'extension est supportée
     */
    public function isSupportedExtension(array $supportedExtensions): bool
    {
        return \in_array($this->extension, $supportedExtensions);
    }

    /**
     * Formater la taille en format lisible
     */
    public function getFormattedSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->size;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < \count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2).' '.$units[$unitIndex];
    }
}
