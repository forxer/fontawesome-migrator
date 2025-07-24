# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel package called `fontawesome-migrator` that automates the migration from Font Awesome 5 to Font Awesome 6 (both Free and Pro versions). The package scans Laravel applications for Font Awesome classes and automatically converts them to the new FA6 syntax.

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
```

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
# Main migration command
php artisan fontawesome:migrate

# Dry-run mode (preview changes)
php artisan fontawesome:migrate --dry-run

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
5. **MigrationReporter** (`src/Services/MigrationReporter.php`): Generates HTML and JSON reports

### Command Structure

- **MigrateFontAwesomeCommand** (`src/Commands/MigrateFontAwesomeCommand.php`): Main Artisan command that coordinates the migration process
- **ServiceProvider** (`src/ServiceProvider.php`): Laravel service provider for package registration and configuration publishing

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
3. **Pro Support**: Full support for Pro styles with fallback to Free alternatives
4. **Backup System**: Creates timestamped backups before modifications
5. **Progress Reporting**: Real-time progress bars and detailed reports
6. **File Type Support**: Works with Blade templates, Vue components, CSS, JS, and more

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
- Core services: IconMapper, StyleMapper, FileScanner, IconReplacer
- Command integration: MigrateFontAwesomeCommand with all options
- Configuration validation and error handling
- File scanning and pattern matching

### Configuration
- **PHPUnit**: `phpunit.xml` with testsuites and coverage configuration
- **Pint**: `pint.json` with Laravel preset and custom rules
- **Rector**: `rector.php` with Laravel-specific modernization rules
- **Composer Scripts**: Automated workflows for development tasks