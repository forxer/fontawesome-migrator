# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Important Development Constraints

**⚠️ PHP Execution Limitation**: Claude Code cannot execute PHP commands or any language interpreters (php, node, python, etc.). Only use Bash tool for basic system commands. Never attempt to run `php artisan`, `composer`, `npm`, or similar commands.

**🇫🇷 Tone and Communication Style**: 
- **Stay humble and factual** - Avoid pretentious terms like "révolutionnaire", "extraordinaire", "incroyable"
- **Don't oversell features** - Describe what the code does without exaggeration
- **Respect French culture** - "On n'aime pas ceux qui pètent plus haut qu'ils ont le cul"
- **Be respectful and modest** - We're in France, we respect people and stay grounded
- **Use simple, clear language** - Avoid marketing speak, focus on technical accuracy

## Project Overview

This is a Laravel package called `fontawesome-migrator` that automates the migration from Font Awesome 5 to Font Awesome 6 (both Free and Pro versions). The package scans Laravel applications for Font Awesome classes and automatically converts them to the new FA6 syntax.

**Target version**: Laravel 12.0+ with PHP 8.4+

## Development Commands

### Testing
```bash
# Run all tests
composer test

# Run tests with coverage report
composer test-coverage

# Run specific test suite
./vendor/bin/phpunit --testsuite=Unit
./vendor/bin/phpunit --testsuite=Feature

# Docker environment (with d-packages-exec)
d-packages-exec php84 composer test
./test.sh  # Automated full test suite
```

### Test Status
✅ **All tests passing**: 78 tests, 243 assertions, 0 failures, 0 errors
- Unit tests: IconMapper, StyleMapper, FileScanner, IconReplacer, AssetMigrator, MigrationReporter
- Feature tests: Complete Artisan command functionality including install wizard and web interface
- Integration tests: Laravel environment simulation with comprehensive asset migration support

### Code Quality
```bash
# Laravel Pint for code formatting
composer pint

# Check code style without fixing
composer pint-test

# Rector for automated refactoring and modernization
composer rector

# Rector dry-run (preview changes)
composer rector-dry

# Complete quality check (style + rector + tests)
composer quality
```

### Package Commands
```bash
# Interactive installation (recommended for first setup)
php artisan fontawesome:install

# Main migration command (icons + assets) - Interactive by default
php artisan fontawesome:migrate

# Non-interactive mode (classic)
php artisan fontawesome:migrate --no-interactive

# Dry-run mode (preview changes)
php artisan fontawesome:migrate --dry-run

# Migration modes
php artisan fontawesome:migrate --icons-only    # Icons only
php artisan fontawesome:migrate --assets-only   # Assets only (CSS, JS, CDN)

# Migrate specific path
php artisan fontawesome:migrate --path=resources/views

# Generate detailed report
php artisan fontawesome:migrate --report --verbose

# Configuration management (interactive by default)
php artisan fontawesome:config

# Configuration commands
php artisan fontawesome:config --show          # Display current configuration
php artisan fontawesome:config --reset         # Reset to defaults
php artisan fontawesome:config --no-interactive # Non-interactive mode
```

## Architecture

### Core Services Architecture

The package follows a service-oriented architecture with clear separation of concerns:

1. **FileScanner** (`src/Services/FileScanner.php`): Scans the Laravel application for files containing Font Awesome classes using Symfony Finder
2. **IconMapper** (`src/Services/IconMapper.php`): Contains mappings for renamed, deprecated, and Pro-only icons
3. **StyleMapper** (`src/Services/StyleMapper.php`): Handles style conversions (fas → fa-solid, etc.)
4. **IconReplacer** (`src/Services/IconReplacer.php`): Orchestrates the replacement process using the mappers
5. **AssetMigrator** (`src/Services/AssetMigrator.php`): Migrates FontAwesome assets (CSS, JS, CDN, package.json) with Pro/Free support
6. **MigrationReporter** (`src/Services/MigrationReporter.php`): Generates HTML and JSON reports using Blade views with shared layout system and comprehensive metadata tracking

### Command Structure

- **MigrateFontAwesomeCommand** (`src/Commands/MigrateFontAwesomeCommand.php`): Main Artisan command that coordinates the migration process with interactive mode by default
- **InstallFontAwesomeCommand** (`src/Commands/InstallFontAwesomeCommand.php`): Interactive installation command with configuration wizard
- **ConfigureFontAwesomeCommand** (`src/Commands/ConfigureFontAwesomeCommand.php`): Advanced configuration management command for large projects
- **ServiceProvider** (`src/ServiceProvider.php`): Laravel service provider for package registration and configuration publishing

