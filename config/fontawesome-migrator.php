<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Type de licence Font Awesome
    |--------------------------------------------------------------------------
    |
    | Définit si vous utilisez la version gratuite ou Pro de Font Awesome
    | Options: 'free', 'pro'
    |
    */

    'license_type' => env('FONTAWESOME_LICENSE', 'free'),

    /*
    |--------------------------------------------------------------------------
    | Stratégie de fallback
    |--------------------------------------------------------------------------
    |
    | Style de fallback à utiliser si un style Pro n'est pas disponible
    | lors de la migration vers la version gratuite
    |
    */

    'fallback_strategy' => 'solid',

    /*
    |--------------------------------------------------------------------------
    | Migrations multi-versions
    |--------------------------------------------------------------------------
    |
    | Configuration pour les migrations entre différentes versions FontAwesome
    | Supporté : 4→5, 5→6, 6→7, et migrations chaînées (ex: 4→5→6→7)
    |
    */

    'auto_detect_version' => true,

    'supported_versions' => [
        'from' => [4, 5, 6],
        'to' => [5, 6, 7],
    ],

    /*
    |--------------------------------------------------------------------------
    | Chemins à scanner
    |--------------------------------------------------------------------------
    |
    | Répertoires et fichiers à analyser pour la migration.
    | Peut contenir des répertoires ou des fichiers individuels.
    |
    | Exemples:
    | - 'resources/views' (répertoire)
    | - 'resources/views/layouts/app.blade.php' (fichier spécifique)
    |
    */

    'scan_paths' => [
        'resources/views',
        'resources/js',
        'resources/css',
        'resources/scss',
        'resources/sass',
        'public/css',
        'public/js',
        // Exemples de fichiers spécifiques :
        // 'resources/views/layouts/app.blade.php',
        // 'public/js/custom-icons.js',
        // 'package.json',
        // 'webpack.mix.js'
    ],

    /*
    |--------------------------------------------------------------------------
    | Fichiers à exclure
    |--------------------------------------------------------------------------
    |
    | Patterns de fichiers/dossiers à ignorer lors du scan
    |
    */

    'exclude_patterns' => [
        'node_modules',
        'vendor',
        '.git',
        'storage',
        'bootstrap/cache',
        '*.min.js',
        '*.min.css',
    ],

    /*
    |--------------------------------------------------------------------------
    | Extensions de fichiers
    |--------------------------------------------------------------------------
    |
    | Types de fichiers à analyser lors du scan
    |
    */

    'file_extensions' => [
        'blade.php',
        'css',
        'html',
        'js',
        'json',
        'less',
        'php',
        'sass',
        'scss',
        'ts',
        'vue',
    ],

    /*
    |--------------------------------------------------------------------------
    | Sauvegarde automatique
    |--------------------------------------------------------------------------
    |
    | Créer une sauvegarde des fichiers avant modification
    |
    */

    'backup_files' => true,

    /*
    |--------------------------------------------------------------------------
    | Répertoire des migrations
    |--------------------------------------------------------------------------
    |
    | Où stocker les migrations (métadonnées, sauvegardes)
    |
    */

    'migrations_path' => storage_path('app/fontawesome-migrator'),

    /*
    |--------------------------------------------------------------------------
    | Mode verbeux
    |--------------------------------------------------------------------------
    |
    | Afficher des informations détaillées pendant la migration
    |
    */

    'verbose' => false,

];
