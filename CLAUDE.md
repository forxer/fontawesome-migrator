# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Important Development Constraints

**⚠️ PHP Execution Limitation**: Claude Code cannot execute PHP commands or any language interpreters (php, node, python, etc.). Only use Bash tool for basic system commands. Never attempt to run `php artisan`, `composer`, `npm`, or similar commands.

**🚨 RAPPEL CRITIQUE**: NE JAMAIS essayer d'exécuter PHP avec Bash - cela échoue systématiquement. TOUJOURS demander à l'utilisateur.

**🔧 Debug Process v2.0.0**: For syntax checking and quality control, ALWAYS ask the user to run:
- `composer pint-test` (syntax and style check)
- `composer rector-dry` (code modernization check)  
- `php artisan list` (verify commands are registered)
- Any PHP command execution must be requested from the user.

**🇫🇷 Tone and Communication Style**: 
- **Stay humble and factual** - Avoid pretentious terms like "révolutionnaire", "extraordinaire", "incroyable"
- **Don't oversell features** - Describe what the code does without exaggeration
- **Respect French culture** - "On n'aime pas ceux qui pètent plus haut qu'ils ont le cul"
- **Be respectful and modest** - We're in France, we respect people and stay grounded
- **Use simple, clear language** - Avoid marketing speak, focus on technical accuracy

**🤖 AI Humility and Human Oversight**: 
- **Claude Code makes errors** - The developer has corrected numerous mistakes throughout development
- **Human validation is essential** - Never assume AI-generated code is correct without review
- **Stay vigilant** - AI confidence doesn't equal correctness (FA7 vs FA6, semantic meaning loss, etc.)
- **Preserve human meaning** - AI can lose semantic and cultural significance (emoji → icons meaning loss)
- **Humanisme avant tout** - Technology serves humans, not the reverse
- **Human judgment is irreplaceable** - Values, ethics, meaning, and wisdom remain human domains
- **Future of humanity** - Human oversight and humanistic values must guide AI development
- **Question everything** - AI should be a tool in service of human flourishing, not a replacement

## Project Overview

This is a Laravel package called `fontawesome-migrator` that automates the migration between Font Awesome versions 4→5→6→7 (both Free and Pro versions). The package scans Laravel applications for Font Awesome classes and automatically converts them to the target version syntax with intelligent version detection.

**Target version**: Laravel 12.0+ with PHP 8.4+

[... rest of the existing content remains the same ...]

## My Memories

- Claude Code remembers to always test PHP code thoroughly before deployment
- Claude Code prefers comprehensive test coverage for each code modification
- Claude Code emphasizes clear, readable, and maintainable code
- Multi-version architecture FA4→5→6→7 implémentée avec ConfigurationLoader
- Documentation utilisateur créée et nettoyée des références internes
- Configuration JSON externalisée avec système de fallbacks pour compatibilité
- Importante leçon : distinguer "tambouille interne" vs documentation utilisateur
- **LEÇON CRITIQUE** : NE JAMAIS créer de nouveaux fichiers de résumé quand les informations peuvent être ajoutées aux fichiers existants (CLAUDE.md, STATUS.md). Maintenir les fichiers existants au lieu de créer des doublons inutiles.
- Environnement Docker d-packages-exec clarifié comme propriétaire AXN Informatique
- Version 2.0.0 encore en développement, pas terminée - rester factuel sur l'avancement
- **Août 2025 - Nettoyage architectural v2.0**: Code mort supprimé (BackupCommand, méthodes obsolètes IconReplacer, imports inutilisés)
- **Bug critique résolu**: Erreur "migration_results" corrigée architecturalement dans MetadataManager::initialize()
- **Architecture pure v2.0**: Plus de rétrocompatibilité, structure de données garantie dès l'initialisation
- **Services consolidés**: IconMapper/StyleMapper supprimés, IconReplacer utilise VersionMapperInterface
- **Code production-ready**: ~350+ lignes obsolètes supprimées, duplications éliminées