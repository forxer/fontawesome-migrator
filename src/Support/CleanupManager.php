<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Support;

use Illuminate\Support\Facades\File;

class CleanupManager
{
    public static function cleanupOldFiles(string $directory, int $daysToKeep = 30): int
    {
        if (! File::exists($directory)) {
            return 0;
        }

        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
        $deleted = 0;

        $files = File::files($directory);

        foreach ($files as $file) {
            if ($file->getMTime() >= $cutoffTime) {
                continue;
            }

            if (File::delete($file->getPathname())) {
                $deleted++;
            }
        }

        return $deleted;
    }

    public static function cleanupOldDirectories(string $baseDirectory, string $directoryPattern, int $daysToKeep = 30): int
    {
        if (! File::exists($baseDirectory)) {
            return 0;
        }

        $cutoffTime = time() - ($daysToKeep * 24 * 60 * 60);
        $deleted = 0;

        $directories = File::directories($baseDirectory);

        foreach ($directories as $directory) {
            if (\in_array(preg_match($directoryPattern, basename((string) $directory)), [0, false], true)) {
                continue;
            }

            if (filemtime($directory) >= $cutoffTime) {
                continue;
            }

            if (File::deleteDirectory($directory)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    public static function cleanupMigrationSessions(string $migrationsPath, int $daysToKeep = 30): int
    {
        return self::cleanupOldDirectories($migrationsPath, '/^migration-/', $daysToKeep);
    }

    public static function cleanupByFileCount(string $directory, int $maxFiles, string $sortBy = 'mtime'): int
    {
        if (! File::exists($directory)) {
            return 0;
        }

        $files = collect(File::files($directory))
            ->map(fn ($file): array => [
                'path' => $file->getPathname(),
                'mtime' => $file->getMTime(),
                'size' => $file->getSize(),
            ]);

        if ($files->count() <= $maxFiles) {
            return 0;
        }

        $sortDirection = $sortBy === 'mtime' ? 'desc' : 'asc';
        $filesToDelete = $files
            ->sortBy($sortBy, SORT_REGULAR, $sortDirection === 'desc')
            ->skip($maxFiles)
            ->pluck('path');

        $deleted = 0;

        foreach ($filesToDelete as $filePath) {
            if (File::delete($filePath)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    public static function getDirectorySize(string $directory): int
    {
        if (! File::exists($directory)) {
            return 0;
        }

        $totalSize = 0;
        $files = File::allFiles($directory);

        foreach ($files as $file) {
            $totalSize += $file->getSize();
        }

        return $totalSize;
    }

    public static function cleanupByDirectorySize(string $directory, int $maxSizeBytes): int
    {
        $currentSize = self::getDirectorySize($directory);

        if ($currentSize <= $maxSizeBytes) {
            return 0;
        }

        $files = collect(File::files($directory))
            ->sortBy(fn ($file) => $file->getMTime());

        $deleted = 0;
        $sizeFreed = 0;

        foreach ($files as $file) {
            if ($currentSize - $sizeFreed <= $maxSizeBytes) {
                break;
            }

            $fileSize = $file->getSize();

            if (File::delete($file->getPathname())) {
                $deleted++;
                $sizeFreed += $fileSize;
            }
        }

        return $deleted;
    }
}
