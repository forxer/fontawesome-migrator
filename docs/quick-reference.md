# FontAwesome Migrator - Référence Rapide

## Commandes essentielles

```bash
# Migration interactive complète
php artisan fontawesome:migrate

# Migration spécifique avec dry-run
php artisan fontawesome:migrate --from=5 --to=6 --dry-run

# Migration avec verbosité
php artisan fontawesome:migrate --from=4 --to=5 --verbose

# Configuration
php artisan fontawesome:config --show
```

## Interface web

| URL | Description |
|-----|-------------|
| `/fontawesome-migrator/` | Dashboard principal |
| `/fontawesome-migrator/tests` | **Configurateur multi-versions** |
| `/fontawesome-migrator/reports` | Rapports de migration |

## Migrations supportées

```
FA4 ──→ FA5 ──→ FA6 ──→ FA7
```

### Transformations principales

| Version | Changements clés |
|---------|------------------|
| **4→5** | `fa` → `fas`, suppression `-o`, styles Pro |
| **5→6** | `fas` → `fa-solid`, renommages massifs |
| **6→7** | Fixed width par défaut, `.woff2` uniquement |

## Exemples de transformation

### FA4 → FA5
```html
<!-- Avant -->
<i class="fa fa-envelope-o"></i>
<i class="fa fa-home"></i>

<!-- Après -->
<i class="far fa-envelope"></i>
<i class="fas fa-house"></i>
```

### FA5 → FA6
```html
<!-- Avant -->
<i class="fas fa-home"></i>
<i class="far fa-envelope"></i>

<!-- Après -->
<i class="fa-solid fa-house"></i>
<i class="fa-regular fa-envelope"></i>
```

### FA6 → FA7
```html
<!-- Avant -->
<i class="fa-solid fa-user-large"></i>
<i class="fa-solid fa-fw fa-icon"></i>

<!-- Après -->
<i class="fa-solid fa-user"></i>
<i class="fa-solid fa-icon"></i> <!-- fa-fw supprimé -->
```

## Configuration JSON

```
config/fontawesome-migrator/mappings/
├── 4-to-5/ (styles, icons, deprecated, pro-only, new-icons)
├── 5-to-6/ (styles, icons)
└── 6-to-7/ (styles, icons, deprecated)
```

## Dépannage rapide

| Problème | Solution |
|----------|----------|
| Migration non supportée | Utiliser migrations intermédiaires |
| Config manquante | Système de fallback automatique |
| Icônes Pro en Free | Fallbacks automatiques activés |
| Erreur parsing | Utiliser `--dry-run` d'abord |

## Architecture technique

- **MigrationVersionManager** : Gestionnaire central
- **ConfigurationLoader** : Chargement JSON avec cache
- **FontAwesome[X]To[Y]Mapper** : Mappers spécialisés
- **Interface web** : Sélecteur interactif `/tests`