### Web Interface Architecture

- **ReportsController** (`src/Http/Controllers/ReportsController.php`): REST API for reports management with CRUD operations, uses Blade views for HTML display
- **Layout View** (`resources/views/layout.blade.php`): Shared HTML layout with unified CSS design system using CSS custom properties
- **Index View** (`resources/views/reports/index.blade.php`): Modern reports listing interface with enhanced design, statistics overview, and responsive grid layout
- **Migration View** (`resources/views/reports/migration.blade.php`): Individual report display with interactive features, detailed statistics, change tracking, and comprehensive data visualization

### View Architecture & Design System

The package implements a **modern, unified design system** with complete inline CSS architecture for maximum reliability:

#### Enhanced Layout System (`resources/views/layout.blade.php`)
- **CSS Variables**: Consistent color palette, spacing, and typography using CSS custom properties
- **Component Styles**: Unified buttons, alerts, tables, cards, and form elements
- **Responsive Design**: Mobile-first approach with adaptive grid layouts
- **Design Tokens**: Standardized spacing (padding, margin), border-radius, shadows, and transitions

#### Modern View Components Architecture
1. **Enhanced Reports Index** (`resources/views/reports/index.blade.php`): 
   - **Complete inline CSS** for reliability (no external dependencies)
   - **Global statistics dashboard** with total reports, size, and activity metrics
   - **Modern card design** with gradients, animations, and hover effects
   - **Responsive grid layout** with auto-fit columns
   - **French number formatting** with proper locale conventions
   - **Enhanced empty state** with helpful guidance and command examples

2. **Interactive Migration Reports** (`resources/views/reports/migration.blade.php`):
   - **Complete inline CSS and JavaScript** for full functionality
   - **Chart.js integration** for data visualization (doughnut charts)
   - **Interactive collapsible sections** with smooth animations
   - **Advanced search and filtering** with real-time highlighting
   - **Performance metrics section** with calculated success rates
   - **Comprehensive recommendations system** with contextual actions
   - **French number formatting** throughout all displays
   - **Copy-to-clipboard functionality** with formatted text reports
   - **Modal dialogs** for testing tips and additional guidance

3. **Navigation System**: Seamless navigation between index and individual reports
4. **MigrationReporter Service**: Generates reports using Blade views with comprehensive metadata tracking

#### Technical Implementation Benefits
- **Reliability**: Inline CSS/JS eliminates asset loading issues
- **Performance**: No external file dependencies, faster load times
- **Consistency**: Unified color palette and spacing across all views
- **Maintainability**: Self-contained views with all resources embedded
- **Internationalization**: French number formatting with proper locale support
- **Accessibility**: Semantic HTML and ARIA attributes for screen readers
- **Responsive**: Mobile-first design with breakpoint optimization

### Report Configuration & Traceability

The package provides comprehensive **migration context tracking** in reports:

#### Configuration Capture
- **Migration Options**: Captures all command-line options (--dry-run, --icons-only, --assets-only, --path, etc.)
- **Environment Configuration**: Records license type, scan paths, file extensions, backup settings
- **Dynamic Versioning**: Package version automatically extracted from composer.json with fallback

#### Report Metadata Structure
```json
{
  "meta": {
    "generated_at": "2025-01-25T10:30:00+00:00",
    "package_version": "1.1.0",
    "dry_run": true,
    "migration_options": {
      "icons_only": false,
      "assets_only": false,
      "custom_path": null,
      "backup": null
    },
    "configuration": {
      "license_type": "free",
      "scan_paths": ["resources/views", "resources/js"],
      "file_extensions": ["blade.php", "vue", "js", "css"]
    }
  }
}
```

#### Traceability Benefits
- **Reproducibility**: Exact command reconstruction from report metadata
- **Audit Trail**: Complete history of migration parameters and context
- **Debugging Support**: Configuration visibility for troubleshooting issues
- **Version Tracking**: Dynamic package version prevents outdated documentation

### Configuration System

The package uses a comprehensive configuration file (`config/fontawesome-migrator.php`) that supports:
- License type detection (free/pro)
- Pro styles configuration (light, duotone, thin, sharp)
- Fallback strategies for Pro → Free migration
- Customizable scan paths and file extensions
- Backup and reporting options
- Advanced configuration management via `fontawesome:config` command with:
  - Interactive menu system for configuration editing
  - Granular control over scan paths, file extensions, exclusion patterns
  - Pro styles management with license validation
  - Configuration validation and backup capabilities
  - Optimized UX for large projects with multiple configuration changes

