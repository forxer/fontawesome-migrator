# Font Awesome Migrator pour Laravel

🚀 Package Laravel pour automatiser la migration de Font Awesome 5 vers Font Awesome 6 (versions Free et Pro).

## Fonctionnalités

- ✅ **Migration automatique** des classes CSS FA5 → FA6
- ✅ **Support complet Pro** (Light, Duotone, Thin, Sharp)
- ✅ **Détection intelligente** des icônes dans tous types de fichiers
- ✅ **Mapping des icônes renommées** et dépréciées
- ✅ **Sauvegarde automatique** des fichiers modifiés
- ✅ **Rapports détaillés** HTML et JSON
- ✅ **Mode dry-run** pour prévisualiser les changements
- ✅ **Fallback automatique** Pro → Free si nécessaire

## Installation

```bash
composer require forxer/fontawesome-migrator
```

Le package sera automatiquement enregistré grâce à la découverte automatique de Laravel.

### Publication des fichiers de configuration

```bash
php artisan vendor:publish --tag=fontawesome-migrator-config
```

## Configuration

Éditez `config/fontawesome-migrator.php` :

```php
return [
    // Type de licence: 'free' ou 'pro'
    'license_type' => env('FONTAWESOME_LICENSE', 'free'),

    // Styles Font Awesome Pro disponibles
    'pro_styles' => [
        'light' => true,
        'duotone' => true,
        'thin' => false,    // Nouveau FA6 Pro
        'sharp' => false,   // Nouveau FA6 Pro
    ],

    // Style de fallback si Pro non disponible
    'fallback_strategy' => 'solid',

    // Chemins à analyser
    'scan_paths' => [
        'resources/views',
        'resources/js',
        'resources/css',
        'public/css',
        'public/js',
    ],

    // Extensions de fichiers supportées
    'file_extensions' => [
        'blade.php', 'php', 'html', 'vue', 'js', 'ts',
        'css', 'scss', 'sass', 'less',
    ],

    // Sauvegarde automatique
    'backup_files' => true,
    'backup_path' => storage_path('fontawesome-migrator/backups'),

    // Génération de rapports
    'generate_report' => true,
    'report_path' => storage_path('fontawesome-migrator/reports'),
];
```

## Utilisation

### Migration complète

```bash
# Migration de tous les fichiers
php artisan fontawesome:migrate

# Prévisualisation sans modification (dry-run)
php artisan fontawesome:migrate --dry-run

# Migration d'un dossier spécifique
php artisan fontawesome:migrate --path=resources/views

# Migration avec rapport détaillé
php artisan fontawesome:migrate --report --verbose
```

### Options disponibles

| Option | Description |
|--------|-------------|
| `--dry-run` | Prévisualise les changements sans les appliquer |
| `--path=` | Chemin spécifique à analyser |
| `--backup` | Force la création de sauvegardes |
| `--no-backup` | Désactive les sauvegardes |
| `--verbose` | Mode verbeux avec détails |
| `--report` | Génère un rapport détaillé |

## Exemples de conversions

### Changements de style

```html
<!-- Font Awesome 5 -->
<i class="fas fa-home"></i>
<i class="far fa-user"></i>
<i class="fal fa-star"></i>
<i class="fad fa-heart"></i>

<!-- Font Awesome 6 -->
<i class="fa-solid fa-house"></i>
<i class="fa-regular fa-user"></i>
<i class="fa-light fa-star"></i>
<i class="fa-duotone fa-heart"></i>
```

### Icônes renommées

```html
<!-- Font Awesome 5 -->
<i class="fas fa-external-link"></i>
<i class="fas fa-times"></i>
<i class="fas fa-trash-o"></i>

<!-- Font Awesome 6 -->
<i class="fa-solid fa-external-link-alt"></i>
<i class="fa-solid fa-xmark"></i>
<i class="fa-solid fa-trash-can"></i>
```

### Support des composants Vue/React

