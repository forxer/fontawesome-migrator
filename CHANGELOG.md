CHANGELOG
=========

2.0.0 (2025-07-XX)
------------------

### Breaking Changes
- **Command Architecture**: Suppression des constructors avec injection de dépendances dans les commandes Artisan
- **Dependency Injection**: Migration vers l'injection de dépendances dans la méthode `handle()`
- **Metadata Architecture**: Refonte complète de la gestion des métadonnées avec séparation du reporting
- **MigrationReporter API**: Suppression des méthodes `setDryRun()` et `setMigrationOptions()` - remplacées par injection de `MetadataManager`

### Added
- **MetadataManager Service**: Nouveau service centralisé pour la gestion des métadonnées de migration
- **Separated Metadata Files**: Sauvegarde automatique des métadonnées dans des fichiers JSON séparés
- **Enhanced Metadata Structure**: Métadonnées enrichies avec session, environment, runtime, backups, statistics
- **Real-time Data Collection**: Collecte des sauvegardes et statistiques en temps réel pendant la migration
- **Metadata Persistence**: Sauvegarde automatique des métadonnées avec session ID unique

### Changed
- **MigrateCommand**: Services injectés via `handle(FileScanner, IconReplacer, MigrationReporter, AssetMigrator, MetadataManager)`
- **BackupCommand**: Service IconReplacer injecté via `handle(IconReplacer)` et assigné à la propriété de classe
- **MigrationReporter**: Constructor injection du `MetadataManager` pour consommer les métadonnées séparées
- **Report Generation**: Rapports HTML/JSON enrichis avec métadonnées complètes (environment, session, backups)

### Enhanced
- **Metadata Traceability**: Traçabilité complète avec session ID, timestamps, durée de migration
- **Backup Integration**: Collecte automatique des informations de sauvegarde en temps réel
- **Statistics Calculation**: Calcul et stockage automatique des statistiques de migration
- **Report Enrichment**: Rapports enrichis avec métadonnées séparées et données d'environnement

### Technical
- **Service Management**: Gestion des services via propriétés de classe assignées dans `handle()`
- **Laravel Pattern**: Adoption du pattern d'injection Laravel dans les méthodes plutôt que constructors
- **Metadata Architecture**: Architecture séparée MetadataManager → MigrationReporter
- **Data Structure**: Structure de métadonnées unifiée avec session, environment, runtime, backups, statistics
- **File Organization**: Fichiers séparés : rapport HTML + rapport JSON + métadonnées JSON


1.6.0 (2025-07-29)
------------------

### Added
- **Backup Management Command**: Nouvelle commande `php artisan fontawesome:backup` pour gérer les sauvegardes
- **Interactive Backup Operations**: Interface interactive pour lister, restaurer, nettoyer et analyser les sauvegardes
- **Backup Restore Functionality**: Restauration de fichiers depuis leurs sauvegardes avec sélection de timestamp
- **Automated Backup Cleanup**: Nettoyage automatique des anciennes sauvegardes avec paramétrage du nombre de jours
- **Backup Statistics Dashboard**: Informations détaillées sur les sauvegardes (nombre, taille, fichiers)
- **Backup Tracking**: Suivi des sauvegardes créées pendant la migration
- **Enhanced Reports**: Affichage des sauvegardes dans les rapports HTML/JSON avec détails complets
- **Backup Section**: Nouvelle section dédiée dans les rapports avec navigation
- **Storage Protection**: Création automatique de fichiers `.gitignore` dans les répertoires de storage
- **Backup Statistics**: Compteur de sauvegardes dans le résumé de configuration
- **Backup Details Display**: Affichage du nom, date, taille et chemin de chaque sauvegarde

### Enhanced
- **Migration Traceability**: Traçabilité complète des fichiers sauvegardés
- **Report Completeness**: Rapports enrichis avec informations de sauvegarde
- **Git Safety**: Protection automatique contre le versioning des fichiers générés
- **User Experience**: Interface utilisateur enrichie avec guidance pour exploitation des sauvegardes

