# Font Awesome Migrator pour Laravel

🚀 Package Laravel pour automatiser la migration de Font Awesome 5 vers Font Awesome 6 (versions Free et Pro).

## 🎉 Statut du package

**✅ PRODUCTION READY** - Tous les tests passent (52 tests, 126 assertions)
- ✅ Compatible Laravel 12.0+ et PHP 8.4+
- ✅ Fonctionnellement complet et testé
- ✅ Compatible avec les environnements Docker
- ✅ Prêt pour la publication et l'utilisation en production

## Prérequis

- PHP 8.4+
- Laravel 12.0+
- Symfony Finder 8.0+

## Fonctionnalités

### 🎯 Migration des icônes
- ✅ **Migration automatique** des classes CSS FA5 → FA6
- ✅ **Support complet Pro** (Light, Duotone, Thin, Sharp)
- ✅ **Détection intelligente** des icônes dans tous types de fichiers
- ✅ **Mapping des icônes renommées** et dépréciées
- ✅ **Fallback automatique** Pro → Free si nécessaire

### 🎨 Migration des assets
- ✅ **CDN URLs** : Migration automatique des liens CDN FA5 → FA6
- ✅ **Package managers** : NPM, Yarn, pnpm (package.json)
- ✅ **Imports JavaScript** : ES6 imports, CommonJS require, dynamic imports
- ✅ **Feuilles de style** : CSS, SCSS, SASS (@import, URLs)
- ✅ **Support Pro & Free** : Détection automatique selon la licence
- ✅ **Composants Vue** : Migration complète des templates et scripts

### 🛠️ Outils
- ✅ **Sauvegarde automatique** des fichiers modifiés
- ✅ **Rapports détaillés** HTML et JSON
- ✅ **Interface web** de gestion des rapports
- ✅ **Mode dry-run** pour prévisualiser les changements
- ✅ **Modes de migration** : complet, icônes uniquement, assets uniquement

## Installation

```bash
composer require forxer/fontawesome-migrator
```

Le package sera automatiquement enregistré grâce à la découverte automatique de Laravel.

### 🚀 Installation interactive (recommandée)

```bash
php artisan fontawesome:install
```

Cette commande interactive vous guide à travers :
- **📝 Configuration personnalisée** : Licence (Free/Pro), chemins de scan, options
- **🔗 Lien symbolique** : Configuration automatique pour l'accès web aux rapports
- **✅ Vérifications** : Validation complète de l'installation
- **📋 Instructions** : Prochaines étapes et commandes utiles

### Installation manuelle

Si vous préférez configurer manuellement :

```bash
php artisan vendor:publish --tag=fontawesome-migrator-config
php artisan storage:link
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
    'report_path' => storage_path('app/public/fontawesome-migrator/reports'),
];
```

## Utilisation

### Migration complète (par défaut)

```bash
# Migration complète : icônes + assets
php artisan fontawesome:migrate
```

Cette commande migre automatiquement :
- **Classes d'icônes** : `fas fa-home` → `fa-solid fa-house`
- **CDN URLs** : `font-awesome/5.15.4` → `font-awesome/6.15.4`
- **NPM packages** : `@fortawesome/fontawesome-free-solid` → `@fortawesome/free-solid-svg-icons`
- **Imports JS** : ES6, CommonJS, dynamic imports
- **Feuilles de style** : SCSS @import, CSS URLs

### Modes de migration

```bash
# Prévisualisation sans modification (dry-run)
php artisan fontawesome:migrate --dry-run

# Migration icônes uniquement
php artisan fontawesome:migrate --icons-only

# Migration assets uniquement (CSS, JS, CDN)
php artisan fontawesome:migrate --assets-only
```

```bash
# Migration d'un dossier spécifique
php artisan fontawesome:migrate --path=resources/views
```

```bash
# Migration avec rapport détaillé
php artisan fontawesome:migrate --report --verbose
```

### Options disponibles

| Option | Description |
|--------|-------------|
| `--dry-run` | Prévisualise les changements sans les appliquer |
| `--path=` | Chemin spécifique à analyser |
| `--icons-only` | Migre uniquement les classes d'icônes |
| `--assets-only` | Migre uniquement les assets (CSS, JS, CDN) |
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

### Migration des assets

#### CDN URLs
```html
<!-- Avant -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- Après -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.15.4/css/all.min.css">
```

#### NPM Packages (package.json)
```json
// Avant
{
  "dependencies": {
    "@fortawesome/fontawesome-free": "^5.15.4",
    "@fortawesome/fontawesome-free-solid": "^5.15.4"
  }
}

// Après
{
  "dependencies": {
    "@fortawesome/fontawesome-free": "^6.15.4",
    "@fortawesome/free-solid-svg-icons": "^6.15.4"
  }
}
```

