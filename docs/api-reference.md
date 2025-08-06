# FontAwesome Migrator - API Reference

Documentation pour l'utilisation programmatique du système de migration multi-versions.

## Services principaux

### MigrationVersionManager

Gestionnaire central pour les migrations multi-versions.

```php
use FontAwesome\Migrator\Services\Core\MigrationVersionManager;

$manager = new MigrationVersionManager();
```

#### Méthodes

##### `createMapper(string $fromVersion, string $toVersion): VersionMapperInterface`

Crée un mapper pour une migration spécifique.

```php
$mapper = $manager->createMapper('5', '6');
$result = $mapper->mapIcon('fa-home');
// ['new_name' => 'fa-house', 'renamed' => true, ...]
```

##### `detectVersion(string $content): string`

Détecte automatiquement la version FontAwesome dans le contenu.

```php
$content = '<i class="fas fa-home"></i>';
$version = $manager->detectVersion($content); // "5"
```

##### `getSupportedMigrations(): array`

Retourne toutes les migrations supportées.

```php
$migrations = $manager->getSupportedMigrations();
/*
[
    ['from' => '4', 'to' => '5', 'mapper' => FontAwesome4To5Mapper::class],
    ['from' => '5', 'to' => '6', 'mapper' => FontAwesome5To6Mapper::class],
    ['from' => '6', 'to' => '7', 'mapper' => FontAwesome6To7Mapper::class],
]
*/
```

##### `getCompatibilityReport(string $fromVersion, string $toVersion): array`

Génère un rapport de compatibilité pour une migration.

```php
$report = $manager->getCompatibilityReport('6', '7');
/*
[
    'supported' => true,
    'breaking_changes' => ['Fixed width par défaut', 'Format .woff2 uniquement'],
    'recommendations' => ['Revoir l\'accessibilité', 'Migrer vers Dart Sass']
]
*/
```

### ConfigurationLoader

Service de chargement des configurations JSON.

```php
use FontAwesome\Migrator\Services\Configuration\ConfigurationLoader;

$loader = new ConfigurationLoader('/custom/config/path');
```

#### Méthodes

##### `loadStyleMappings(string $fromVersion, string $toVersion): array`

Charge les mappings de styles depuis les fichiers JSON.

```php
$mappings = $loader->loadStyleMappings('4', '5');
// ['fa' => 'fas', 'fa fa-' => 'fas fa-']
```

##### `loadIconMappings(string $fromVersion, string $toVersion): array`

Charge les mappings d'icônes.

```php
$mappings = $loader->loadIconMappings('5', '6');
// ['fa-home' => 'fa-house', 'fa-envelope-o' => 'fa-envelope']
```

##### `getAvailableMigrations(): array`

Découvre automatiquement les migrations disponibles.

```php
$migrations = $loader->getAvailableMigrations();
/*
[
    ['key' => '4-to-5', 'from' => '4', 'to' => '5', 'path' => '/path/to/config'],
    ['key' => '5-to-6', 'from' => '5', 'to' => '6', 'path' => '/path/to/config'],
]
*/
```

##### `validateMigrationConfig(string $fromVersion, string $toVersion): array`

Valide la structure d'une configuration de migration.

```php
$errors = $loader->validateMigrationConfig('6', '7');
// [] si valide, ou array d'erreurs
```

## Mappers spécialisés

### FontAwesome4To5Mapper

```php
use FontAwesome\Migrator\Services\Mappers\FontAwesome4To5Mapper;

$mapper = new FontAwesome4To5Mapper($config, $configLoader);
```

### FontAwesome5To6Mapper

```php
use FontAwesome\Migrator\Services\Mappers\FontAwesome5To6Mapper;

$mapper = new FontAwesome5To6Mapper($config, $configLoader);
```

### FontAwesome6To7Mapper

```php
use FontAwesome\Migrator\Services\Mappers\FontAwesome6To7Mapper;

$mapper = new FontAwesome6To7Mapper($config, $configLoader);
```

## Interface VersionMapperInterface

Tous les mappers implémentent cette interface commune.

### Méthodes principales

##### `mapIcon(string $iconName, string $style = 'fa'): array`

Mappe une icône vers sa nouvelle version.

```php
$result = $mapper->mapIcon('fa-envelope-o');
/*
[
    'new_name' => 'fa-envelope',
    'found' => true,
    'deprecated' => false,
    'pro_only' => false,
    'renamed' => true,
    'warnings' => ['Icône outlined → style Regular (far)']
]
*/
```

##### `mapStyle(string $style, bool $withFallback = true): string`

Mappe un style vers sa nouvelle version.

```php
$newStyle = $mapper->mapStyle('fa'); // 'fas' (FA4→FA5)
$newStyle = $mapper->mapStyle('fas'); // 'fa-solid' (FA5→FA6)
```

##### `getIconMappings(): array`

Retourne tous les mappings d'icônes.

```php
$mappings = $mapper->getIconMappings();
// ['fa-old' => 'fa-new', ...]
```

##### `getFreeAlternative(string $iconName): ?string`

⭐ **NOUVEAU v2.0** - Obtient une alternative Free pour une icône Pro/dépréciée.

```php
// Alternative pour icône dépréciée
$alt = $mapper->getFreeAlternative('fa-home'); // 'fa-house'

// Alternative Pro → Free (licence Free)
$alt = $mapper->getFreeAlternative('fa-analytics'); // 'fa-chart-line'

// Aucune alternative
$alt = $mapper->getFreeAlternative('fa-custom'); // null
```

##### `findSimilarIcons(string $iconName): array`

Trouve des icônes similaires pour suggestions.

