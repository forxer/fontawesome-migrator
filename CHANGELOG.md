CHANGELOG
=========

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
