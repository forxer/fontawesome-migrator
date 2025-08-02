# Système JSON Alternatives - FontAwesome Migrator v2.0

## Vue d'ensemble

Le système JSON alternatives permet de fournir des alternatives **Free** pour les icônes **Pro/dépréciées** lors des migrations FontAwesome. Ce système assure la **compatibilité FontAwesome** (pas la compatibilité du package).

## Architecture

### Structure des fichiers

```
config/fontawesome-migrator/alternatives/
├── 4-to-5.json    # Alternatives FA4 → FA5
├── 5-to-6.json    # Alternatives FA5 → FA6  
└── 6-to-7.json    # Alternatives FA6 → FA7
```

### Format JSON

```json
{
    "metadata": {
        "version": "2.0.0",
        "migration": "FontAwesome 5 → 6",
        "description": "Alternatives Free pour icônes Pro/dépréciées FA5→6",
        "last_updated": "2025-01-01"
    },
    "alternatives": {
        "fa-home": "fa-house",
        "fa-glass": "fa-martini-glass-empty"
    },
    "pro_to_free": {
        "fa-analytics": "fa-chart-line",
        "fa-apple-pay": "fa-credit-card"
    },
    "style_alternatives": {
        "fa-light": "fa-regular",
        "fa-duotone": "fa-solid"
    }
}
```

## Utilisation

### Dans les Mappers

```php
class FontAwesome5To6Mapper implements VersionMapperInterface
{
    private array $alternatives;

    private function loadMappings(): void
    {
        // Chargement depuis JSON
        $this->alternatives = $this->configLoader->loadAlternatives('5', '6');
    }

    public function getFreeAlternative(string $iconName): ?string
    {
        // Recherche d'alternative depuis JSON config
        return $this->alternatives[$iconName] ?? null;
    }
}
```

### Via ConfigurationLoader

```php
$configLoader = new ConfigurationLoader();

// Charger toutes les alternatives pour FA5→6
$alternatives = $configLoader->loadAlternatives('5', '6');

// Rechercher une alternative
$alternative = $alternatives['fa-home'] ?? null; // → 'fa-house'
```

## Types d'alternatives

### 1. **Alternatives d'icônes dépréciées**
```json
"alternatives": {
    "fa-home": "fa-house",           // Icône renommée
    "fa-glass": "fa-martini-glass",  // Icône dépréciée
    "fa-close": "fa-xmark"           // Icône supprimée
}
```

### 2. **Alternatives Pro → Free**
```json
"pro_to_free": {
    "fa-analytics": "fa-chart-line",     // Pro → équivalent Free
    "fa-apple-pay": "fa-credit-card",    // Pro → alternative Free
    "fa-users-crown": "fa-users"         // Pro → version basique
}
```

### 3. **Alternatives de styles**
```json
"style_alternatives": {
    "fa-light": "fa-regular",    // Style Pro → Free
    "fa-duotone": "fa-solid",    // Style Pro → Free
    "fa-thin": "fa-regular"      // Style Pro → Free
}
```

## ConfigurationLoader

### Méthode loadAlternatives()

```php
public function loadAlternatives(string $fromVersion, string $toVersion): array
{
    $cacheKey = "alternatives_{$fromVersion}_to_{$toVersion}";
    
    // Cache Redis-style
    if (isset($this->cache[$cacheKey])) {
        return $this->cache[$cacheKey];
    }

    $filePath = $this->configPath."/alternatives/{$fromVersion}-to-{$toVersion}.json";
    
    // Chargement et validation JSON
    $data = json_decode(File::get($filePath), true);
    
    // Fusion de toutes les alternatives
    $alternatives = array_merge(
        $data['alternatives'] ?? [],
        $data['pro_to_free'] ?? [],
        $data['style_alternatives'] ?? []
    );

    return $this->cache[$cacheKey] = $alternatives;
}
```

### Caractéristiques

