<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Support;

class StatisticsCalculator
{
    public static function calculateMigrationStats(array $results): array
    {
        $stats = [
            'total_files' => \count($results),
            'modified_files' => 0,
            'total_changes' => 0,
            'warnings' => 0,
            'errors' => 0,
            'changes_by_type' => [],
            'files_with_errors' => [],
        ];

        foreach ($results as $result) {
            if (! empty($result['changes'])) {
                $stats['modified_files']++;
                $stats['total_changes'] += \count($result['changes']);

                foreach ($result['changes'] as $change) {
                    $type = $change['type'] ?? 'unknown';
                    $stats['changes_by_type'][$type] = ($stats['changes_by_type'][$type] ?? 0) + 1;
                }
            }

            if (! empty($result['warnings'])) {
                $stats['warnings'] += \count($result['warnings']);
            }

            if (! empty($result['error']) || (isset($result['success']) && ! $result['success'])) {
                $stats['errors']++;
                $stats['files_with_errors'][] = $result['file'] ?? 'unknown';
            }
        }

        return $stats;
    }

    public static function calculateFileStats(array $files): array
    {
        $stats = [
            'total_files' => \count($files),
            'by_extension' => [],
            'total_size' => 0,
        ];

        foreach ($files as $file) {
            $extension = pathinfo((string) ($file['path'] ?? $file), PATHINFO_EXTENSION);
            $stats['by_extension'][$extension] = ($stats['by_extension'][$extension] ?? 0) + 1;

            if (isset($file['size'])) {
                $stats['total_size'] += $file['size'];
            }
        }

        return $stats;
    }

    public static function calculateAssetStats(array $files, callable $assetAnalyzer): array
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
            $analysis = $assetAnalyzer($file);

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

    public static function calculateBackupStats(array $backups): array
    {
        $stats = [
            'total_backups' => \count($backups),
            'total_size' => 0,
            'by_date' => [],
        ];

        foreach ($backups as $backup) {
            $stats['total_size'] += $backup['size'] ?? 0;

            $date = isset($backup['created_at']) ? date('Y-m-d', strtotime((string) $backup['created_at'])) : 'unknown';
            $stats['by_date'][$date] = ($stats['by_date'][$date] ?? 0) + 1;
        }

        return $stats;
    }

    public static function mergeStats(array ...$statArrays): array
    {
        $merged = [];

        foreach ($statArrays as $stats) {
            foreach ($stats as $key => $value) {
                if (\is_array($value) && isset($merged[$key]) && \is_array($merged[$key])) {
                    $merged[$key] = array_merge_recursive($merged[$key], $value);
                } elseif (is_numeric($value) && isset($merged[$key]) && is_numeric($merged[$key])) {
                    $merged[$key] += $value;
                } else {
                    $merged[$key] = $value;
                }
            }
        }

        return $merged;
    }
}
