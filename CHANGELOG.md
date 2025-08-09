CHANGELOG
=========

2.0.0-DEV (2025-08-XX)
------------------

**🚧 EN DÉVELOPPEMENT** - Architecture multi-versions et configuration JSON

### Breaking Changes
- **🔄 Migration Scope**: Extension de FA5→6 vers multi-versions FA4→5→6→7 (nouvelles options requises)
- **Configuration Structure**: Mappings externalisés en JSON (fallbacks assurés pour compatibilité)
- **Command Options**: Ajout `--from` et `--to` (ancienne syntaxe reste supportée)
- **Package Description**: Repositionnement comme solution multi-versions (impact marketing)
- **Docker Documentation**: Clarification environnement AXN Informatique (impact utilisateurs externes)
- **Command Architecture**: Suppression des constructors avec injection de dépendances dans les commandes Artisan
- **Dependency Injection**: Migration vers l'injection de dépendances dans la méthode `handle()`
- **Metadata Architecture**: Refonte complète de la gestion des métadonnées avec séparation du reporting
- **MigrationReporter API**: Suppression des méthodes `setDryRun()` et `setMigrationOptions()` - remplacées par injection de `MetadataManager`
- **Report Generation**: Suppression de l'option `--report` et configuration `generate_report` - rapports automatiques via métadonnées

### Added
- **🎯 Multi-Version Architecture**: Support complet FA4→5→6→7 avec détection automatique de version
- **MigrationVersionManager**: Gestionnaire central pour orchestrer les migrations multi-versions
- **Specialized Mappers**: FontAwesome4To5Mapper, FontAwesome5To6Mapper, FontAwesome6To7Mapper avec logique spécifique
- **ConfigurationLoader Service**: Système de chargement JSON avec cache et fallbacks hardcodés
- **JSON Configuration System**: Mappings externalisés dans `config/fontawesome-migrator/mappings/`
- **Version-Specific Commands**: Options `--from` et `--to` pour migrations ciblées (ex: `--from=4 --to=7`)
- **Interactive Version Selector**: Interface web `/tests` avec configurateur multi-versions temps réel
- **Dynamic Migration Validation**: Vérification en temps réel des combinaisons de versions supportées
- **Complete Documentation Suite**: Guide multi-versions, API reference, Quick reference dans `/docs`
- **Source Traceability**: Traçabilité CLI/Web dans métadonnées et rapports pour audit complet
- **MetadataManager Service**: Nouveau service centralisé pour la gestion des métadonnées de migration
- **Separated Metadata Files**: Sauvegarde automatique des métadonnées dans des fichiers JSON séparés
- **Enhanced Metadata Structure**: Métadonnées enrichies avec migration, environment, runtime, backups, statistics
- **Real-time Data Collection**: Collecte des sauvegardes et statistiques en temps réel pendant la migration
- **Metadata Persistence**: Sauvegarde automatique des métadonnées avec migration ID unique
- **Homepage**: Page d'accueil avec dashboard statistiques et actions rapides
- **Navigation System**: Menu de navigation et fil d'ariane sur toutes les pages
- **Short Migration IDs**: Affichage simplifié des IDs de migration (8 caractères)
- **Unified Architecture**: Organisation cohérente des partials CSS/JS à la racine
- **FontAwesome 7.0.0**: Migration complète vers FontAwesome 7.0.0 avec CDN officiel
- **Modern UI Design**: Remplacement systématique des emojis par icônes FontAwesome sémantiquement cohérentes
- **Bubble Animation System**: Animation de bulles optimisée avec performance GPU (translate3d)
- **Mixed Icon Styles**: Équilibre intelligent entre fa-regular et fa-solid selon disponibilité
- **Bootstrap 5.3.7 Migration**: Migration complète de l'interface vers Bootstrap 5 avec composants natifs
- **Laravel Breadcrumbs**: Intégration du package diglactic/laravel-breadcrumbs pour navigation contextuelle
- **Bootstrap Components**: Utilisation exclusive des composants Bootstrap (Cards, Tables, Navbar, etc.)
- **Performance Optimization**: Suppression de Chart.js et optimisation CSS/JS inline

### Changed
- **🔄 Migration Strategy**: Passage de FA5→6 uniquement vers architecture multi-versions FA4→5→6→7
- **Configuration Architecture**: Migration des mappings hardcodés vers fichiers JSON externalisés
- **Mapper Classes**: Refactoring complet avec ConfigurationLoader et fallbacks de compatibilité
- **Command Enhancement**: Options `--from` et `--to` ajoutées aux commandes de migration
- **Web Interface**: Configurateur `/tests` étendu avec sélecteur de versions interactif
- **Documentation Structure**: Réorganisation complète avec index et guides spécialisés
- **Project Description**: Package positionné comme solution multi-versions professionnelle
- **Docker Context**: Clarification environnement `d-packages-exec` comme propriétaire AXN Informatique
- **MigrateCommand**: Services injectés via `handle(FileScanner, IconReplacer, MigrationReporter, AssetMigrator, MetadataManager)`
- **BackupCommand**: Service IconReplacer injecté via `handle(IconReplacer)` et assigné à la propriété de classe
- **MigrationReporter**: Constructor injection du `MetadataManager` pour consommer les métadonnées séparées
- **Report Generation**: Rapports HTML/JSON enrichis avec métadonnées complètes (environment, migration, backups)
- **Storage Path**: `backup_path` → `migrations_path` (`storage/app/fontawesome-migrator`)
- **Report Naming**: Fichiers sans suffixe date/heure pour organisation par migration
- **RESTful Routes**: Toutes les sections utilisent le pattern `index`/`show` cohérent
- **CSS Architecture**: Partials réorganisés avec séparation commun/spécifique

