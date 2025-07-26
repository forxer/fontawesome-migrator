CHANGELOG
=========

1.2.0 (2025-07-25)
------------------

### Added
- Design system unifié pour toutes les interfaces web
- Traçabilité complète des configurations et options de migration dans les rapports
- Section configuration de migration dans les rapports avec options et environnement
- Extraction dynamique de version depuis CHANGELOG.md
- Support des fichiers individuels dans scan_paths (en plus des répertoires)

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
