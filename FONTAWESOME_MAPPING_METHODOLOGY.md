# Méthodologie FontAwesome Mappings Generator

## Script PHP réutilisable : `scripts/generate-mappings.php`

### Usage de base

```bash
# Générer mappings FA5→6 avec fichier manuel
php scripts/generate-mappings.php --from=5 --to=6 --output=config/mappings/5-to-6/icons.json --manual=raw-mappings.json

# Valider un fichier existant
php scripts/generate-mappings.php --validate-only --from=5 --to=6 --output=config/mappings/5-to-6/icons.json

# Afficher l'aide
php scripts/generate-mappings.php --help
```

### Processus complet utilisé le 2025-08-14 pour FA5→6

#### 1. Préparation des données sources
- **WebFetch** des sources officielles FontAwesome
- Compilation manuelle des mappings dans un fichier temporaire

#### 2. Génération automatisée
```bash
# Avec les données compilées
php scripts/generate-mappings.php \
  --from=5 \
  --to=6 \
  --output=config/fontawesome-migrator/mappings/5-to-6/icons.json \
  --manual=fa5-to-fa6-raw.json
```

#### 3. Fonctionnalités du script

**Nettoyage automatique** :
- Supprime les doublons (clé = valeur)
- Valide la syntaxe JSON
- Optimise la structure

**Validation intégrée** :
- Vérifie les mappings critiques connus
- Affiche un rapport de validation
- Détecte les incohérences

**Sources documentées** :
- Intègre automatiquement les sources officielles
- Ajoute la méthodologie utilisée
- Horodate la génération

#### 4. Format des fichiers manuels acceptés

**JSON** (`raw-mappings.json`) :
```json
{
  "renamed_icons": {
    "fa-old": "fa-new",
    "fa-home": "fa-house"
  }
}
```

**Texte** (`raw-mappings.txt`) :
```
"fa-old": "fa-new"
"fa-home": "fa-house"
```

#### 5. Résultats FA5→6 obtenus

- **Entrée** : 823 mappings bruts
- **Doublons supprimés** : 513
- **Sortie finale** : 310 mappings valides
- **Validation** : 6/6 mappings critiques ✅

#### 6. Structure de sortie générée

```json
{
  "description": "Mappings des icônes FontAwesome 5 vers 6",
  "version": "5-to-6",
  "renamed_icons": {
    "fa-home": "fa-house",
    "fa-search": "fa-magnifying-glass"
  },
  "notes": {
    "count": "310 icônes réellement renommées (doublons supprimés)",
    "compatibility": "FA6 maintient des alias pour la rétrocompatibilité"
  },
  "sources": {
    "official_documentation": "https://docs.fontawesome.com/v6/web/setup/upgrade/whats-changed#icons-renamed-in-version-6",
    "generated_by": "FontAwesome Mapping Generator Script (PHP)",
    "generated_date": "2025-08-14",
    "methodology": "Compilation manuelle + nettoyage automatisé + validation"
  }
}
```

### Réutilisation pour autres versions

#### FA4→5
```bash
# 1. Collecter les mappings depuis la doc officielle FA5
# 2. Sauver dans fa4-to-fa5-raw.json
# 3. Générer
php scripts/generate-mappings.php --from=4 --to=5 --output=config/mappings/4-to-5/icons.json --manual=fa4-to-fa5-raw.json
```

#### FA6→7
```bash
# 1. WebFetch de https://docs.fontawesome.com/upgrade/whats-changed#icons-renamed-in-version-7
# 2. Compiler dans fa6-to-fa7-raw.json  
# 3. Générer
php scripts/generate-mappings.php --from=6 --to=7 --output=config/mappings/6-to-7/icons.json --manual=fa6-to-fa7-raw.json
```

### Sources officielles intégrées

Le script inclut automatiquement les sources pour chaque version :

- **FA4→5** : Documentation upgrade FA5, GitHub metadata
- **FA5→6** : Documentation FA6, GitHub metadata, blog migration, gist communautaire
- **FA6→7** : Documentation FA7, GitHub metadata

### Commandes de maintenance

```bash
# Valider tous les mappings existants
find config/mappings -name "icons.json" -exec php scripts/generate-mappings.php --validate-only --from=X --to=Y --output={} \;

# Régénérer après mise à jour des sources
php scripts/generate-mappings.php --from=5 --to=6 --output=config/mappings/5-to-6/icons.json --manual=updated-fa5-to-fa6.json

# Vérifier l'intégrité JSON
php -r "json_decode(file_get_contents('config/mappings/5-to-6/icons.json')); echo 'JSON valide\n';"
```

### Points d'attention

1. **Sources officielles obligatoires** - Toujours partir de la documentation FontAwesome
2. **Mappings critiques** - Le script valide automatiquement les renommages importants
3. **Nettoyage systématique** - Les doublons sont supprimés automatiquement
4. **Traçabilité complète** - Sources et méthodologie documentées dans le fichier final
5. **Validation avant usage** - Tester sur échantillon réel avant déploiement production

### Extension du script

Pour ajouter de nouvelles versions, modifier dans `generate-mappings.php` :

```php
private array $officialSources = [
    '7-to-8' => [
        'documentation' => 'https://docs.fontawesome.com/v8/upgrade/whats-changed',
        'github' => 'https://github.com/FortAwesome/Font-Awesome/blob/8.x/metadata/icons.json'
    ]
];

private array $criticalMappings = [
    '7-to-8' => [
        'fa-new-icon' => 'fa-updated-icon'
    ]
];
```

---
**Généré le** : 2025-08-14  
**Script** : `scripts/generate-mappings.php`  
**Contexte** : FontAwesome Migrator v2.0