#### JavaScript Imports
```javascript
// Avant - ES6 imports
import { faHome } from "@fortawesome/fontawesome-free-solid";
const icons = require("@fortawesome/fontawesome-free-regular");

// Après
import { faHome } from "@fortawesome/free-solid-svg-icons";
const icons = require("@fortawesome/free-regular-svg-icons");
```

#### SCSS Imports
```scss
// Avant
@import "~@fortawesome/fontawesome-free/scss/fontawesome";
@import "~@fortawesome/fontawesome-free/scss/solid";

// Après (structure identique, packages mis à jour)
@import "~@fortawesome/fontawesome-free/scss/fontawesome";
@import "~@fortawesome/fontawesome-free/scss/solid";
```

#### Support Pro
```javascript
// Assets Pro FA5
import { faHome } from "@fortawesome/fontawesome-pro-solid";
const lightIcons = require("@fortawesome/fontawesome-pro-light");

// Migrés vers FA6 Pro
import { faHome } from "@fortawesome/pro-solid-svg-icons";
const lightIcons = require("@fortawesome/pro-light-svg-icons");
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

### Accès aux rapports

Les rapports sont automatiquement sauvegardés dans `storage/app/public/fontawesome-migrator/reports/` et **accessibles directement via votre navigateur** :

```bash
# Après migration, la commande affiche :
📊 Rapport généré :
   • Fichier : fontawesome-migration-report-2024-01-15_14-30-25.html
   • HTML : /storage/fontawesome-migrator/reports/fontawesome-migration-report-2024-01-15_14-30-25.html
   • JSON : /storage/fontawesome-migrator/reports/fontawesome-migration-report-2024-01-15_14-30-25.json
   • Menu : http://localhost/fontawesome-migrator/reports
```

### 🎛️ Interface de gestion des rapports

Le package inclut une **interface web complète** accessible à `/fontawesome-migrator/reports` :

- **📊 Vue d'ensemble** : Liste de tous les rapports avec métadonnées
- **🔍 Accès direct** : Liens vers HTML et JSON de chaque rapport  
- **🗑️ Gestion** : Suppression individuelle ou nettoyage automatique
- **🔄 Temps réel** : Actualisation et notifications AJAX
- **📱 Responsive** : Interface adaptée mobile et desktop

**Note :** Assurez-vous que le lien symbolique `public/storage` existe :
```bash
php artisan storage:link
```

### 🔧 Fonctionnalités de l'interface

L'interface de gestion des rapports offre :

**📊 Vue d'ensemble**
- Liste complète des rapports avec date, heure et taille
- Tri automatique par date (plus récent en premier)
- Compteur total des rapports disponibles

**🔗 Accès direct**  
- Boutons "Voir HTML" et "Voir JSON" pour chaque rapport
- Ouverture dans de nouveaux onglets pour consultation facile
- URLs directes pour partage et intégration

**🗑️ Gestion avancée**
- Suppression individuelle avec confirmation
- Nettoyage automatique des rapports anciens (30+ jours)
- Notifications temps réel des actions

**🔄 Interface dynamique**
- Actualisation AJAX sans rechargement de page
- Animations et transitions fluides
- Responsive design pour mobile et desktop

### 🎨 Design moderne

L'interface utilise un design moderne avec :
- Gradients et ombres pour un aspect professionnel
- Cards avec effets hover pour une UX intuitive
- Icons et couleurs cohérentes avec l'identité FontAwesome
- États vides informatifs quand aucun rapport n'existe

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

## Développement

### Workflow de développement

#### Environnement standard
```bash
# 1. Cloner le projet
git clone https://github.com/forxer/fontawesome-migrator.git
cd fontawesome-migrator

# 2. Installer les dépendances
composer install

# 3. Exécuter les tests
composer test

# 4. Vérifier la qualité du code
composer quality
```

#### Environnement Docker (avec d-packages-exec)

Si vous utilisez Docker avec `d-packages-exec php84` :

**⚠️ Important : Utilisez votre terminal WSL Ubuntu (pas le terminal VSCode)**

```bash
# 1. Cloner le projet
git clone https://github.com/forxer/fontawesome-migrator.git
cd fontawesome-migrator

# 2. Utiliser le script de test automatisé
./test.sh

# 3. Ou exécuter les commandes manuellement
d-packages-exec php84 composer install
d-packages-exec php84 composer test
d-packages-exec php84 composer quality
```

Le script `test.sh` effectue automatiquement :
- ✅ Installation des dépendances
- ✅ Tests unitaires complets
- ✅ Vérification du style de code
- ✅ Vérification de modernisation du code
- ✅ Test d'intégration avec Laravel
- ✅ Test des commandes Artisan

### Scripts Composer disponibles

#### Environnement standard
```bash
# Tests
composer test              # Exécuter tous les tests
composer test-coverage     # Tests avec couverture HTML

