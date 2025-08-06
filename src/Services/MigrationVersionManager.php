<?php

declare(strict_types=1);

namespace FontAwesome\Migrator\Services;

use FontAwesome\Migrator\Contracts\VersionMapperInterface;
use FontAwesome\Migrator\Services\Mappers\FontAwesome4To5Mapper;
use FontAwesome\Migrator\Services\Mappers\FontAwesome5To6Mapper;
use FontAwesome\Migrator\Services\Mappers\FontAwesome6To7Mapper;
use InvalidArgumentException;

/**
 * Gestionnaire central pour les migrations multi-versions FontAwesome
 */
class MigrationVersionManager
{
    private readonly array $config;

    private array $versionMappers = [];

    public function __construct(
        private readonly ConfigurationLoader $configLoader,
        private readonly FontAwesomePatternService $patternService,
    ) {
        $this->config = config('fontawesome-migrator', []);
    }

    /**
     * Créer un mapper pour une migration spécifique
     *
     * @param  string  $fromVersion  Version source ("4", "5", "6")
     * @param  string  $toVersion  Version cible ("5", "6", "7")
     *
     * @throws InvalidArgumentException Si la migration n'est pas supportée
     */
    public function createMapper(string $fromVersion, string $toVersion): VersionMapperInterface
    {
        $migrationKey = \sprintf('%sto%s', $fromVersion, $toVersion);

        if (isset($this->versionMappers[$migrationKey])) {
            return $this->versionMappers[$migrationKey];
        }

        $mapperClass = $this->getMapperClassName($fromVersion, $toVersion);

        if (! class_exists($mapperClass)) {
            throw new InvalidArgumentException(
                \sprintf('Migration %s → %s non supportée (classe %s introuvable)',
                    $fromVersion, $toVersion, $mapperClass)
            );
        }

        $this->versionMappers[$migrationKey] = new $mapperClass($this->config, $this->configLoader);

        return $this->versionMappers[$migrationKey];
    }

    /**
     * Détecter automatiquement la version FontAwesome dans le contenu
     * Délègue au FontAwesomePatternService centralisé
     *
     * @param  string  $content  Contenu à analyser
     * @return string Version détectée ("4", "5", "6", "7", "unknown")
     */
    public function detectVersion(string $content): string
    {
        return $this->patternService->detectVersion($content);
    }

    /**
     * Obtenir toutes les migrations supportées
     *
     * @return array<array{from: string, to: string, mapper: string}>
     */
    public function getSupportedMigrations(): array
    {
        return [
            [
                'from' => '4',
                'to' => '5',
                'mapper' => FontAwesome4To5Mapper::class,
                'description' => 'Migration FontAwesome 4 → 5 (préfixes + noms)',
            ],
            [
                'from' => '5',
                'to' => '6',
                'mapper' => FontAwesome5To6Mapper::class,
                'description' => 'Migration FontAwesome 5 → 6 (noms + nouveautés)',
            ],
            [
                'from' => '6',
                'to' => '7',
                'mapper' => FontAwesome6To7Mapper::class,
                'description' => 'Migration FontAwesome 6 → 7 (modernisation + comportements)',
            ],
        ];
    }

    /**
     * Valider qu'une combinaison de versions est supportée
     *
     * @param  string  $fromVersion  Version source
     * @param  string  $toVersion  Version cible
     * @return bool True si la migration est supportée
     */
    public function isMigrationSupported(string $fromVersion, string $toVersion): bool
    {
        $supported = $this->getSupportedMigrations();

        foreach ($supported as $migration) {
            if ((string) $migration['from'] === $fromVersion && (string) $migration['to'] === $toVersion) {
                return true;
            }
        }

        return false;
    }

    /**
     * Obtenir un rapport de compatibilité pour une migration
     *
     * @param  string  $fromVersion  Version source
     * @param  string  $toVersion  Version cible
     * @return array{supported: bool, breaking_changes: array, recommendations: array}
     */
    public function getCompatibilityReport(string $fromVersion, string $toVersion): array
    {
        $report = [
            'supported' => $this->isMigrationSupported($fromVersion, $toVersion),
            'breaking_changes' => [],
            'recommendations' => [],
        ];

        if (! $report['supported']) {
            $report['breaking_changes'][] = 'Migration non supportée';

            return $report;
        }

        // Ajouter les breaking changes spécifiques selon la migration
        switch (\sprintf('%sto%s', $fromVersion, $toVersion)) {
            case '4to5':
                $report['breaking_changes'] = [
                    'Système de préfixes complètement revu',
                    'Suppression suffixes -o (outlined)',
                    'Incompatibilité sans shims',
                ];
                $report['recommendations'] = [
                    'Utiliser les shims v4 temporairement',
                    'Migrer progressivement les préfixes',
                    'Tester sur un environnement de dev',
                ];
                break;

            case '5to6':
                $report['breaking_changes'] = [
                    'Nombreux renommages d\'icônes',
                    'Nouvelles icônes disponibles',
                    'Améliorations stylistiques',
                ];
                $report['recommendations'] = [
                    'Réviser les icônes renommées',
                    'Profiter des nouvelles icônes',
                    'Mettre à jour les CDN',
                ];
                break;

            case '6to7':
                $report['breaking_changes'] = [
                    'Fixed width par défaut',
                    'Icônes décoratives par défaut',
                    'Format .woff2 uniquement',
                    'Abandon support jQuery/Less',
                ];
                $report['recommendations'] = [
                    'Revoir l\'accessibilité',
                    'Migrer vers Dart Sass',
                    'Tester les largeurs fixes',
                ];
                break;
        }

        return $report;
    }

    /**
     * Créer une chaîne de migration pour plusieurs versions
     *
     * @param  string  $fromVersion  Version de départ
     * @param  string  $toVersion  Version d'arrivée
     * @return array<VersionMapperInterface> Chaîne de mappers
     *
     * @throws InvalidArgumentException Si la chaîne ne peut être créée
     */
    public function createMigrationChain(string $fromVersion, string $toVersion): array
    {
        if ($fromVersion === $toVersion) {
            return [];
        }

        // Pour l'instant, seulement migrations directes
        // Plus tard : support migration chaînée (4→5→6→7)
        if (! $this->isMigrationSupported($fromVersion, $toVersion)) {
            throw new InvalidArgumentException(
                \sprintf('Migration en chaîne %s → %s non implémentée', $fromVersion, $toVersion)
            );
        }

        return [$this->createMapper($fromVersion, $toVersion)];
    }

    /**
     * Obtenir le nom de classe du mapper pour une migration
     *
     * @param  string  $fromVersion  Version source
     * @param  string  $toVersion  Version cible
     * @return string Nom de classe complet
     */
    private function getMapperClassName(string $fromVersion, string $toVersion): string
    {
        return \sprintf(
            'FontAwesome\\Migrator\\Services\\Mappers\\FontAwesome%sTo%sMapper',
            $fromVersion,
            $toVersion
        );
    }

    /**
     * Obtenir des statistiques sur les migrations disponibles
     *
     * @return array{total_migrations: int, versions_supported: array, detection_patterns: int}
     */
    public function getMigrationStatistics(): array
    {
        $migrations = $this->getSupportedMigrations();
        $versions = array_unique(array_merge(
            array_column($migrations, 'from'),
            array_column($migrations, 'to')
        ));

        return [
            'total_migrations' => \count($migrations),
            'versions_supported' => $versions,
            'detection_patterns' => 4, // FA4, FA5, FA6, FA7
        ];
    }
}
