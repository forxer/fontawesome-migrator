# Guide de Migration Multi-Versions FontAwesome

Ce guide explique comment utiliser le système de migration multi-versions du package FontAwesome Migrator pour migrer entre les différentes versions de FontAwesome (4 → 5 → 6 → 7).

## Table des Matières

1. [Vue d'ensemble](#vue-densemble)
2. [Migrations supportées](#migrations-supportées)
3. [Interface en ligne de commande](#interface-en-ligne-de-commande)
4. [Interface web](#interface-web)
5. [Configuration avancée](#configuration-avancée)
6. [Exemples pratiques](#exemples-pratiques)
7. [Dépannage](#dépannage)

## Vue d'ensemble

Le système de migration multi-versions permet de migrer automatiquement votre code FontAwesome entre les versions majeures. Le package détecte automatiquement la version source et applique les transformations appropriées.

### Architecture

```
FontAwesome 4 → FontAwesome 5 → FontAwesome 6 → FontAwesome 7
     ↓               ↓               ↓               ↓
FA4To5Mapper    FA5To6Mapper    FA6To7Mapper    [Future]
```

### Fonctionnalités principales

- **Détection automatique** de la version source
- **Migrations directes** entre versions adjacentes
- **Interface web interactive** avec sélecteur de versions
- **Configuration JSON** pour les mappings personnalisés
- **Rapports détaillés** avec traçabilité complète
- **Support Pro/Free** avec fallbacks automatiques

## Migrations supportées

| Migration | Description | Changements principaux |
|-----------|-------------|----------------------|
| **4 → 5** | Révolution des préfixes | `fa` → `fas`, suppression suffixes `-o`, nouveaux styles Pro |
| **5 → 6** | Modernisation des noms | Nombreux renommages, nouvelles icônes, amélioration styles |
| **6 → 7** | Optimisations comportementales | Fixed width par défaut, accessibilité, format .woff2 |

### Détails par migration

#### FontAwesome 4 → 5
- **Préfixes** : `fa` devient `fas` (solid par défaut)
- **Icônes outlined** : Suffixe `-o` supprimé, style `far` (regular)
- **Renommages** : `fa-home` → `fa-house`, `fa-times` → `fa-xmark`
- **Nouveautés** : Styles Pro (`fal`, `fad`), nouvelles icônes

#### FontAwesome 5 → 6  
- **Préfixes longs** : `fas` → `fa-solid`, `far` → `fa-regular`
- **Renommages massifs** : `fa-sort-alpha-down` → `fa-arrow-down-a-z`
- **Nouvelles icônes** : `fa-house`, `fa-magnifying-glass`, `fa-user-group`
- **Améliorations** : Styles Pro étendus (`fa-thin`, `fa-sharp`)

#### FontAwesome 6 → 7
- **Comportements** : Fixed width par défaut, icônes décoratives
- **Simplifications** : `fa-user-large` → `fa-user`
- **Technologie** : Format .woff2 uniquement, Dart Sass requis
- **Accessibilité** : `sr-only` supprimé au profit d'`aria-label`

## Interface en ligne de commande

### Commande principale

```bash
# Migration interactive (recommandée)
php artisan fontawesome:migrate

# Migration avec version spécifique
php artisan fontawesome:migrate --from=5 --to=6

# Migration dry-run pour prévisualiser
php artisan fontawesome:migrate --from=4 --to=5 --dry-run

# Migration avec rapport détaillé
php artisan fontawesome:migrate --from=6 --to=7 --verbose
```

### Options avancées

```bash
# Migration d'un chemin spécifique
php artisan fontawesome:migrate --path=resources/views --from=5 --to=6

# Migration icons uniquement
php artisan fontawesome:migrate --icons-only --from=4 --to=5

# Migration assets uniquement (CDN, packages)
php artisan fontawesome:migrate --assets-only --from=5 --to=6

# Mode non-interactif
php artisan fontawesome:migrate --no-interactive --from=6 --to=7
```

### Détection automatique

Si aucune version n'est spécifiée, le système détecte automatiquement :

```bash
# Détection automatique + migration interactive
php artisan fontawesome:migrate

# Le système analyse votre code et propose les migrations appropriées
```

## Interface web

### Accès à l'interface

Accédez à l'interface web via votre navigateur :

```
http://votre-app.local/fontawesome-migrator/
```

### Pages disponibles

| Page | URL | Description |
|------|-----|-------------|
| **Accueil** | `/fontawesome-migrator/` | Dashboard avec statistiques et actions rapides |
| **Tests** | `/fontawesome-migrator/tests` | **Configurateur multi-versions interactif** |
| **Rapports** | `/fontawesome-migrator/reports` | Visualisation des rapports de migration |
| **Sessions** | `/fontawesome-migrator/sessions` | Gestion des sessions de migration |

### Configurateur multi-versions (`/tests`)

L'interface la plus puissante pour configurer et lancer des migrations :

#### Fonctionnalités

- **Sélecteur de versions** : Dropdown interactif pour From/To
- **Validation dynamique** : Vérification des combinaisons supportées
- **Aperçu de compatibilité** : Breaking changes et recommandations
- **Configuration avancée** : Options dry-run, chemins, modes
- **Lancement direct** : Exécution des migrations depuis l'interface

#### Utilisation

1. **Sélectionner les versions** : Choisir version source et cible
2. **Configurer les options** : Dry-run, chemins spécifiques, modes
3. **Prévisualiser** : Voir les changements prévus et recommandations
4. **Lancer la migration** : Exécuter et suivre le progrès en temps réel

## Configuration avancée

### Fichiers de configuration JSON

Le système utilise des fichiers JSON pour définir les mappings :

```
config/fontawesome-migrator/mappings/
├── 4-to-5/
│   ├── styles.json      # Mappings des styles
│   ├── icons.json       # Mappings des icônes
│   ├── deprecated.json  # Icônes dépréciées
│   ├── pro-only.json   # Icônes Pro uniquement
│   └── new-icons.json  # Nouvelles icônes
├── 5-to-6/
│   ├── styles.json
│   └── icons.json
└── 6-to-7/
    ├── styles.json
    ├── icons.json
    └── deprecated.json
```

### Structure des fichiers

#### styles.json
```json
{
  "description": "Mappings des styles FontAwesome X vers Y",
  "version": "X-to-Y",
  "mappings": {
    "old-style": "new-style",
    "fa": "fas"
  }
}
```

#### icons.json
```json
{
  "description": "Mappings des icônes FontAwesome X vers Y",
  "version": "X-to-Y",
  "outlined_icons": {
    "target_style": "far",
    "mappings": {
      "fa-envelope-o": "fa-envelope"
    }
  },
  "renamed_icons": {
    "fa-old-name": "fa-new-name"
  }
}
```

### Personnalisation des mappings

Vous pouvez modifier les fichiers JSON pour :

- **Ajouter des mappings personnalisés**
- **Modifier les comportements de migration**
- **Définir des fallbacks spécifiques**
- **Adapter aux besoins de votre projet**

### ConfigurationLoader

Le service `ConfigurationLoader` gère le chargement des configurations :

```php
use FontAwesome\Migrator\Services\ConfigurationLoader;

$loader = new ConfigurationLoader();

// Charger les mappings pour une migration
$styleMappings = $loader->loadStyleMappings('5', '6');
$iconMappings = $loader->loadIconMappings('4', '5');

// Valider une configuration
$errors = $loader->validateMigrationConfig('6', '7');
```

## Exemples pratiques

### Exemple 1 : Migration FA4 → FA5

**Avant (FA4) :**
```html
<i class="fa fa-envelope-o"></i>
<i class="fa fa-star-o"></i>
<i class="fa fa-home"></i>
```

**Après (FA5) :**
```html
<i class="far fa-envelope"></i>
<i class="far fa-star"></i>  
<i class="fas fa-house"></i>
```

**Commande :**
```bash
php artisan fontawesome:migrate --from=4 --to=5 --dry-run
```

### Exemple 2 : Migration FA5 → FA6 avec rapport

**Commande :**
```bash
php artisan fontawesome:migrate --from=5 --to=6 --verbose --path=resources/views
```

**Rapport généré :**
- Fichiers analysés : 45
- Icônes transformées : 127
- Avertissements : 8 (icônes Pro détectées)
- Nouveau rapport : `storage/app/fontawesome-migrator/session_abc12345/`

### Exemple 3 : Migration FA6 → FA7 via interface web

1. Aller sur `/fontawesome-migrator/tests`
2. Sélectionner "6" → "7"
3. Activer "Dry run" pour prévisualiser
4. Cliquer "Lancer la migration"
5. Consulter le rapport généré

## Dépannage

### Problèmes fréquents

#### 1. Migration non supportée

**Erreur :** `Migration X → Y non supportée`

**Solutions :**
- Vérifier les versions supportées : 4→5, 5→6, 6→7
- Utiliser des migrations intermédiaires pour les sauts de versions
- Vérifier que les mappers sont bien installés

#### 2. Fichiers de configuration manquants

**Erreur :** `Configuration non trouvée pour la migration X-to-Y`

**Solutions :**
- Vérifier que les fichiers JSON existent dans `config/fontawesome-migrator/mappings/`
- Publier la configuration : `php artisan vendor:publish --tag=fontawesome-migrator-config`
- Le système utilisera les fallbacks hardcodés automatiquement

#### 3. Icônes Pro détectées en licence Free

**Avertissement :** `Icône Pro uniquement: fa-example`

**Solutions :**
- Configurer la licence Pro dans `config/fontawesome-migrator.php`
- Utiliser les fallbacks Free automatiques
- Remplacer manuellement par des icônes Free équivalentes

#### 4. Erreurs de parsing dans les fichiers

**Erreur :** `Parse error in file.blade.php`

**Solutions :**
- Vérifier la syntaxe des fichiers avant migration
- Utiliser `--dry-run` pour prévisualiser les changements
- Créer une sauvegarde avant migration

### Validation et vérification

#### Vérifier la configuration

```bash
# Tester la configuration
php artisan fontawesome:config --show

# Valider les mappings
php artisan fontawesome:config --validate
```

#### Tester une migration

```bash
# Dry-run complet
php artisan fontawesome:migrate --dry-run --verbose

# Test sur un fichier spécifique
php artisan fontawesome:migrate --dry-run --path=resources/views/test.blade.php
```

### Debugging avancé

#### Logs détaillés

Les migrations génèrent des logs détaillés dans :
- `storage/logs/laravel.log`
- Rapports de session : `storage/app/fontawesome-migrator/`

#### Mode verbose

```bash
php artisan fontawesome:migrate --verbose --from=5 --to=6
```

Affiche :
- Fichiers analysés en temps réel
- Transformations appliquées
- Avertissements et recommandations
- Statistiques détaillées

## Support et contribution

### Signaler un problème

1. **Logs** : Joindre les logs de migration
2. **Configuration** : Préciser la version Laravel et PHP
3. **Contexte** : Décrire la migration tentée
4. **Fichiers** : Fournir des exemples de code problématique

### Contribuer

- **Mappings** : Proposer des améliorations aux fichiers JSON
- **Nouveaux mappers** : Ajouter le support pour de nouvelles versions
- **Tests** : Améliorer la couverture de tests
- **Documentation** : Améliorer ce guide

---

**Version du guide :** Compatible avec FontAwesome Migrator v2.0.0+  
**Dernière mise à jour :** Août 2025  
**Architecture :** Multi-versions FA4→5→6→7