- **Cache intégré** : Évite les lectures JSON répétées
- **Validation** : Vérification de l'existence et structure JSON
- **Fusion intelligente** : Combine tous les types d'alternatives
- **Gestion d'erreurs** : Retourne tableau vide si fichier manquant

## Exemples d'utilisation

### Migration FA5 → FA6 avec alternatives

```php
// Mapper une icône avec alternative
$mapper = new FontAwesome5To6Mapper();

// Icône dépréciée
$alt = $mapper->getFreeAlternative('fa-home');
// → 'fa-house'

// Icône Pro en licence Free
$alt = $mapper->getFreeAlternative('fa-analytics');  
// → 'fa-chart-line'

// Icône inexistante
$alt = $mapper->getFreeAlternative('fa-nonexistent');
// → null
```

### Via MigrationVersionManager

```php
$versionManager = new MigrationVersionManager();
$mapper = $versionManager->createMapper('5', '6');

// Recherche d'alternative pour icône dépréciée
$alternative = $mapper->getFreeAlternative('fa-home');
// → 'fa-house'
```

## Avantages du système

### 1. **Architecture propre**
- Séparation claire entre compatibilité FontAwesome vs Package
- Configuration externalisée (pas de hardcodé)
- Respect de l'architecture V2.0 pure

### 2. **Flexibilité**
- Alternatives personnalisables par migration
- Ajout facile de nouvelles alternatives
- Versioning des alternatives

### 3. **Performance**
- Cache intégré pour éviter lectures JSON répétées
- Chargement à la demande
- Fusion optimisée des alternatives

### 4. **Maintenabilité**
- Configuration JSON lisible et modifiable
- Documentation intégrée dans les fichiers JSON
- Validation et gestion d'erreurs

## Gestion des erreurs

### Fichier JSON manquant
```php
// Retourne tableau vide, pas d'exception
$alternatives = $configLoader->loadAlternatives('9', '10');
// → []
```

### JSON invalide
```php
// Retourne tableau vide si structure incorrecte
$alternatives = $configLoader->loadAlternatives('5', '6');
// → [] si JSON malformé
```

### Alternative inexistante
```php
// Retourne null si pas d'alternative
$alt = $mapper->getFreeAlternative('fa-custom-icon');
// → null
```

## Migration depuis l'ancien système

### Avant (hardcodé)
```php
private function getFreeFallback(string $proIcon): ?string
{
    $fallbacks = [
        'fa-analytics' => 'fa-chart-line',
        'fa-apple-pay' => 'fa-credit-card',
        // ... 40+ lignes hardcodées
    ];
    
    return $fallbacks[$proIcon] ?? null;
}
```

### Après (JSON config)
```php
public function getFreeAlternative(string $iconName): ?string
{
    // Configuration externalisée, cache intégré
    return $this->alternatives[$iconName] ?? null;
}
```

## Bonnes pratiques

### 1. **Nommage des alternatives**
- Utiliser les noms d'icônes FA officiels
- Préférer les alternatives sémantiquement proches
- Documenter les choix dans le JSON

### 2. **Organisation des fichiers**
- Un fichier par migration (4-to-5, 5-to-6, etc.)
- Séparer par type : alternatives, pro_to_free, style_alternatives
- Maintenir les métadonnées à jour

### 3. **Performance**
- Le cache est automatique, pas de gestion manuelle
- Les alternatives sont chargées une seule fois par migration
- Utiliser `clearCache()` uniquement pour les tests

## Extensibilité future

Le système supporte facilement :

- **Nouvelles migrations** : Créer `7-to-8.json`
- **Nouveaux types** : Ajouter sections dans JSON
- **Alternatives contextuelles** : Enrichir la structure JSON
- **Validation avancée** : Schémas JSON, tests automatisés

## Conclusion

Le système JSON alternatives V2.0 offre une architecture **propre**, **performante** et **extensible** pour gérer les alternatives FontAwesome. Il respecte parfaitement l'architecture V2.0 pure en éliminant tout code hardcodé tout en conservant la flexibilité nécessaire pour les migrations complexes.