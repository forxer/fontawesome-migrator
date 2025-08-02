# Analyse de l'architecture actuelle des services de mapping

## Services existants

### 1. IconMapper (`src/Services/IconMapper.php`)

**Responsabilités actuelles :**
- Mappings icônes FA5 → FA6 (renommages)
- Détection icônes dépréciées FA6
- Gestion icônes Pro vs Free
- Fallbacks pour icônes Pro → Free
- Recherche d'icônes similaires

**Structure des données :**
```php
protected array $renamedIcons;      // FA5 → FA6 mappings
protected array $deprecatedIcons;   // Icônes supprimées en FA6
protected array $proOnlyIcons;      // Icônes Pro uniquement
protected array $newIcons;          // Nouvelles icônes FA6
```

**Méthodes principales :**
- `mapIcon()` - Mapping simple
- `mapIconDetailed()` - Mapping avec métadonnées complètes
- `findSimilarIcons()` - Recherche similarité
- `iconExistsInFA6()` - Validation existence

### 2. StyleMapper (`src/Services/StyleMapper.php`)

**Responsabilités actuelles :**
- Conversion styles FA5 → FA6 (`fas` → `fa-solid`)
- Gestion fallbacks Pro → Free selon licence
- Détection type de licence basée sur styles
- Recommandations de styles FA6

**Structure des données :**
```php
protected array $styleMapping = [
    'fas' => 'fa-solid',
    'far' => 'fa-regular',
    'fal' => 'fa-light',   // Pro
    'fad' => 'fa-duotone', // Pro
    // etc.
];
```

**Méthodes principales :**
- `mapStyle()` - Conversion style simple
- `mapStyleWithFallback()` - Avec fallback selon licence
- `isProStyle()` - Détection style Pro
- `convertFullClass()` - Conversion classe CSS complète

## Analyse des limitations actuelles

### 1. **Architecture monolithique**
- Tout codé en dur pour FA5 → FA6 uniquement
- Aucune extensibilité pour d'autres versions
- Logique métier mélangée avec données

### 2. **Données statiques**
- Mappings hardcodés dans le code
- Pas de séparation données/logique
- Difficile à maintenir et étendre

### 3. **Absence d'abstraction**
- Pas d'interfaces communes
- Services couplés à une version spécifique
- Pas de polymorphisme

### 4. **Couverture partielle**
- Mappings FA5→6 incomplets (91 icônes seulement)
- Aucun support FA4→5 ou FA6→7
- Logique de détection de version absente

## Besoins identifiés pour l'architecture multi-versions

### 1. **Interface commune**
```php
interface VersionMapperInterface {
    public function getIconMappings(): array;
    public function getStyleMappings(): array;
    public function getDeprecatedIcons(): array;
    public function getProOnlyIcons(): array;
    public function mapIcon(string $icon): array;
    public function mapStyle(string $style): string;
}
```

### 2. **Factory Pattern**
```php
class MigrationVersionManager {
    public function createMapper(string $fromVersion, string $toVersion): VersionMapperInterface;
    public function detectVersion(string $content): string;
    public function getSupportedMigrations(): array;
}
```

### 3. **Séparation données/logique**
- Fichiers de configuration par version
- Chargement dynamique des mappings
- Validation et cache des données

### 4. **Mappers spécialisés**
- `FontAwesome4To5Mapper`
- `FontAwesome5To6Mapper` (refactor actuel)
- `FontAwesome6To7Mapper`

## Plan de refactoring proposé

### Phase 1 : Création des interfaces
1. `VersionMapperInterface`
2. `VersionDetectorInterface`
3. `MigrationConfigInterface`

### Phase 2 : Refactor services existants
1. Extraire données vers fichiers config
2. Implémenter interface sur `IconMapper`/`StyleMapper`
3. Créer `FontAwesome5To6Mapper` composite

### Phase 3 : Nouveaux mappers
1. `FontAwesome4To5Mapper` avec données recherchées
2. `FontAwesome6To7Mapper` avec données recherchées
3. Tests unitaires pour chaque mapper

### Phase 4 : Service central
1. `MigrationVersionManager` avec factory
2. `VersionDetector` pour détection automatique
3. Intégration dans les commandes existantes

### Phase 5 : Configuration multi-versions
1. Fichiers config séparés par version
2. Interface web pour sélection versions
3. Validation et migration des configs existantes

## Compatibilité avec l'existant

**Points à préserver :**
- API des méthodes `mapIcon()` et `mapStyle()`
- Configuration actuelle `fontawesome-migrator.php`
- Interface web actuelle
- Commandes Artisan existantes

**Évolutions prévues :**
- Nouvelles options `--from=X --to=Y`
- Sélecteur de version dans interface web
- Configuration étendue pour multi-versions
- Rapports enrichis avec info version source/target