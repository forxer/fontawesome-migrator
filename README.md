# Font Awesome Migrator pour Laravel

> ⚠️ **Disclaimer**: Ce code a été largement généré avec Claude Code (claude.ai/code) à des fins d'apprentissage et pédagogiques. Bien que fonctionnel, **je n'ai pas entièrement confiance en l'IA** - j'ai dû corriger de nombreuses erreurs et imprécisions tout au long du développement. Utilisez ce package avec prudence et testez soigneusement avant usage en production.

🚀 **Package Laravel professionnel** pour automatiser la migration FontAwesome multi-versions (4 → 5 → 6 → 7) avec architecture moderne et interface web complète.

**🎉 Version 2.0.0** : Architecture multi-versions avec configuration JSON externalisée.

## Prérequis

- PHP 8.4+
- Laravel 12.0+
- Symfony Finder 8.0+

## Fonctionnalités

### 🎯 Migration multi-versions des icônes
- ✅ **Architecture multi-versions** : FA4 → FA5 → FA6 → FA7 avec MigrationVersionManager
- ✅ **Détection automatique** de la version source dans votre code
- ✅ **Support complet Pro** (Light, Duotone, Thin, Sharp) avec fallbacks Free
- ✅ **Configuration JSON** : Mappings externalisés avec ConfigurationLoader et cache
- ✅ **Mappers spécialisés** : FontAwesome4To5Mapper, FontAwesome5To6Mapper, FontAwesome6To7Mapper
- ✅ **Interface web interactive** avec sélecteur de versions temps réel
- ✅ **Mapping intelligent** des icônes renommées et dépréciées avec fallbacks

### 🎨 Migration des assets
- ✅ **CDN URLs** : Migration automatique des liens CDN (toutes versions)
- ✅ **Package managers** : NPM, Yarn, pnpm (package.json avec extension .json)
- ✅ **Build tools** : webpack.mix.js avec support des fichiers JS individuels
- ✅ **Imports JavaScript** : ES6 imports, CommonJS require, dynamic imports
- ✅ **Feuilles de style** : CSS, SCSS, SASS (@import, URLs)
- ✅ **Support Pro & Free** : Détection automatique selon la licence
- ✅ **Composants Vue/React** : Migration complète des templates et scripts

### 🛠️ Interface et outils avancés
- ✅ **Configurateur multi-versions** : Interface web `/tests` avec sélecteur interactif FA4→5→6→7
- ✅ **Validation dynamique** : Vérification des migrations supportées en temps réel
- ✅ **Aperçu de compatibilité** : Breaking changes et recommandations par migration
- ✅ **Interface web complète** : Dashboard, rapports, migrations avec navigation moderne
- ✅ **Documentation complète** : Guide multi-versions, API reference, Quick reference
- ✅ **Architecture JSON** : Configuration externalisée avec ConfigurationLoader et fallbacks
- ✅ **Mode dry-run** : Prévisualisation des changements avant application
- ✅ **Rapports détaillés** : HTML interactifs et JSON avec métadonnées complètes
- ✅ **Traçabilité complète** : Origine CLI/Web, migrations avec short IDs
- ✅ **Design system Bootstrap 5** : Interface moderne, responsive et accessible

## Installation

```bash
composer require forxer/fontawesome-migrator --dev
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
- **⚡ Configuration optimisée** : Seules les valeurs modifiées sont sauvegardées

### Installation manuelle

Si vous préférez configurer manuellement :

```bash
php artisan vendor:publish --tag=fontawesome-migrator-config
php artisan storage:link
```

## Configuration

### 🎯 Configuration optimisée

Le package utilise un système de configuration intelligent : **seules les valeurs que vous modifiez sont sauvegardées** dans `config/fontawesome-migrator.php`. Cela simplifie la maintenance et les mises à jour.

**Exemple de fichier généré après installation interactive :**
```php
<?php