### Technical
- **BackupCommand**: Nouvelle commande Artisan avec architecture modulaire et mode interactif
- **DirectoryHelper**: Classe utilitaire refactorisée pour gestion unifiée des répertoires et `.gitignore`
- **Code Refactoring**: Élimination de la duplication dans la création de répertoires
- **Service Integration**: Intégration complète avec `IconReplacer` pour les opérations de backup
- **Backup Data Structure**: Structure de données unifiée pour les informations de sauvegarde
- **Reporter Enhancement**: Extension du `MigrationReporter` avec support des sauvegardes
- **Command Architecture**: Collecte et transmission des données de sauvegarde entre services
- **CSS Styling**: Nouveaux styles pour la section des sauvegardes avec design responsive


1.5.0 (2025-07-29)
------------------

### Added
- **Configuration Management**: Nouvelle commande `php artisan fontawesome:config`
- **Interactive Config Menu**: Affichage, édition, validation et sauvegarde de configuration
- **Granular Editing**: Modification des licences, chemins, extensions et patterns d'exclusion
- **Config Validation**: Vérification automatique des chemins et cohérence
- **Config Backup**: Sauvegarde avant modifications importantes
- **Pro Styles Management**: Gestion styles Pro avec validation de licence
- **Large Project UX**: Optimisé pour les gros projets avec configurations multiples

### Enhanced
- **Developer Experience**: UX améliorée pour configuration de gros projets
- **Command Architecture**: Modes interactif/non-interactif unifiés
- **Error Handling**: Messages d'aide contextuels et validation temps réel

### Technical
- **ConfigureFontAwesomeCommand**: Nouvelle commande avec architecture modulaire
- **Service Provider**: Enregistrement automatique de la commande
- **Smart Persistence**: Sauvegarde intelligente des valeurs modifiées uniquement

1.4.0 (2025-07-28)
------------------

### Added
- **Laravel Prompts Integration**: Mode interactif par défaut pour toutes les commandes
- **Enhanced Install Command**: Configuration des patterns d'exclusion et chemins de scan personnalisés
- **Interactive Migration Command**: Interface guidée avec résumé de configuration et confirmation
- **Modular CSS Architecture**: Système de partials Blade pour une meilleure maintenabilité
- **Enhanced Web Interface**: Design system unifié avec CSS inline pour maximum de fiabilité
- **Advanced Modal System**: Système de modales unifié avec JavaScript modulaire
- **Performance Metrics Dashboard**: Métriques calculées avec taux de succès et densité des changements
- **Table of Contents Navigation**: Navigation améliorée dans les rapports avec ancres
- **Search and Filtering**: Recherche temps réel avec surlignage des correspondances
- **Back-to-Top Button**: Bouton de retour en haut avec animations fluides
- **Warning Enhancement**: Affichage des avertissements avec numéros de ligne et détails enrichis
- **French Number Formatting**: Formatage des nombres selon les conventions françaises
- **Copy-to-Clipboard**: Fonctionnalité de copie des rapports formatés
- **Responsive Design**: Interface mobile-first avec breakpoints optimisés

### Enhanced
- **Install Command UX**: Prompts interactifs avec exemples contextuels et aide intégrée
- **Migration Command UX**: Sélection du mode de migration, configuration des sauvegardes, et résumé détaillé
- **CSS Architecture**: Factorisation et mutualization des styles dans un système de partials Blade modulaire
- **Web Reports Interface**: Design moderne avec gradients, animations et indicateurs visuels améliorés
- **Modal System**: Migration vers un système unifié avec meilleure gestion des événements
- **Help System**: Modales d'aide avec tips de test et explications détaillées des métriques

### Fixed
- **String Interpolation**: Correction des guillemets simples vers doubles pour les caractères d'échappement
- **Icon Display**: Correction de l'affichage des emojis dans les interfaces (suppression des dégradés CSS masquants)
- **CSS Conflicts**: Résolution des doublons et conflits entre fichiers de styles
- **Warning Correlation**: Amélioration de la corrélation entre warnings et changements avec extraction serveur
- **Badge Truncation**: Correction du tronquage des badges de nombre d'avertissements
- **Number Formatting**: Formatage cohérent des nombres en français dans toute l'interface

