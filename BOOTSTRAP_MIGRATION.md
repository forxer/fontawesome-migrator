# Bootstrap Migration - Documentation de travail

## 📁 Sauvegardes
**Répertoire de sauvegarde** : `/home/vincent/packages-dev/fontawesome-migrator/backup-bootstrap-migration/`

### Fichiers sauvegardés (2025-07-31)
- `layout.blade.php.bak`
- `partials/` (tous les CSS et JS)
- `home/`
- `reports/`
- `sessions/`
- `tests/`

## 📋 Plan de migration mémorisé

### Phase A : Intégration Bootstrap (EN COURS)
- [ ] Intégrer Bootstrap 5.3.3 CDN
- [ ] Intégrer Bootstrap Icons 1.11.3
- [ ] Analyser conflits CSS

### Phase B : Migration composants
- [ ] Navbar
- [ ] Breadcrumb
- [ ] Container
- [ ] Boutons, badges, cards, tables

### Phase C : Migration icônes
Mapping FontAwesome → Bootstrap Icons :
- `fa-arrows-rotate` → `bi-arrow-repeat`
- `fa-file` → `bi-file-text`
- `fa-folder` → `bi-folder`
- `fa-flask` → `bi-flask`
- `fa-house` → `bi-house`
- `fa-clock` → `bi-clock`
- `fa-eye` → `bi-eye`
- `fa-trash-can` → `bi-trash`
- `fa-chart-bar` → `bi-bar-chart`
- `fa-gear` → `bi-gear`

### Phase D : Nettoyage
- [ ] Supprimer CSS custom obsolète
- [ ] Retirer FontAwesome CDN
- [ ] Optimiser JavaScript

## ⚠️ Notes importantes
- Toujours créer une sauvegarde avant modification
- Garder FontAwesome temporairement pendant la migration
- Tester sur mobile après chaque changement