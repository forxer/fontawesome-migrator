<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Support;

use Illuminate\Support\Facades\File;
use InvalidArgumentException;

class JsonFileHelper
{
    public static function loadJson(string $filePath, ?array $default = null): array
    {
        if (! File::exists($filePath)) {
            if ($default !== null) {
                return $default;
            }

            throw new InvalidArgumentException('JSON file not found: '.$filePath);
        }

        $content = File::get($filePath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Invalid JSON in file "'.$filePath.'": '.json_last_error_msg());
        }

        return $data;
    }

    public static function saveJson(string $filePath, array $data, bool $createDirectory = true): bool
    {
        if ($createDirectory) {
            $directory = \dirname($filePath);

            if (! File::exists($directory)) {
                File::makeDirectory($directory, 0755, true);
            }
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException('Failed to encode data to JSON: '.json_last_error_msg());
        }

        return File::put($filePath, $json) !== false;
    }

    public static function existsAndValid(string $filePath): bool
    {
        try {
            self::loadJson($filePath);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }
}