### Changed
- **Default Behavior**: Mode interactif activé par défaut (utiliser `--no-interactive` pour le mode classique)
- **CSS Organization**: Architecture modulaire avec common.blade.php, index.blade.php, et migration.blade.php
- **Dependencies**: Ajout de `laravel/prompts: ^0.3.0` pour l'interface interactive
- **Command Options**: Remplacement de `--interactive` par `--no-interactive` (logique inversée)
- **Warning Processing**: Migration du traitement client-side vers server-side pour plus de fiabilité
- **Minimum Requirements**: Laravel Prompts requis (ajouté automatiquement via composer)
- **Command Behavior**: Mode interactif par défaut peut affecter les scripts automatisés (utiliser `--no-interactive`)

### Technical
- **Laravel Prompts**: Intégration complète avec intro/outro, select/confirm, text input et progress indicators
- **Blade Partials**: Système de partials CSS pour une architecture maintenable et DRY
- **JavaScript Modularity**: Refactoring vers des modules JavaScript réutilisables et scoped
- **Server-Side Processing**: Migration du parsing DOM vers traitement PHP pour les warnings
- **CSS Custom Properties**: Utilisation extensive des variables CSS pour cohérence du design system
- **Animation System**: Animations CSS3 avec keyframes et transitions fluides


1.3.1 (2025-07-26)
------------------

### Fixed
- Tests MigrationReporter avec mock View::make() pour support des vues Blade
- Tests InstallFontAwesome en mode non-interactif pour éviter écriture configuration vide
- Tests ReportsController adaptés au nouveau système de vues Blade partagées
- Synchronisation getDefaultPaths() avec la configuration par défaut du package

### Changed
- Documentation complète mise à jour avec chiffres de tests exacts (78 tests, 243 assertions)
- CLAUDE.md enrichi avec fonctionnalités webpack.mix.js et support .json extension
- README.md avec exemples webpack.mix.js pour Laravel Mix
- STATUS.md actualisé avec version 1.3.1 et phase de développement asset migration

### Technical
- Résolution des conflits entre configuration par défaut et mode test non-interactif
- Amélioration de la robustesse des tests avec mocks appropriés pour l'environnement Blade
- Suppression du package.json des chemins par défaut pour éviter conflits de configuration


1.3.0 (2025-07-26)
------------------

### Added
- Extension .json dans les types de fichiers supportés (ordre alphabétique)

### Fixed
- Support complet des fichiers package.json pour migration des dépendances NPM

1.2.0 (2025-07-26)
------------------

### Added
- Design system unifié pour toutes les interfaces web
- Traçabilité complète des configurations et options de migration dans les rapports
- Section configuration de migration dans les rapports avec options et environnement
- Extraction dynamique de version depuis CHANGELOG.md
- Support des fichiers individuels dans scan_paths (en plus des répertoires)
- Support webpack.mix.js avec patterns pour fichiers JS FontAwesome individuels

### Changed
- Interface web unifiée avec layout partagé
- Rapports affichent maintenant la configuration utilisée (chemins, licence, options)
- ReportsController utilise toujours les vues Blade (suppression fallback HTML brut)

### Fixed
- Section configuration visible dans tous les rapports HTML (anciens et nouveaux)
- Version du package extraite automatiquement du CHANGELOG.md (plus de valeurs en dur)


1.1.0 (2025-07-25)
------------------

### Added
- Migration des assets FontAwesome (CDN, NPM packages, JS imports, CSS)
- Support Pro/Free pour les assets avec détection automatique
- Nouveaux modes : `--icons-only`, `--assets-only`
- Interface web de gestion des rapports à `/fontawesome-migrator/reports`
- Commande d'installation interactive `php artisan fontawesome:install`

### Changed
- Migration complète (icônes + assets) activée par défaut
- Rapports générés avec URLs web accessibles

### Fixed
- Affichage des rapports avec URLs clickables
- Support complet des assets dans les statistiques



1.0.0 (2025-07-25)
------------------

- First release
