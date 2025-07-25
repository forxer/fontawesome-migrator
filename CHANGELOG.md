CHANGELOG
=========

1.1.0 (2025-07-25)
------------------

### Added
- Migration des assets FontAwesome (CDN, NPM packages, JS imports, CSS)
- Support Pro/Free pour les assets avec détection automatique
- Nouveaux modes : `--icons-only`, `--assets-only` (migration complète par défaut)
- Section assets dans les rapports avec statistiques détaillées
- Service AssetMigrator pour la gestion modulaire des assets
- Interface web de gestion des rapports à `/fontawesome-migrator/reports`
- ReportsController avec API REST pour CRUD et nettoyage automatique

### Changed
- Rapports générés dans `storage/app/public` avec URLs web accessibles
- API `generateReport()` retourne maintenant un tableau avec paths et URLs
- Migration complète (icônes + assets) activée par défaut
- Commande affiche maintenant l'URL du menu des rapports

### Fixed  
- Affichage détaillé des rapports avec URLs clickables dans la commande
- Support du type 'asset' dans les statistiques et rapports HTML/JSON



1.0.0 (2025-07-25)
------------------

- First release
