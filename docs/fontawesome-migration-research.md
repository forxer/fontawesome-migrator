# Recherche des changements FontAwesome par version

## FontAwesome 4 → 5

### Changements majeurs

**Système de préfixes :**
- **Version 4** : Un seul préfixe `fa`
- **Version 5** : Quatre préfixes pour différents styles :
  - `fas` - FontAwesome Solid (icônes pleines)
  - `far` - FontAwesome Regular (icônes contour)
  - `fal` - FontAwesome Light (Pro uniquement)
  - `fab` - FontAwesome Brands (marques)

**Icônes avec contour (-o) :**
Toutes les icônes avec style contour (se terminant par `-o`) :
- Passent au style Regular (`far`)
- Perdent leur suffixe `-o`
- Exemples :
  - `fa fa-envelope-o` → `far fa-envelope`
  - `fa fa-star-o` → `far fa-star`
  - `fa fa-heart-o` → `far fa-heart`

**Icônes standard :**
Les icônes sans suffixe passent au style Solid :
- `fa fa-cog` → `fas fa-cog`
- `fa fa-home` → `fas fa-home`
- `fa fa-user` → `fas fa-user`

**Rétrocompatibilité :**
- FontAwesome 5 n'est **pas** rétrocompatible avec v4
- Fourniture de shims (v4-shims.css/js) pour mapper automatiquement
- Suppression des alias en v5 - chaque icône a un nom officiel unique

### Outils de migration
- Script Python : `giovdi/fontawesome-4-to-5` sur GitHub
- Shims officiels FontAwesome
- Solutions jQuery/JavaScript pour transition automatique

---

## FontAwesome 6 → 7

### Changements majeurs v7

**Changements techniques :**
- **Dart Sass uniquement** : Abandon de node-sass, libsass, @import
- **Abandon Less CSS** : Migration complète vers SCSS moderne
- **Format webfont** : Uniquement `.woff2` (moderne)
- **Vue.js** : Support Vue 3+ uniquement
- **React** : Suppression du dynamic importing

**Nouveautés comportementales :**
- **Fixed Width par défaut** : Icônes largeur fixe automatiquement (ancien `fa-fw` déprécié)
- **Accessibilité** : Icônes décoratives par défaut (cachées aux lecteurs d'écran)
- **SVG** : `fill=currentColor` par défaut, pas de stylesheet globale

**Fonctionnalités supprimées :**
- Support jQuery
- Python Django Plugin
- Ruby on Rails Gem
- Rails avec Turbolinks
- Require.js
- Classe `sr-only` (remplacée par `aria-label`)

**Icônes renommées (exemples) :**
- `user-large` → `user`
- `headphones-simple` → `headphones`
- `handshake-simple` → `handshake`

**Nouveautés :**
- **Pro+ icon packs** : Collections d'icônes curées
- **CSS custom properties** améliorées
- **Propriétés logiques CSS**
- **Optimisations performance**

### Rétrocompatibilité
- **Maintenue avec v6 et v5** : Traduction automatique des icônes existantes
- **Aliases conservés** : Les anciens noms fonctionnent encore

---

## Patterns de migration identifiés

### 4 → 5 : Restructuration complète
1. **Préfixe unique → Multiple** : `fa` → `fas/far/fal/fab`  
2. **Suffixes `-o`** : Suppression + passage en `far`
3. **Breaking changes** : Incompatibilité complète sans shims

### 5 → 6 : Evolution incrémentale  
1. **Noms d'icônes** : Nombreux renommages mais aliases conservés
2. **Nouvelles icônes** : Ajouts massifs
3. **Styles** : Améliorations esthétiques
4. **Compatibilité** : Maintenue avec shims

### 6 → 7 : Modernisation technique
1. **Infrastructure** : Migration Dart Sass, formats modernes
2. **Comportement** : Fixed width par défaut, accessibilité repensée
3. **Ecosystem** : Abandon support anciens frameworks
4. **Performance** : Optimisations SVG et CSS

---

## Implications pour l'architecture du package

### Multi-versions nécessaire
- **4→5** : Transformation majeure (préfixes + noms)
- **5→6** : Mapping noms (avec current mappings à étendre)
- **6→7** : Adaptations comportementales + renommages

### Stratégies par version
- **4→5** : Parser + transformer préfixes + mapper noms
- **5→6** : Mapper noms renommés (architecture actuelle)
- **6→7** : Adapter nouveaux comportements + mapper noms

### Détection automatique version
Patterns à détecter dans les fichiers :
- **v4** : `fa fa-*` sans préfixe style
- **v5** : `fas fa-*`, `far fa-*`, etc.
- **v6** : Noms spécifiques v6, CDN v6
- **v7** : Nouveaux noms v7, comportements v7