# MigrateCommand v2.0 - État d'avancement

**Date de sauvegarde :** 10 août 2025  
**Contexte :** Refactorisation complète de la commande MigrateCommand depuis zéro, approche pas à pas

## 🎯 Objectif
Créer une nouvelle commande MigrateCommand simplifiée et modulaire, en remplaçant l'ancienne version trop complexe.

## ✅ Étapes Terminées

### 1. Initialisation des métadonnées
- `$this->metadata->initialize()` implémentée
- Affichage intro avec Laravel Prompts

### 2. Capture des options de commande  
- **Architecture runtime** : Options stockées dans `$this->migrationOptions[]` avant transmission aux métadonnées
- **Avantage** : État intermédiaire dans la commande, possibilité de traiter/valider avant persistance
- Toutes les options CLI capturées : `source_version`, `target_version`, `dry_run`, etc.

### 3. Configuration et validation des versions
- **Externalisée** vers `VersionConfigurationService` (injection DI)
- **Logique déléguée** : Détection automatique, validation, suggestion version cible
- **Gestion d'erreurs** : Try/catch avec RuntimeException, exit sur échec
- **Services utilisés** :
  - `$this->versionConfigService->configureVersions()` 
  - `MigrationVersionManager::getSupportedMigrations()`
  - `MigrationVersionManager::isMigrationSupported()`

### 3b. Refactorisation architecturale
- Méthode `configureVersions()` réduite de ~70 lignes à ~20 lignes
- Toute la logique complexe déplacée dans le service dédié
- Architecture propre : commande = orchestration, services = logique métier

## 🚨 OPTIONS NON IMPLÉMENTÉES À REPRENDRE

### ❌ Options capturées mais PAS utilisées

**CRITIQUE** : Toutes les options CLI sont capturées dans `captureCommandOptions()` mais la logique métier n'est pas implémentée !

#### 1. Gestion des sauvegardes
```php
// Options: --backup / --no-backup
// MANQUE: Logique de sauvegarde conditionnelle
if ($this->migrationOptions['backup'] || (!$this->migrationOptions['no_backup'] && config('default'))) {
    // Créer sauvegardes avant migration
}
```

#### 2. Mode debug
```php
// Option: --debug
// MANQUE: Affichage informations debug environnement
if ($this->migrationOptions['debug']) {
    $this->displayDebugInfo();
}
```

#### 3. Mode non-interactif
```php
// Option: --no-interactive
// MANQUE: Désactiver prompts interactifs
if ($this->migrationOptions['no_interactive']) {
    // Pas de questions/confirmations utilisateur
}
```

#### 4. Filtrage icons-only / assets-only
```php
// Options: --icons-only / --assets-only
// PARTIELLEMENT GÉRÉ: MigrationProcessor devrait respecter ces flags
// Vérifier que les options sont transmises correctement
```

#### 5. Interface web marker
```php
// Option: --web-interface
// MANQUE: Marquage source migration + comportement adapté
if ($this->migrationOptions['web_interface']) {
    // Comportement spécifique interface web
}
```

### 🔧 Actions requises

1. **Implémenter logique `--backup` / `--no-backup`**
2. **Créer méthode `displayDebugInfo()` pour `--debug`**
3. **Gérer `--no-interactive` dans prompts**
4. **Vérifier transmission `--icons-only` / `--assets-only` à MigrationProcessor**
5. **Implémenter marquage `--web-interface` dans métadonnées**

## 📁 Fichiers Concernés

### Principal
- **`/src/Commands/MigrateCommand.php`** (148 lignes) - Nouvelle version en cours
- **`/src/Commands/MigrateCommand.backup.php`** - Sauvegarde de l'ancienne version

### Services utilisés
- **`VersionConfigurationService`** - Configuration et validation des versions
- **`FileScannerInterface`** - Scanner les fichiers du projet
- **`MigrationVersionManager`** - Détection versions, validation migrations
- **`IconReplacer`** - Migration des classes d'icônes
- **`AssetMigrator`** - Migration des assets CSS/JS/CDN  
- **`MigrationReporter`** - Génération des rapports
- **`MetadataManagerInterface`** - Gestion des métadonnées

## 🏗️ Architecture Actuelle

### Injection de dépendances complète
```php
public function handle(
    FileScannerInterface $scanner,
    IconReplacer $replacer,
    MigrationReporter $reporter,
    AssetMigrator $assetMigrator,
    MetadataManagerInterface $metadata,
    MigrationVersionManager $versionManager,
    VersionConfigurationService $versionConfigService
): int
```

### État runtime dans la commande
```php
protected array $migrationOptions = [];
// Stockage des options CLI avant transmission aux métadonnées
// Facilite l'accès et la modification en cours de traitement
```

### Séparation des responsabilités
- **Commande** : Orchestration, gestion d'erreurs, affichage
- **Services** : Logique métier, détection, validation, traitement
- **Métadonnées** : Persistance des informations de migration

## 🎯 Principes Respectés

1. **Injection DI pure** - Tous les services injectés via constructeur
2. **Une responsabilité par méthode** - Méthodes courtes et focalisées  
3. **Services externalisés** - Logique complexe déléguée aux services
4. **Gestion d'erreurs centralisée** - Try/catch avec messages clairs
5. **Options runtime** - État intermédiaire avant persistance
6. **Pas de valeurs par défaut** - Échec si versions non déterminées

## 🚀 Pour Reprendre

1. **Lire ce fichier** pour contexte
2. **Ouvrir MigrateCommand.php** - Reprendre à partir de l'étape 4
3. **Suivre les étapes séquentiellement** - Une par une, tester chaque étape
4. **Utiliser les services existants** - Ne pas réimplémenter la logique
5. **Garder l'approche modulaire** - Externaliser si une méthode devient complexe

## 📝 Notes Importantes

- **Nommage cohérent** : `source_version`/`target_version` partout (pas de `from`/`to` en interne)
- **Pas de valeurs par défaut** : Si pas de versions → arrêt avec erreur explicite  
- **Services avant tout** : Utiliser `MigrationVersionManager`, `VersionConfigurationService`, etc.
- **Architecture testable** : Services injectés = facilite les tests unitaires
- **Approche pas à pas** : Une étape à la fois, validation avant continuation

---

*Refactorisation initiée le 10 août 2025 - À reprendre après implémentation feature UI*