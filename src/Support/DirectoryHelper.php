<?php

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
    public static function ensureGitignoreExists(string $directory): void
    {
        $gitignorePath = $directory.'/.gitignore';

        if (! File::exists($gitignorePath)) {
            $content = "# FontAwesome Migrator - Fichiers générés automatiquement\n";
            $content .= "# Ces fichiers ne doivent pas être versionnés\n";
            $content .= "*\n";
            $content .= "!.gitignore\n";

            File::put($gitignorePath, $content);
        }
    }

    /**
     * S'assurer qu'un répertoire existe avec son .gitignore
     */
    public static function ensureExistsWithGitignore(string $directory): void
    {
        self::ensureExists($directory);
        self::ensureGitignoreExists($directory);
    }
}