```vue
<!-- Avant -->
<font-awesome-icon icon="fas fa-user" />

<!-- Après -->
<font-awesome-icon icon="fa-solid fa-user" />
```

## Migration Pro vers Free

Si vous migrez d'une licence Pro vers Free, le package :

1. **Détecte automatiquement** les icônes Pro uniquement
2. **Propose des alternatives** gratuites quand disponible
3. **Applique le fallback** configuré pour les styles Pro
4. **Génère des avertissements** pour les icônes nécessitant une intervention manuelle

```bash
# Exemple de migration Pro → Free
FONTAWESOME_LICENSE=free php artisan fontawesome:migrate --dry-run
```

## Rapports

Le package génère automatiquement des rapports détaillés :

### Rapport HTML
- Vue d'ensemble visuelle des changements
- Statistiques détaillées par type
- Liste des fichiers modifiés
- Avertissements et recommandations

### Rapport JSON
- Format programmable pour l'automatisation
- Métadonnées de migration
- Détails techniques complets

Les rapports sont sauvegardés dans `storage/fontawesome-migrator/reports/`.

## Sauvegardes

Avant chaque modification, le package peut créer une sauvegarde :

```php
// Dans config/fontawesome-migrator.php
'backup_files' => true,
'backup_path' => storage_path('fontawesome-migrator/backups'),
```

### Restauration depuis sauvegarde

```bash
# Les sauvegardes sont organisées par timestamp
ls storage/fontawesome-migrator/backups/resources/views/
# -> welcome.blade.php.backup.2024-01-15_14-30-25
```

## Support des fichiers

Le package analyse intelligemment :

- **Templates Blade** (`*.blade.php`)
- **Composants Vue** (`*.vue`)
- **JavaScript/TypeScript** (`*.js`, `*.ts`)
- **Feuilles de style** (`*.css`, `*.scss`, `*.sass`, `*.less`)
- **Fichiers HTML** (`*.html`)

## Icônes supportées

### Icônes renommées (exemples)
- `fa-external-link` → `fa-external-link-alt`
- `fa-times` → `fa-xmark`
- `fa-home` → `fa-house`
- `fa-trash-o` → `fa-trash-can`

### Icônes dépréciées
- `fa-glass` → `fa-martini-glass-empty`
- `fa-star-o` → `fa-star`
- `fa-close` → `fa-xmark`

### Nouveaux styles FA6 Pro
- `fa-thin` (ultra-fin)
- `fa-sharp` (angles nets)

## Dépannage

### Icônes non reconnues

Si une icône n'est pas dans les mappings :

1. Vérifiez la [documentation Font Awesome 6](https://fontawesome.com/search)
2. Consultez le rapport généré pour les suggestions
3. Ajoutez manuellement les mappings personnalisés

### Problèmes de performance

Pour les gros projets :

```bash
# Analyser un dossier à la fois
php artisan fontawesome:migrate --path=resources/views
php artisan fontawesome:migrate --path=resources/js
```

### Exclusion de fichiers

Modifiez `exclude_patterns` dans la configuration :

```php
'exclude_patterns' => [
    'node_modules',
    'vendor',
    '*.min.js',
    '*.min.css',
    'my-custom-exclude-pattern'
],
```

## Contribution

Les contributions sont les bienvenues ! Veuillez :

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Commit vos changements (`git commit -am 'Ajout nouvelle fonctionnalité'`)
4. Push vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
5. Créer une Pull Request

## Tests

```bash
composer test
```

## Licence

Ce package est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## Changelog

Voir [CHANGELOG.md](CHANGELOG.md) pour l'historique des versions.

## Support

- 📖 [Documentation complète](https://github.com/forxer/fontawesome-migrator/wiki)
- 🐛 [Signaler un bug](https://github.com/forxer/fontawesome-migrator/issues)
- 💬 [Discussions](https://github.com/forxer/fontawesome-migrator/discussions)