### Key Features

1. **Intelligent Migration**: Automatically converts FA5 syntax to FA6 (e.g., `fas fa-home` → `fa-solid fa-house`)
2. **Icon Mapping**: Handles renamed icons (e.g., `fa-times` → `fa-xmark`)
3. **Asset Migration**: Migrates CDN URLs, NPM packages, JS imports, CSS @import statements, webpack.mix.js
4. **Pro Support**: Full support for Pro styles with fallback to Free alternatives
5. **Package Manager Support**: Complete NPM, Yarn, pnpm package.json migration with .json extension support
6. **Multi-Format Support**: CSS, SCSS, JS, TS, Vue, HTML, Blade, JSON (including package.json and webpack.mix.js)
7. **Modern Web Interface**: 
   - **Enhanced reports management UI** at `/fontawesome-migrator/reports`
   - **Interactive data visualization** with Chart.js integration
   - **Real-time search and filtering** with syntax highlighting
   - **Performance metrics dashboard** with success rate calculations
   - **Responsive design** with mobile-first approach
8. **Backup System**: Creates timestamped backups before modifications
9. **Progress Reporting**: Real-time progress bars and detailed interactive reports
10. **Migration Modes**: Complete, icons-only, assets-only options
11. **Advanced UI/UX Design**: 
    - **Complete inline CSS/JS architecture** for maximum reliability
    - **French localization** with proper number formatting conventions
    - **Modern gradient design** with smooth animations and hover effects
    - **Contextual recommendations** with actionable guidance
    - **Copy-to-clipboard functionality** for reports and commands
12. **Configuration Traceability**: Comprehensive migration context tracking with dynamic versioning and reproducible audit trails
13. **Navigation System**: Seamless navigation between reports index and individual report views
14. **Accessibility**: WCAG-compliant design with semantic HTML and ARIA support
15. **Advanced Configuration Management**: Interactive configuration command (`fontawesome:config`) optimized for large projects with granular editing capabilities

### Package Status
🎉 **PRODUCTION READY** - All tests passing, fully functional, ready for:
- ✅ Production use in Laravel applications
- ✅ Publication on Packagist
- ✅ Team collaboration and contributions
- ✅ CI/CD integration

## Améliorations de l'Interface Web

### 📋 Résumé des Améliorations

Toutes les améliorations apportées à l'interface web du package FontAwesome Migrator pour offrir une expérience utilisateur moderne et intuitive.

### 🎨 Design System Modernisé

#### Architecture CSS Inline
- **Problème résolu** : Dépendances externes d'assets CSS/JS
- **Solution** : Intégration complète inline dans les vues Blade
- **Avantages** : 
  - Aucun problème de chargement d'assets
  - Performance optimisée
  - Facilité de déploiement

#### Palette de Couleurs Unifiée
```css
:root {
    --primary-color: #4299e1;     /* Bleu principal */
    --primary-hover: #3182ce;     /* Bleu hover */
    --secondary-color: #667eea;   /* Violet secondaire */
    --success-color: #48bb78;     /* Vert succès */
    --error-color: #e53e3e;       /* Rouge erreur */
    --warning-color: #ed8936;     /* Orange avertissement */
}
```

### 🚀 Vue Index des Rapports (`index.blade.php`)

#### Nouvelles Fonctionnalités
1. **Dashboard de Statistiques Globales**
   - Nombre total de rapports
   - Taille totale en KB (formatage français)
   - Date du dernier rapport
   - Nombre de rapports cette semaine

2. **Design des Cartes Modernisé**
   - Gradients et animations hover
   - Métadonnées enrichies (Taille, Heure, Âge)
   - Bordures colorées et effets de profondeur
   - Responsive design mobile-first

3. **État Vide Amélioré**
   - Guidance claire pour générer le premier rapport
   - Exemple de commande avec conseil dry-run
   - Design accueillant avec bordures en pointillés

#### Corrections Techniques
- **Formatage français** : `number_format($value, 1, ',', ' ')`
- **Icônes visibles** : Suppression du dégradé CSS qui cachait les emojis
- **Navigation** : Liens vers les rapports via le contrôleur

### 📊 Vue Rapports Individuels (`migration.blade.php`)