# Qualité de code
composer pint             # Formatter le code (Laravel Pint)
composer pint-test        # Vérifier le style sans corriger
composer rector           # Moderniser le code (Rector)
composer rector-dry       # Prévisualiser les modernisations
composer quality          # Contrôle qualité complet (style + rector + tests)
```

#### Environnement Docker
```bash
# Tests
d-packages-exec php84 composer test              # Exécuter tous les tests
d-packages-exec php84 composer test-coverage     # Tests avec couverture HTML

# Qualité de code
d-packages-exec php84 composer pint             # Formatter le code
d-packages-exec php84 composer pint-test        # Vérifier le style sans corriger
d-packages-exec php84 composer rector           # Moderniser le code
d-packages-exec php84 composer rector-dry       # Prévisualiser les modernisations
d-packages-exec php84 composer quality          # Contrôle qualité complet

# Script automatisé (recommandé)
./test.sh                                        # Test complet automatisé
```

### Avant de soumettre une PR

#### Environnement standard
1. **Tests** : Assurez-vous que tous les tests passent
```bash
composer test
```

2. **Style de code** : Formatez le code avec Pint
```bash
composer pint
```

3. **Modernisation** : Appliquez les améliorations Rector
```bash
composer rector
```

4. **Contrôle complet** : Exécutez le contrôle qualité global
```bash
composer quality
```

#### Environnement Docker
**Méthode simple** : Utilisez le script automatisé
```bash
./test.sh
```

**Méthode manuelle** :
```bash
d-packages-exec php84 composer test     # Tests
d-packages-exec php84 composer pint     # Style
d-packages-exec php84 composer rector   # Modernisation
d-packages-exec php84 composer quality  # Contrôle complet
```

## Contribution

Les contributions sont les bienvenues ! Veuillez :

1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/nouvelle-fonctionnalite`)
3. Développer en suivant le workflow ci-dessus
4. Commit vos changements (`git commit -am 'Ajout nouvelle fonctionnalité'`)
5. Push vers la branche (`git push origin feature/nouvelle-fonctionnalite`)
6. Créer une Pull Request

## Tests

### Tests automatisés

Le package utilise PHPUnit avec Orchestra Testbench pour les tests Laravel :

```bash
# Exécuter tous les tests
composer test

# Tests avec couverture de code HTML
composer test-coverage

# Exécuter une suite spécifique
./vendor/bin/phpunit --testsuite=Unit
./vendor/bin/phpunit --testsuite=Feature

# Test d'un fichier spécifique
./vendor/bin/phpunit tests/Unit/Services/IconMapperTest.php
```

### Structure des tests

```
tests/
├── TestCase.php                           # Classe de base avec configuration Laravel
├── Unit/                                  # Tests unitaires
│   └── Services/
│       ├── IconMapperTest.php            # Test des mappings d'icônes FA5→FA6
│       ├── StyleMapperTest.php           # Test des conversions de styles
│       └── FileScannerTest.php           # Test du scanner de fichiers
├── Feature/                              # Tests d'intégration
│   └── MigrateFontAwesomeCommandTest.php # Test complet de la commande Artisan
└── Fixtures/                             # Fichiers d'exemple pour les tests
    ├── sample-blade.php                  # Exemple Blade avec icônes FA5
    └── sample-vue.vue                    # Exemple Vue avec icônes FA5
```

### Types de tests

- **Tests unitaires** : Services individuels (IconMapper, StyleMapper, FileScanner)
- **Tests d'intégration** : Commande Artisan complète avec toutes les options
- **Tests de régression** : Validation des mappings d'icônes FA5 → FA6
- **Tests de configuration** : Validation des paramètres et gestion d'erreurs

### Couverture de code

### Résultats des tests

**🎉 TOUS LES TESTS PASSENT** (dernière exécution)
- **52 tests** exécutés avec succès
- **126 assertions** validées
- **0 erreur, 0 échec**

Les tests couvrent :
- ✅ Mappings d'icônes renommées et dépréciées
- ✅ Conversions de styles FA5 → FA6 (fas → fa-solid, etc.)
- ✅ Gestion des licences Free/Pro avec fallbacks
- ✅ Scanner de fichiers avec filtres et exclusions
- ✅ Commande Artisan (dry-run, chemins spécifiques, rapports)
- ✅ Validation de configuration et gestion d'erreurs

Les tests utilisent Orchestra Testbench pour simuler un environnement Laravel complet.

## Licence

Ce package est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## Changelog

Voir [CHANGELOG.md](CHANGELOG.md) pour l'historique des versions.

## Support

🐛 [Signaler un bug](https://github.com/forxer/fontawesome-migrator/issues)
