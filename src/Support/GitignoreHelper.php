<?php

namespace FontAwesome\Migrator\Support;

use Illuminate\Support\Facades\File;

class GitignoreHelper
{
    /**
     * S'assurer qu'un répertoire existe avec son .gitignore
     */
    public static function ensureDirectoryWithGitignore(string $directory): void
    {
        // Créer le répertoire s'il n'existe pas
        if (! File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        // S'assurer que le .gitignore existe
        self::ensureExists($directory);
    }

    /**
     * S'assurer qu'un fichier .gitignore existe dans le répertoire
     */
    public static function ensureExists(string $directory): void
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
}
