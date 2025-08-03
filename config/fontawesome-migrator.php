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
    | Styles Font Awesome Pro
    |--------------------------------------------------------------------------
    |
    | Configuration des styles disponibles avec Font Awesome Pro
    | Uniquement utilisé si license_type = 'pro'
    |
    */

    'pro_styles' => [
        'light' => true,
        'duotone' => true,
        'thin' => false,
        'sharp' => false,
    ],

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
    | Sauvegarde automatique
    |--------------------------------------------------------------------------
    |
    | Créer une sauvegarde des fichiers avant modification
    |
    */

    'backup_files' => true,

    /*
    |--------------------------------------------------------------------------
    | Répertoire des sessions de migration
    |--------------------------------------------------------------------------
    |
    | Où stocker les sessions de migration (rapports, métadonnées, sauvegardes)
    |
    */

    'sessions_path' => storage_path('app/fontawesome-migrator'),

    /*
    |--------------------------------------------------------------------------
    | Mode verbeux
    |--------------------------------------------------------------------------
    |
    | Afficher des informations détaillées pendant la migration
    |
    */

    'verbose' => false,

    /*
    |--------------------------------------------------------------------------
    | Chemin du rapport
    |--------------------------------------------------------------------------
    |
    | Où sauvegarder le rapport de migration
    | Par défaut dans storage/app/public pour accès web via Storage::url()
    |
    */

    'report_path' => storage_path('app/public/fontawesome-migrator/reports'),
];
