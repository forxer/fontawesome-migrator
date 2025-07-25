# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

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
✅ **All tests passing**: 60+ tests, 150+ assertions, 0 failures, 0 errors
- Unit tests: IconMapper, StyleMapper, FileScanner, IconReplacer, AssetMigrator
- Feature tests: Complete Artisan command functionality including asset migration modes
- Integration tests: Laravel environment simulation with Docker support

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

# Main migration command (icons + assets)
php artisan fontawesome:migrate

# Dry-run mode (preview changes)
php artisan fontawesome:migrate --dry-run

# Migration modes
php artisan fontawesome:migrate --icons-only    # Icons only
php artisan fontawesome:migrate --assets-only   # Assets only (CSS, JS, CDN)

# Migrate specific path
php artisan fontawesome:migrate --path=resources/views

# Generate detailed report
php artisan fontawesome:migrate --report --verbose
```

## Architecture

### Core Services Architecture

The package follows a service-oriented architecture with clear separation of concerns:

1. **FileScanner** (`src/Services/FileScanner.php`): Scans the Laravel application for files containing Font Awesome classes using Symfony Finder
2. **IconMapper** (`src/Services/IconMapper.php`): Contains mappings for renamed, deprecated, and Pro-only icons
3. **StyleMapper** (`src/Services/StyleMapper.php`): Handles style conversions (fas → fa-solid, etc.)
4. **IconReplacer** (`src/Services/IconReplacer.php`): Orchestrates the replacement process using the mappers
5. **AssetMigrator** (`src/Services/AssetMigrator.php`): Migrates FontAwesome assets (CSS, JS, CDN, package.json) with Pro/Free support
6. **MigrationReporter** (`src/Services/MigrationReporter.php`): Generates HTML and JSON reports using Blade views with shared layout system

### Command Structure

- **MigrateFontAwesomeCommand** (`src/Commands/MigrateFontAwesomeCommand.php`): Main Artisan command that coordinates the migration process
- **InstallFontAwesomeCommand** (`src/Commands/InstallFontAwesomeCommand.php`): Interactive installation command with configuration wizard
- **ServiceProvider** (`src/ServiceProvider.php`): Laravel service provider for package registration and configuration publishing

### Web Interface Architecture

- **ReportsController** (`src/Http/Controllers/ReportsController.php`): REST API for reports management with CRUD operations, uses Blade views for HTML display
- **Layout View** (`resources/views/layout.blade.php`): Shared HTML layout with unified CSS design system using CSS custom properties
- **Index View** (`resources/views/reports/index.blade.php`): Reports listing interface with responsive grid layout
- **Migration View** (`resources/views/reports/migration.blade.php`): Individual report display with detailed statistics and change tracking

### View Architecture & Design System

The package implements a **unified design system** with complete mutualization of HTML formatting:

#### Shared Layout System (`resources/views/layout.blade.php`)
- **CSS Variables**: Consistent color palette, spacing, and typography using CSS custom properties
- **Component Styles**: Unified buttons, alerts, tables, cards, and form elements
- **Responsive Design**: Mobile-first approach with adaptive grid layouts
- **Design Tokens**: Standardized spacing (padding, margin), border-radius, shadows, and transitions

#### View Components Architecture
1. **Reports Index** (`resources/views/reports/index.blade.php`): Extends shared layout, displays report cards with metadata
2. **Migration Report** (`resources/views/reports/migration.blade.php`): Extends shared layout, shows detailed migration results
3. **MigrationReporter Service**: Generates reports using Blade views instead of inline HTML (200+ lines refactored)
4. **ReportsController**: Loads JSON data and renders through Blade for consistent formatting

#### Design Benefits
- **Consistency**: All interfaces share the same visual language and CSS framework
- **Maintainability**: Single source of truth for styles and layout structure  
- **Flexibility**: Easy theming through CSS variables modification
- **Performance**: Cached Blade views and optimized CSS without external dependencies

### Configuration System

The package uses a comprehensive configuration file (`config/fontawesome-migrator.php`) that supports:
- License type detection (free/pro)
- Pro styles configuration (light, duotone, thin, sharp)
- Fallback strategies for Pro → Free migration
- Customizable scan paths and file extensions
- Backup and reporting options

### Key Features

1. **Intelligent Migration**: Automatically converts FA5 syntax to FA6 (e.g., `fas fa-home` → `fa-solid fa-house`)
2. **Icon Mapping**: Handles renamed icons (e.g., `fa-times` → `fa-xmark`)
3. **Asset Migration**: Migrates CDN URLs, NPM packages, JS imports, CSS @import statements
4. **Pro Support**: Full support for Pro styles with fallback to Free alternatives
5. **Package Manager Support**: NPM, Yarn, pnpm package.json migration
6. **Multi-Format Support**: CSS, SCSS, JS, TS, Vue, HTML, Blade, JSON
7. **Web Interface**: Reports management UI at `/fontawesome-migrator/reports`
8. **Backup System**: Creates timestamped backups before modifications
9. **Progress Reporting**: Real-time progress bars and detailed reports
10. **Migration Modes**: Complete, icons-only, assets-only options
11. **Unified UI Design**: Complete mutualization of HTML formatting with shared Blade layout system for consistent visual experience

### Package Status
🎉 **PRODUCTION READY** - All tests passing, fully functional, ready for:
- ✅ Production use in Laravel applications
- ✅ Publication on Packagist
- ✅ Team collaboration and contributions
- ✅ CI/CD integration

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