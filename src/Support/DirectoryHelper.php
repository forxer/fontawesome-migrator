<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Support;

use Illuminate\Support\Facades\File;

class DirectoryHelper
{
    /**
     * S'assurer qu'un répertoire existe
     */
    public static function ensureExists(string $directory): void
    {
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }
    }

    /**
     * S'assurer qu'un fichier .gitignore existe dans le répertoire
     */
    public static function ensureGitignoreExists(string $directory, bool $isDryRun = false): void
    {
        $gitignorePath = $directory.'/.gitignore';

        $content = "# FontAwesome Migrator\n\n";
        
        // Ces fichiers sont toujours versionnés (exclus du gitignore)
        $content .= "!.gitignore\n";
        $content .= "!metadata.json\n\n";
        
        if ($isDryRun) {
            // En dry-run : ignorer les backups (pas de vraie migration)
            $content .= "# Mode dry-run : les backups sont ignorés\n";
            $content .= "backup-*\n";
        }
        // En mode réel : les backups sont versionnés pour traçabilité

        File::put($gitignorePath, $content);
    }

    /**
     * S'assurer qu'un répertoire existe avec son .gitignore
     */
    public static function ensureExistsWithGitignore(string $directory, bool $isDryRun = false): void
    {
        self::ensureExists($directory);
        self::ensureGitignoreExists($directory, $isDryRun);
    }
}