#### Fonctionnalités Interactives
1. **Visualisation de Données**
   - Graphiques Chart.js avec données en temps réel
   - Répartition par type de changement clarifiée
   - Légendes avec formatage français des pourcentages

2. **Sections Pliables**
   - Détails fermés par défaut pour une meilleure UX
   - Animations fluides CSS3
   - Toggle individuel et global

3. **Recherche et Filtrage**
   - Recherche en temps réel dans les changements
   - Surlignage des correspondances
   - Compteur de résultats dynamique

4. **Métriques de Performance**
   - Taux de migration calculé
   - Densité des changements par fichier
   - Taux de succès basé sur les avertissements
   - Indicateurs visuels (tendances)

5. **Système de Recommandations**
   - Conseils contextuels selon les résultats
   - Actions rapides (copie de commandes)
   - Modales d'aide avec tips de test

6. **Export et Partage**
   - Copie du rapport formaté en texte
   - Export des statistiques
   - Notifications toast avec animations

#### Navigation
- **Bouton retour** vers l'index des rapports
- **Breadcrumb** visuel dans le header

### 🔧 Corrections Techniques Appliquées

#### 1. Affichage des Icônes Emoji
**Problème** : Les dégradés CSS rendaient les emojis invisibles
```css
/* ❌ AVANT - Masquait les emojis */
background: linear-gradient(...);
-webkit-background-clip: text;
-webkit-text-fill-color: transparent;

/* ✅ APRÈS - Emojis visibles */
color: var(--primary-color);
```

**Fichiers corrigés** :
- `resources/views/reports/index.blade.php` (ligne 138-144)
- `resources/views/reports/migration.blade.php` (ligne 38-41)

#### 2. Formatage Français des Nombres
**Problème** : Nombres au format anglais (1,234.5)
**Solution** : Format français (1 234,5)

```php
/* ❌ AVANT */
number_format($value, 1)

/* ✅ APRÈS */
number_format($value, 1, ',', ' ')
```

**JavaScript** :
```javascript
// Formatage avec Intl.NumberFormat
function formatNumber(number, decimals = 0) {
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals
    }).format(number);
}
```

#### 3. Conflits JavaScript
**Problème** : Variables redéclarées entre inline et externe
**Solution** : Exposition au scope global
```javascript
/* ✅ Fonctions accessibles globalement */
window.toggleFileDetails = function(index) { ... }
window.filterChanges = function() { ... }
window.copyToClipboard = function() { ... }
```

#### 4. Architecture d'Assets
**Avant** : Fichiers CSS/JS externes avec routes PHP
**Après** : Tout inline dans les vues Blade

**Fichiers supprimés** :
- `resources/css/migration-reports.css`
- `resources/js/migration-reports.js`
- `src/Http/Controllers/AssetsController.php`
- Routes assets dans `ServiceProvider.php`

### 📱 Responsive Design

#### Breakpoints
```css
@media (max-width: 768px) {
    .reports-grid { grid-template-columns: 1fr; }
    .header h1 { font-size: 2rem; }
    .actions { flex-direction: column; }
    .report-meta { grid-template-columns: 1fr 1fr; }
}
```

#### Approche Mobile-First
- Grilles flexibles avec `auto-fit`
- Textes et boutons adaptatifs
- Navigation optimisée pour le tactile

### 🎯 Clarifications UX

#### 1. Avertissements Expliqués
**Avant** : "Avertissements - À vérifier"
**Après** : "Icônes à vérifier - icône(s) renommée(s), dépréciée(s) ou Pro détectée(s)"

#### 2. Graphique de Répartition
**Avant** : "Répartition des changements" (ambigu)
**Après** : "Répartition par type de changement" avec explication des types

#### 3. Métadonnées des Cartes
**Ajouté** : Âge du rapport (en minutes/heures/jours)
**Amélioré** : Labels explicites avec icônes

### 📈 Métriques de Performance

#### Calculs Automatiques
```javascript
// Taux de migration
const migrationRate = (modified_files / total_files) * 100;

// Densité des changements
const changesDensity = total_changes / modified_files;

// Taux de succès
const successRate = ((total_changes - warnings) / total_changes) * 100;
```

#### Indicateurs Visuels
- **Couleurs** : Vert (bon), Orange (moyen), Rouge (attention)
- **Flèches** : ↗ (positif), → (neutre)
- **Seuils** : Configurables selon les métriques

### 🔍 Fonctionnalités de Recherche

