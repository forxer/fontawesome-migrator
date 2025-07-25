CHANGELOG
=========

1.1.0 (2025-07-25)
------------------

### Added
- Migration des assets FontAwesome (CDN, NPM packages, JS imports, CSS)
- Support Pro/Free pour les assets avec détection automatique
- Nouveaux modes : `--icons-only`, `--assets-only`
- Interface web de gestion des rapports à `/fontawesome-migrator/reports`
- Commande d'installation interactive `php artisan fontawesome:install`
- Design system unifié pour toutes les interfaces web
- Traçabilité complète des configurations et options de migration dans les rapports

### Changed
- Migration complète (icônes + assets) activée par défaut
- Rapports générés avec URLs web accessibles
- Interface web unifiée avec layout partagé

### Fixed  
- Affichage des rapports avec URLs clickables
- Support complet des assets dans les statistiques
- Version du package rendue dynamique (plus de valeurs en dur)



1.0.0 (2025-07-25)
------------------

- First release
