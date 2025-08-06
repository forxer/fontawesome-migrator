<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Support\Traits;

use Illuminate\Support\Facades\File;

trait FileValidator
{
    protected function validateFileExists(string $filePath, string $context = 'File'): ?array
    {
        if (! File::exists($filePath)) {
            return [
                'success' => false,
                'error' => $context.' not found: '.$filePath,
                'changes' => [],
                'warnings' => [],
            ];
        }

        return null;
    }

    protected function validateFileReadable(string $filePath, string $context = 'File'): ?array
    {
        if (! File::exists($filePath)) {
            return $this->validateFileExists($filePath, $context);
        }

        if (! is_readable($filePath)) {
            return [
                'success' => false,
                'error' => $context.' is not readable: '.$filePath,
                'changes' => [],
                'warnings' => [],
            ];
        }

        return null;
    }

    protected function validateFileWritable(string $filePath, string $context = 'File'): ?array
    {
        if (File::exists($filePath) && ! is_writable($filePath)) {
            return [
                'success' => false,
                'error' => $context.' is not writable: '.$filePath,
                'changes' => [],
                'warnings' => [],
            ];
        }

        $directory = \dirname($filePath);

        if (! File::exists($directory)) {
            return [
                'success' => false,
                'error' => 'Directory does not exist for '.$context.': '.$directory,
                'changes' => [],
                'warnings' => [],
            ];
        }

        if (! is_writable($directory)) {
            return [
                'success' => false,
                'error' => 'Directory is not writable for '.$context.': '.$directory,
                'changes' => [],
                'warnings' => [],
            ];
        }

        return null;
    }

    protected function validateFileExtension(string $filePath, array $allowedExtensions, string $context = 'File'): ?array
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        if (! \in_array($extension, $allowedExtensions, true)) {
            return [
                'success' => false,
                'error' => $context.' has unsupported extension "'.$extension.'". Allowed: '.implode(', ', $allowedExtensions),
                'changes' => [],
                'warnings' => [],
            ];
        }

        return null;
    }

    protected function createErrorResponse(string $error, string $filePath = ''): array
    {
        return [
            'success' => false,
            'error' => $error,
            'file' => $filePath,
            'changes' => [],
            'warnings' => [],
        ];
    }

    protected function createSuccessResponse(string $filePath = '', array $changes = [], array $warnings = []): array
    {
        return [
            'success' => true,
            'file' => $filePath,
            'changes' => $changes,
            'warnings' => $warnings,
        ];
    }
}
