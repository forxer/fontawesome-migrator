# Guide Docker pour fontawesome-migrator

Ce guide explique comment utiliser le package `fontawesome-migrator` dans un environnement Docker avec `d-packages-exec php84`.

## 🐳 Environnement Docker

### Prérequis

- Docker configuré avec `d-packages-exec php84`
- Accès aux commandes `d-packages-exec`
- Git pour cloner le projet

### Installation rapide

```bash
# 1. Cloner le projet
git clone https://github.com/forxer/fontawesome-migrator.git
cd fontawesome-migrator

# 2. Rendre le script exécutable
chmod +x test.sh

# 3. Lancer le script de test complet
./test.sh
```

## 🧪 Script de test automatisé

Le script `test.sh` effectue automatiquement :

1. ✅ **Vérification de l'environnement** - S'assure que `d-packages-exec` est disponible
2. ✅ **Installation des dépendances** - `composer install` avec PHP 8.4
3. ✅ **Tests unitaires** - Suite complète de tests PHPUnit
4. ✅ **Vérification du style** - Laravel Pint pour le formatage
5. ✅ **Modernisation du code** - Rector pour PHP moderne
6. ✅ **Test d'intégration** - Création d'un projet Laravel temporaire
7. ✅ **Test des commandes** - Validation des commandes Artisan
8. ✅ **Nettoyage automatique** - Suppression des fichiers temporaires

### Sortie du script

```
🧪 Test du package fontawesome-migrator
=======================================

📦 Installation des dépendances...
✅ Tests unitaires: RÉUSSIS
✅ Style de code: CONFORME
✅ Code moderne: OK
🚀 Test d'intégration avec Laravel...
✅ Commande dry-run: SUCCÈS
✅ Commande avec rapport: SUCCÈS

🎉 TOUS LES TESTS SONT RÉUSSIS !
=================================
```

## 🔧 Commandes manuelles

Si vous préférez exécuter les commandes individuellement :

### Tests et qualité
```bash
# Installation
d-packages-exec php84 composer install

# Tests unitaires
d-packages-exec php84 composer test
d-packages-exec php84 ./vendor/bin/phpunit

# Style de code
d-packages-exec php84 composer pint          # Corriger
d-packages-exec php84 composer pint-test     # Vérifier seulement

# Modernisation
d-packages-exec php84 composer rector        # Appliquer
d-packages-exec php84 composer rector-dry    # Prévisualiser

# Tout en un
d-packages-exec php84 composer quality
```

### Test d'intégration Laravel
```bash
# Créer un projet de test
mkdir test-laravel && cd test-laravel
d-packages-exec php84 composer create-project laravel/laravel . --prefer-dist

# Installer le package
d-packages-exec php84 composer config repositories.local path ../
d-packages-exec php84 composer require forxer/fontawesome-migrator:@dev

# Publier la configuration
d-packages-exec php84 php artisan vendor:publish --tag=fontawesome-migrator-config

# Créer des fichiers de test
echo '<i class="fas fa-home"></i><i class="fas fa-times"></i>' > resources/views/test.blade.php

# Tester la commande
d-packages-exec php84 php artisan fontawesome:migrate --dry-run
d-packages-exec php84 php artisan fontawesome:migrate --dry-run --verbose --report
```

## 🚀 Utilisation du package

### Dans un projet Laravel existant

```bash
# 1. Installer le package
d-packages-exec php84 composer require forxer/fontawesome-migrator

# 2. Publier la configuration
d-packages-exec php84 php artisan vendor:publish --tag=fontawesome-migrator-config

# 3. Configurer dans config/fontawesome-migrator.php
# 4. Lancer la migration
d-packages-exec php84 php artisan fontawesome:migrate --dry-run
d-packages-exec php84 php artisan fontawesome:migrate
```

### Options disponibles

```bash
# Prévisualisation (recommandé en premier)
d-packages-exec php84 php artisan fontawesome:migrate --dry-run

# Migration avec rapport détaillé
d-packages-exec php84 php artisan fontawesome:migrate --report --verbose

# Migration d'un dossier spécifique
d-packages-exec php84 php artisan fontawesome:migrate --path=resources/views

# Forcer les sauvegardes
d-packages-exec php84 php artisan fontawesome:migrate --backup
```

## 🛠️ Dépannage

### Erreur "d-packages-exec not found"
```bash
# Vérifier que vous êtes dans le bon environnement Docker
which d-packages-exec

# Si non disponible, contactez votre administrateur système
```

### Erreur de permissions
```bash
# Rendre le script exécutable
chmod +x test.sh

# Vérifier les permissions Docker
docker ps
```

### Tests qui échouent
```bash
# Nettoyer et réinstaller
rm -rf vendor/ composer.lock
d-packages-exec php84 composer install

# Relancer les tests
./test.sh
```

## 📝 Notes pour l'équipe

- **Script recommandé** : Utilisez `./test.sh` pour tous les tests
- **CI/CD** : Le script peut être intégré dans vos pipelines
- **Environnement** : Compatible avec votre configuration Docker existante
- **Performance** : Le script optimise les temps d'exécution avec des tests en parallèle

## 🔗 Liens utiles

- [README principal](README.md) - Documentation complète
- [CLAUDE.md](CLAUDE.md) - Guide pour l'IA Claude
- [Composer Scripts](composer.json) - Scripts disponibles