return [
    /*
    | Ce fichier contient uniquement les paramètres personnalisés.
    | Les valeurs par défaut sont définies dans le package.
    */

    'license_type' => 'pro',
    'scan_paths' => [
        'resources/views',
        'resources/js',
        'resources/css',
        'public/css',
        'public/js',
        'custom/path'  // Chemin ajouté
    ],
    'auto_detect_version' => true,
    'supported_versions' => [
        'from' => [4, 5, 6],
        'to' => [5, 6, 7]
    ]
];
```

### 📋 Configuration complète

Pour voir **toutes les options disponibles**, publiez la configuration complète :

```bash
php artisan vendor:publish --tag=fontawesome-migrator-config-full
```

Cela créera `config/fontawesome-migrator-full.php` avec toutes les options documentées :

```php
return [
    // Type de licence: 'free' ou 'pro'
    'license_type' => env('FONTAWESOME_LICENSE', 'free'),

    // Configuration multi-versions
    'auto_detect_version' => true,
    'supported_versions' => [
        'from' => [4, 5, 6],
        'to' => [5, 6, 7]
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

];
```

## Utilisation

### 🚀 Migration automatique (recommandée)

```bash
# Mode interactif avec détection automatique de version
php artisan fontawesome:migrate

# Mode classique (non-interactif)
php artisan fontawesome:migrate --no-interactive
```

### 🎯 Migrations spécifiques par version

```bash
# Migration FA4 → FA5 (révolution des préfixes)
php artisan fontawesome:migrate --from=4 --to=5

# Migration FA5 → FA6 (modernisation des noms)
php artisan fontawesome:migrate --from=5 --to=6

# Migration FA6 → FA7 (optimisations comportementales)
php artisan fontawesome:migrate --from=6 --to=7

# Aperçu des changements (dry-run)
php artisan fontawesome:migrate --from=5 --to=6 --dry-run
```

**Le mode interactif** détecte automatiquement votre version FontAwesome et vous guide :
- **🎯 Détection automatique** : FA4, FA5, FA6 ou FA7 dans votre code
- **📋 Sélection du type** : Complète, icônes uniquement, assets uniquement
- **👁️ Mode prévisualisation** : Dry-run avec confirmation
- **📂 Chemins personnalisés** : Analyse de dossiers spécifiques
- **📊 Génération de rapports** : Rapports automatiques dans les métadonnées
- **💾 Configuration des sauvegardes** : Forcées, désactivées ou par défaut

### 🎛️ Modes de migration

```bash
# Prévisualisation sans modification (dry-run)
php artisan fontawesome:migrate --dry-run

# Migration icônes uniquement
php artisan fontawesome:migrate --icons-only

# Migration assets uniquement (CSS, JS, CDN)
php artisan fontawesome:migrate --assets-only
```

### 🔧 Gestion avancée de la configuration

```bash
# Commande de configuration interactive
php artisan fontawesome:config

# Afficher la configuration actuelle
php artisan fontawesome:config --show

# Réinitialiser aux valeurs par défaut
php artisan fontawesome:config --reset
```

**Fonctionnalités de la gestion de configuration :**
- **👁️ Affichage structuré** : Vue claire de toute la configuration
- **✏️ Modification granulaire** : Licence, chemins, extensions, exclusions
- **🔍 Validation** : Vérification automatique des chemins et cohérence
- **💾 Sauvegarde** : Backup automatique avant modifications importantes
- **🔄 Réinitialisation** : Retour aux valeurs par défaut avec confirmation

**Avantages pour les gros projets :**
- Configuration rapide de chemins personnalisés multiples
- Gestion simplifiée des extensions de fichiers
- Patterns d'exclusion avancés (tests, legacy, backups)
- Configuration Pro granulaire (Light, Duotone, Thin, Sharp)
```

```bash
# Migration d'un dossier spécifique
php artisan fontawesome:migrate --path=resources/views
```

```bash
# Migration avec rapport détaillé
php artisan fontawesome:migrate --verbose
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

## 📖 Documentation complète

### 🎯 Guides détaillés

- **[Guide Migration Multi-Versions](docs/migration-multi-versions-guide.md)** - Guide complet pour migrer entre FA4 → FA5 → FA6 → FA7
- **[Référence Rapide](docs/quick-reference.md)** - Commandes essentielles et exemples
- **[API Reference](docs/api-reference.md)** - Documentation programmatique pour développeurs

### 🚀 Architecture Multi-Versions (v2.0)

Le package supporte maintenant les **migrations multi-versions** avec:

```
FontAwesome 4 ──→ FontAwesome 5 ──→ FontAwesome 6 ──→ FontAwesome 7
```

#### Interface web interactive

Accédez au **configurateur multi-versions** via:
```
http://votre-app.local/fontawesome-migrator/tests
```

Fonctionnalités:
- ✅ **Sélecteur de versions** : Dropdown pour choisir source/cible
- ✅ **Validation dynamique** : Vérification des combinaisons supportées
- ✅ **Aperçu compatibilité** : Breaking changes et recommandations
- ✅ **Lancement direct** : Exécution des migrations depuis l'interface

#### Configuration JSON avancée

```
config/fontawesome-migrator/mappings/
├── 4-to-5/
├── 5-to-6/
└── 6-to-7/
```

#### Migration par versions

```bash
# FA4 → FA5 : Révolution des préfixes
php artisan fontawesome:migrate --from=4 --to=5

# FA5 → FA6 : Modernisation des noms
php artisan fontawesome:migrate --from=5 --to=6

# FA6 → FA7 : Optimisations comportementales
php artisan fontawesome:migrate --from=6 --to=7
```

## Exemples de conversions

### FA4 → FA5 : Révolution des préfixes

```html
<!-- Font Awesome 4 -->
<i class="fa fa-home"></i>
<i class="fa fa-envelope-o"></i>
<i class="fa fa-star-o"></i>

<!-- Font Awesome 5 -->
<i class="fas fa-house"></i>
<i class="far fa-envelope"></i>
<i class="far fa-star"></i>
```

### FA5 → FA6 : Modernisation des styles

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

### FA6 → FA7 : Simplifications

```html
<!-- Font Awesome 6 -->
<i class="fa-solid fa-user-large"></i>
<i class="fa-solid fa-fw fa-icon"></i>
<div class="sr-only">Texte caché</div>

<!-- Font Awesome 7 -->
<i class="fa-solid fa-user"></i>
<i class="fa-solid fa-icon"></i> <!-- fa-fw supprimé -->
<div aria-label="Texte caché"></div> <!-- sr-only → aria-label -->
```

### Migration des assets (tous versions)

#### CDN URLs
```html
<!-- FA4 → FA5 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
↓
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<!-- FA5 → FA6 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
↓
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.15.4/css/all.min.css">

<!-- FA6 → FA7 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.15.4/css/all.min.css">
↓
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css">
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

#### webpack.mix.js
```javascript
// Avant - Laravel Mix avec FontAwesome 5
mix.scripts([
    'node_modules/@fortawesome/fontawesome-free/js/solid.js',
    'node_modules/@fortawesome/fontawesome-free/js/fontawesome.js'
], 'public/js/fontawesome.js');

// Après - Laravel Mix avec FontAwesome 6
mix.scripts([
    'node_modules/@fortawesome/fontawesome-free/js/solid.js',
    'node_modules/@fortawesome/fontawesome-free/js/fontawesome.js'
], 'public/js/fontawesome.js');
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

## Support universel des styles

Le package **reconnaît et convertit TOUS les styles FontAwesome** (solid, regular, light, duotone, thin, sharp) indépendamment de votre licence :

### 🎯 **Comportement intelligent :**

1. **Reconnaissance complète** : Tous les styles FA5 sont convertis vers leur équivalent FA6
   - `fal fa-star` → `fa-light fa-star` (toujours)
   - `fad fa-heart` → `fa-duotone fa-heart` (toujours)

2. **Fallback selon la licence** : Les styles Pro sont adaptés selon votre licence
   - **Licence Pro** : Styles Pro conservés (`fa-light`, `fa-duotone`)
   - **Licence Free** : Fallback vers styles gratuits (`fa-solid`, `fa-regular`)

### 📝 **Exemple avec licence Free :**
```bash
# Input (FA5)
<i class="fal fa-star"></i>   # Light style
<i class="fad fa-heart"></i>  # Duotone style

# Output (FA6 avec fallback)
<i class="fa-solid fa-star"></i>  # Fallback vers solid
<i class="fa-solid fa-heart"></i> # Fallback vers solid
```

### 📝 **Exemple avec licence Pro :**
```bash
# Input (FA5)
<i class="fal fa-star"></i>   # Light style
<i class="fad fa-heart"></i>  # Duotone style

# Output (FA6 Pro)
<i class="fa-light fa-star"></i>   # Style Pro conservé
<i class="fa-duotone fa-heart"></i> # Style Pro conservé
```

### Configuration du fallback

```bash
# Exemple avant migration
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
📊 Rapport généré automatiquement :
   • Migration : migration_abc123
   • Métadonnées : metadata.json
   • Interface web : http://localhost/fontawesome-migrator/migrations
```

### 🎛️ Interface web complète

Le package inclut une **interface web moderne** avec plusieurs sections :

#### 🏠 Dashboard principal (`/fontawesome-migrator/`)
- **📊 Statistiques** : Vue d'ensemble des migrations et migrations
- **🚀 Actions rapides** : Liens vers les fonctionnalités principales
- **📈 Activité récente** : Dernières migrations et rapports

#### 🧪 Configurateur multi-versions (`/fontawesome-migrator/tests`)
- **🎯 Sélecteur interactif** : Dropdown pour choisir versions source/cible
- **✅ Validation dynamique** : Vérification des migrations supportées
- **📋 Aperçu compatibilité** : Breaking changes et recommandations
- **🚀 Lancement direct** : Exécution des migrations depuis l'interface

#### 📊 Gestion des rapports (`/fontawesome-migrator/reports`)
- **📋 Liste complète** : Tous les rapports avec métadonnées
- **🔍 Accès direct** : Liens vers HTML et JSON de chaque rapport
- **🗑️ Gestion** : Suppression individuelle ou nettoyage automatique

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
- **Bootstrap Icons 1.13.1** : Icônes cohérentes sur toute l'interface
- **Classes utilitaires Bootstrap** : Espacement, couleurs et typographie
- **Performance améliorée** : Pas de dépendances CSS/JS externes

### 🎨 Design Bootstrap 5 moderne

L'interface a été entièrement migrée vers Bootstrap 5.3.7 :
- **Composants Bootstrap natifs** : Cards, Tables, Navbar, Breadcrumbs
- **Design system cohérent** : Classes Bootstrap sur toute l'application
- **Performance optimisée** : Suppression de Chart.js, CSS/JS inline
- **Navigation moderne** : Navbar sticky avec breadcrumbs intégrés
- **Responsive mobile-first** : Grilles Bootstrap adaptatives
- **États vides informatifs** : Messages clairs avec suggestions d'actions

## 🧪 Panneau de Debug (Nouveau v2.0)

Le package inclut un **panneau de debug interactif** pour faciliter la validation et le débogage :

### 🚀 Accès au panneau

Accessible à `/fontawesome-migrator/test/panel` depuis l'interface de gestion des rapports.

### 🎯 Fonctionnalités du panneau

**📊 Dashboard des migrations**
- Statistiques en temps réel des migrations de migration
- Nombre total de migrations et sauvegardes
- Taille totale utilisée et dernière activité
- Aperçu visuel de l'activité de migration

**🔬 Exécution interactive**
```bash
# Types de migrations disponibles via l'interface :
🔍 Dry-Run           # Migration de prévisualisation
🎯 Icônes seulement  # Migration des icônes uniquement
🎨 Assets seulement  # Migration des assets uniquement
⚡ Migration réelle  # Migration complète (attention !)
```

**📋 Gestion des migrations**
- Liste complète des migrations de migration créées
- Inspection détaillée avec visualiseur JSON intégré
- Métadonnées complètes : durée, type, fichiers sauvegardés
- Navigation fluide entre migrations

**🧹 Nettoyage intelligent**
- Suppression des migrations anciennes (> 7 jours ou > 1 jour)
- Nettoyage automatique avec confirmation
- Statistiques de nettoyage en temps réel

### 🏗️ Architecture par migrations

La v2.0 utilise une nouvelle organisation des sauvegardes :

```
storage/app/fontawesome-backups/
├── migration-migration_66ba1234abcd5678/
│   ├── .gitignore
│   ├── metadata.json
│   ├── resources_views_file1.blade.php
│   └── public_css_file2.css
└── migration-migration_66ba9876efgh9012/
    ├── .gitignore
    ├── metadata.json
    └── resources_js_file3.js
```

**Avantages :**
- ⚡ **Traçabilité parfaite** : 1 migration = 1 répertoire avec toutes ses sauvegardes
- 📋 **Métadonnées intégrées** : Fichier `metadata.json` dans chaque migration
- 🔗 **Liaison directe** : Correspondance exacte métadonnées ↔ sauvegardes
- 🧹 **Nettoyage facilité** : Suppression par migration complète
- 🔍 **Inspection avancée** : Exploration détaillée via interface web

### 💡 Cas d'usage

**🧑‍💻 Pour les développeurs :**
- Exécution rapide sans ligne de commande
- Débogage visuel des problèmes de migration
- Validation des configurations avant production

**👥 Pour les équipes :**
- Formation et démonstration des migrations
- Validation collaborative des résultats
- Partage des migrations de migration

**🏢 Pour la production :**
- Validation avant déploiement
- Audit des migrations effectuées
- Nettoyage automatique des données temporaires

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

# 3. Vérifier la qualité du code
composer quality
```

#### Environnement Docker (avec d-packages-exec)

**Note** : `d-packages-exec` est un environnement Docker propriétaire à la société AXN Informatique. Si vous n'utilisez pas cet environnement, adaptez les commandes selon votre configuration Docker.

Si vous utilisez Docker avec `d-packages-exec php84` :

**⚠️ Important : Utilisez votre terminal WSL Ubuntu (pas le terminal VSCode)**

```bash
# 1. Cloner le projet
git clone https://github.com/forxer/fontawesome-migrator.git
cd fontawesome-migrator

# 2. Exécuter les commandes manuellement
d-packages-exec php84 composer install
d-packages-exec php84 composer quality

### Scripts Composer disponibles

#### Environnement standard
```bash
# Qualité de code
composer pint             # Formatter le code (Laravel Pint)
composer pint-test        # Vérifier le style sans corriger
composer rector           # Moderniser le code (Rector)
composer rector-dry       # Prévisualiser les modernisations
composer quality          # Contrôle qualité complet (style + rector)
```

#### Environnement Docker (AXN Informatique)
```bash
# Qualité de code avec d-packages-exec (environnement propriétaire AXN)
d-packages-exec php84 composer pint             # Formatter le code
d-packages-exec php84 composer pint-test        # Vérifier le style sans corriger
d-packages-exec php84 composer rector           # Moderniser le code
d-packages-exec php84 composer rector-dry       # Prévisualiser les modernisations
d-packages-exec php84 composer quality          # Contrôle qualité complet
```

### Avant de soumettre une PR

#### Environnement standard
1. **Style de code** : Formatez le code avec Pint
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

#### Environnement Docker (AXN Informatique)
**Méthodes disponibles avec d-packages-exec** :
```bash
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

## Version 2.0.0 - En développement 🚧

**État actuel** : Architecture multi-versions implémentée, optimisations en cours

### Fonctionnalités implémentées

- ✅ **Architecture multi-versions** : MigrationVersionManager + mappers FA4→5→6→7
- ✅ **Configuration JSON** : ConfigurationLoader avec mappings externalisés et cache
- ✅ **Interface web avancée** : Configurateur multi-versions interactif `/tests`
- ✅ **Documentation complète** : Guide multi-versions, API reference, Quick reference
- ✅ **Injection de dépendances modernisée** dans les commandes Laravel
- ✅ **Système de métadonnées centralisé** avec gestion des migrations
- ✅ **Interface web reorganisée** avec contrôleurs spécialisés
- ✅ **Navigation fluide** entre rapports, migrations et tests
- ✅ **Migration Bootstrap 5** : Design system moderne et cohérent
- ✅ **Traçabilité complète** : Origine CLI/Web dans métadonnées et rapports

### Prochaines étapes

- 🕒 **Optimisation CSS** : Consolidation des 1782 lignes de partials CSS
- 🕒 **Tests automatisés** : Refonte pour les nouveaux composants multi-versions
- 🕒 **Migrations chaînées** : Support 4→5→6→7 en une commande (optionnel)

**Objectif** : Package Laravel professionnel avec architecture multi-versions complète

## Licence

Ce package est sous licence MIT. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## Changelog

Voir [CHANGELOG.md](CHANGELOG.md) pour l'historique des versions.

## Support

🐛 [Signaler un bug](https://github.com/forxer/fontawesome-migrator/issues)