```php
$similar = $mapper->findSimilarIcons('fa-home');
/*
[
    ['icon' => 'fa-house', 'reason' => 'Renommage', 'confidence' => 0.9],
    ['icon' => 'fa-building', 'reason' => 'Similaire', 'confidence' => 0.7]
]
*/
```

##### `getMappingStats(): array`

Statistiques sur les mappings du mapper.

```php
$stats = $mapper->getMappingStats();
/*
[
    'renamed_icons' => 45,
    'deprecated_icons' => 12,
    'pro_only_icons' => 8,
    'new_icons' => 23
]
*/
```

## Utilisation avancée

### Migration programmatique complète

```php
use FontAwesome\Migrator\Services\Core\MigrationVersionManager;
use FontAwesome\Migrator\Services\Core\FileScanner;
use FontAwesome\Migrator\Services\Core\IconReplacer;

// 1. Initialiser le gestionnaire
$manager = new MigrationVersionManager();

// 2. Détecter la version source
$content = file_get_contents('app.blade.php');
$fromVersion = $manager->detectVersion($content);

// 3. Créer le mapper approprié
$mapper = $manager->createMapper($fromVersion, '7');

// 4. Scanner les fichiers
$scanner = new FileScanner($config);
$files = $scanner->scanDirectory('resources/views');

// 5. Appliquer les transformations
$replacer = new IconReplacer($mapper, $config);
foreach ($files as $file) {
    $result = $replacer->processFile($file);
    // Traiter le résultat
}
```

### ConfigurationLoader API

⭐ **NOUVEAU v2.0** - Service de chargement des configurations JSON.

```php
use FontAwesome\Migrator\Services\Configuration\ConfigurationLoader;

$loader = new ConfigurationLoader();

// Charger les alternatives Free
$alternatives = $loader->loadAlternatives('5', '6');
// ['fa-home' => 'fa-house', 'fa-analytics' => 'fa-chart-line', ...]

// Charger les mappings d'icônes  
$iconMappings = $loader->loadIconMappings('5', '6');

// Charger les mappings de styles
$styleMappings = $loader->loadStyleMappings('5', '6');

// Charger les icônes dépréciées
$deprecated = $loader->loadDeprecatedIcons('5', '6');

// Nettoyer le cache
$loader->clearCache();
```

### Configuration personnalisée

```php
// Charger une configuration personnalisée
$customConfigPath = '/path/to/custom/mappings';
$loader = new ConfigurationLoader($customConfigPath);

// Créer un mapper avec configuration personnalisée
$mapper = new FontAwesome5To6Mapper($config, $loader);
```

### Validation et tests

```php
// Valider une migration avant exécution
$manager = new MigrationVersionManager();
$report = $manager->getCompatibilityReport('5', '6');

if (!$report['supported']) {
    throw new Exception('Migration non supportée');
}

// Tester un mapping spécifique
$mapper = $manager->createMapper('4', '5');
$result = $mapper->mapIcon('fa-envelope-o');

assert($result['new_name'] === 'fa-envelope');
assert($result['renamed'] === true);
```

## Gestion des erreurs

### Exceptions communes

```php
use InvalidArgumentException;

try {
    $mapper = $manager->createMapper('8', '9');
} catch (InvalidArgumentException $e) {
    // Migration non supportée
    echo "Erreur: " . $e->getMessage();
}
```

### Fallbacks et récupération

```php
// Les mappers utilisent automatiquement les fallbacks
$mapper = new FontAwesome4To5Mapper();

// Même sans fichiers de configuration, le système fonctionne
// grâce aux mappings hardcodés de base
$result = $mapper->mapIcon('fa-envelope-o');
// Fonctionne toujours avec les mappings essentiels
```

## Configuration du système

### config/fontawesome-migrator.php

```php
return [
    'license_type' => 'free', // ou 'pro'
    'fallback_strategy' => 'solid', // 'regular', 'brands'
    'sessions_path' => storage_path('app/fontawesome-migrator'),
    
    // Chemins de scan par défaut
    'scan_paths' => [
        'resources/views',
        'resources/js',
        'resources/css',
    ],
    
    // Extensions supportées
    'file_extensions' => [
        'blade.php', 'php', 'vue', 'js', 'ts', 'css', 'scss'
    ],
];
```

### Injection de dépendances Laravel

```php
// Dans un ServiceProvider
$this->app->singleton(MigrationVersionManager::class, function ($app) {
    return new MigrationVersionManager(
        $app->make(ConfigurationLoader::class)
    );
});

// Utilisation avec injection
class MigrationController extends Controller
{
    public function migrate(MigrationVersionManager $manager)
    {
        $migrations = $manager->getSupportedMigrations();
        return view('migrations', compact('migrations'));
    }
}
```

## Performance et optimisation

### Cache et mise en cache

```php
// Le ConfigurationLoader cache automatiquement les configurations
$loader = new ConfigurationLoader();

// Première charge : lecture des fichiers JSON
$mappings1 = $loader->loadStyleMappings('5', '6');

// Deuxième charge : données en cache
$mappings2 = $loader->loadStyleMappings('5', '6');

// Vider le cache si nécessaire
$loader->clearCache();
```

### Optimisations pour gros volumes

```php
// Traitement par lots pour de gros volumes
$scanner = new FileScanner($config);
$files = $scanner->scanDirectory('resources/views');

$batchSize = 50;
$batches = array_chunk($files, $batchSize);

foreach ($batches as $batch) {
    // Traiter chaque lot séparément
    foreach ($batch as $file) {
        $replacer->processFile($file);
    }
    
    // Libérer la mémoire entre les lots
    gc_collect_cycles();
}
```