#### Recherche Intelligente
- **Champs** : Noms de fichiers + contenu des changements
- **Highlighting** : Surlignage des correspondances
- **Temps réel** : Filtrage instantané sans rechargement

#### Algorithme de Filtrage
```javascript
// Recherche dans fichiers ET changements
const fileMatches = fileName.includes(searchTerm);
const changeMatches = changeFrom.includes(searchTerm) || changeTo.includes(searchTerm);

// Affichage si correspondance OU terme vide
if (matches || fileMatches || searchTerm === '') {
    // Afficher avec surlignage
}
```

### 🚀 Performance et Optimisations

#### Animations CSS3
- **Transitions** : `transition: all 0.3s ease`
- **Keyframes** : fadeInUp, slideIn/Out, pulse
- **Hover** : Transform et box-shadow

#### Lazy Loading
- **Métriques** : Calculées seulement si données disponibles
- **Graphiques** : Initialisés seulement si changements > 0
- **Animations** : Intersection Observer pour déclenchement

#### Cache et Performance
- **Inline** : Pas de requêtes HTTP additionnelles
- **Minimal DOM** : Structures optimisées
- **Efficient JS** : Pas de frameworks lourds

### 🎉 Résultat Final

#### Avant vs Après
| Aspect | Avant | Après |
|--------|-------|-------|
| **Design** | Basique, statique | Moderne, animé |
| **Navigation** | Liens directs vers fichiers | Navigation contrôleur fluide |
| **Données** | Statiques | Interactives avec graphiques |
| **Formatage** | Anglais | Français complet |
| **Responsive** | Limité | Mobile-first complet |
| **Fonctionnalités** | Basiques | Recherche, filtrage, export |
| **Performance** | Dépendances externes | Tout inline optimisé |

#### Impact Utilisateur
- **UX améliorée** : Interface intuitive et moderne
- **Productivité** : Recherche et navigation rapides
- **Compréhension** : Métriques et explications claires
- **Fiabilité** : Aucun problème de chargement d'assets
- **Accessibilité** : Design responsive et sémantique

## Development Guidelines

### Code Standards
- Uses PHP 8.4+ features including attributes and constructor property promotion
- Follows Laravel conventions and PSR standards
- Configured with Laravel Pint using Laravel preset
- Uses Rector for automated code modernization

### Testing Structure
- **Orchestra Testbench**: Laravel package testing environment
- **PHPUnit 10.5+**: Test runner with coverage support
- **Unit Tests** (`tests/Unit/`): Test individual services (IconMapper, StyleMapper, FileScanner)
- **Feature Tests** (`tests/Feature/`): Test complete Artisan command functionality
- **Test Fixtures** (`tests/Fixtures/`): Sample files for testing migrations

### Test Coverage
- Core services: IconMapper, StyleMapper, FileScanner, IconReplacer, AssetMigrator
- Command integration: MigrateFontAwesomeCommand with all migration modes
- Asset migration: CSS, JS, CDN, package.json Pro/Free scenarios  
- Configuration validation and error handling
- File scanning and pattern matching

### View Architecture & Design System

The package implements a unified design system using Laravel Blade views:

#### Shared Layout (`resources/views/layout.blade.php`)
- **CSS Variables**: Consistent color palette and spacing using CSS custom properties
- **Component Styles**: Unified buttons, tables, alerts, badges, and form elements
- **Responsive Design**: Mobile-first approach with breakpoint management
- **Typography**: Consistent font system with proper hierarchy
- **Animations**: Smooth transitions and hover effects

#### View Components
- **Reports Index** (`resources/views/reports/index.blade.php`): Grid-based listing with AJAX functionality
- **Migration Report** (`resources/views/reports/migration.blade.php`): Detailed statistics and change visualization
- **Shared Sections**: Header, stats cards, file listings, and action buttons

#### Design Benefits
- **Maintainability**: Single source of truth for styles and layouts
- **Consistency**: Unified visual experience across all interfaces
- **Performance**: Optimized CSS with minimal duplication
- **Accessibility**: Proper semantic HTML and ARIA attributes

### Configuration
- **PHPUnit**: `phpunit.xml` with testsuites and coverage configuration
- **Pint**: `pint.json` with Laravel preset and custom rules
- **Rector**: `rector.php` with Laravel-specific modernization rules
- **Composer Scripts**: Automated workflows for development tasks
```

## My Memories

- Claude Code remembers to always test PHP code thoroughly before deployment
- Claude Code prefers comprehensive test coverage for each code modification
- Claude Code emphasizes clear, readable, and maintainable code