### Enhanced
- **Metadata Traceability**: Traçabilité complète avec migration ID, timestamps, durée de migration
- **Backup Integration**: Collecte automatique des informations de sauvegarde en temps réel
- **Statistics Calculation**: Calcul et stockage automatique des statistiques de migration
- **Report Enrichment**: Rapports enrichis avec métadonnées séparées et données d'environnement

### Multi-Version Features
- **🎯 Intelligent Version Detection**: Analyse automatique du code pour identifier FA4, FA5, FA6 ou FA7
- **Specialized Migration Paths**: Logique dédiée pour chaque migration (4→5, 5→6, 6→7)
- **JSON Configuration Files**: Structure `config/fontawesome-migrator/mappings/{version}/`
- **Cached Loading**: ConfigurationLoader avec cache pour performance optimale
- **Fallback System**: Compatibilité assurée avec mappings hardcodés si JSON indisponible
- **Version-Specific Options**: Commandes CLI avec ciblage précis des versions
- **Web Version Selector**: Interface graphique pour sélectionner source et cible
- **Migration Validation**: Vérification des combinaisons supportées en temps réel
- **Complete Documentation**: Guide complet pour chaque type de migration
- **Breaking Changes Info**: Documentation des changements majeurs par version

### Migration-Based Features
- **Migration-Based Backup Architecture**: Nouvelle organisation des sauvegardes par migration
- **Migration Directories**: Chaque migration crée son propre répertoire `migration-migration_xxxxx/`
- **Metadata Integration**: Fichier `metadata.json` intégré dans chaque répertoire de migration
- **Web Testing Panel**: Interface web complète pour tester et déboguer les migrations (`/fontawesome-migrator/tests`)
- **Migration Management**: API complète pour lister, inspecter et nettoyer les migrations de migration
- **Advanced Migration Inspection**: Inspection détaillée des migrations avec métadonnées et fichiers de sauvegarde
- **Migration Cleanup**: Nettoyage automatique des migrations anciennes avec seuils configurables

### Web Interface Enhancements
- **Test Panel Interface**: Panneau de test interactif avec boutons pour tous les types de migration
- **Real-time Testing**: Exécution des commandes Artisan directement depuis l'interface web
- **Migration Statistics**: Dashboard complet des statistiques de migrations et sauvegardes
- **Migration Inspector**: Modal d'inspection détaillée des migrations avec JSON viewer
- **Interactive Cleanup**: Boutons de nettoyage pour les migrations avec confirmation
- **Migration Navigation**: Navigation fluide entre liste des migrations et inspection détaillée

### Architecture Improvements
- **Migration-Directory Structure**: `storage/app/fontawesome-backups/migration-migration_xxxxx/`
- **Integrated Metadata**: Métadonnées sauvegardées dans le répertoire de migration
- **Git Integration**: Fichiers `.gitignore` automatiques dans chaque migration
- **Migration Traceability**: Traçabilité parfaite entre métadonnées et sauvegardes
- **API Enhancement**: Nouvelles méthodes `getAvailableMigrations()`, `cleanOldMigrations()`, `getMigrationDirectory()`

### Testing Infrastructure
- **Web-based Testing**: Tests complets via interface web sans ligne de commande
- **Multiple Test Types**: Dry-run, icons-only, assets-only, migrations réelles
- **Real-time Feedback**: Sortie des commandes en temps réel dans l'interface
- **Migration Creation**: Création automatique de migrations lors des tests
- **Debug Capabilities**: Outils de débogage intégrés pour diagnostiquer les problèmes

### Technical Architecture
- **🏗️ Multi-Version Engine**: MigrationVersionManager orchestrant les mappers spécialisés
- **JSON Configuration System**: ConfigurationLoader avec cache Redis-style et fallbacks
- **Specialized Mappers**: Classes dédiées par migration avec logique métier spécifique
- **Version Detection Algorithm**: Analyse des patterns pour identifier automatiquement la version
- **Migration Orchestration**: Coordination des étapes par le MigrationVersionManager
- **Configuration Externalization**: Séparation complète mappings/code pour maintenance
- **Fallback Compatibility**: Système de fallback pour compatibilité ascendante
- **Documentation Generation**: Système automatisé pour guides et références
- **Service Management**: Gestion des services via propriétés de classe assignées dans `handle()`
- **Laravel Pattern**: Adoption du pattern d'injection Laravel dans les méthodes plutôt que constructors
- **Metadata Architecture**: Architecture séparée MetadataManager → MigrationReporter
- **Data Structure**: Structure de métadonnées unifiée avec migration, environment, runtime, backups, statistics
- **File Organization**: Organisation par migration avec métadonnées intégrées
- **ServiceProvider Fix**: Correction de l'enregistrement des commandes pour `Artisan::call()` depuis le web

### Package Status
- **✅ Production Ready**: Version 2.0.0 fonctionnellement complète et robuste
- **🎯 Professional Quality**: Architecture moderne avec design Bootstrap 5
- **📚 Complete Documentation**: Guide utilisateur, API reference, documentation Docker
- **🔧 Extensible Architecture**: Configuration JSON pour personnalisations avancées
- **⚡ Performance Optimized**: Cache, CSS/JS inline, architecture responsive


1.7.0 (2025-07-29)
------------------

- Version fonctionnelle de démonstration (v2 en cours de développement...)


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
