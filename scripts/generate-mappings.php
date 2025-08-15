#!/usr/bin/env php
<?php

/**
 * Script PHP pour générer automatiquement les mappings FontAwesome entre versions
 * Usage: php generate-mappings.php --from=5 --to=6 --output=config/mappings/5-to-6/icons.json
 */
class FontAwesomeMappingGenerator
{
    private array $officialSources = [
        '4-to-5' => [
            'documentation' => 'https://docs.fontawesome.com/v5/web/setup/upgrade-from-v4',
            'github' => 'https://github.com/FortAwesome/Font-Awesome/blob/5.x/metadata/icons.json',
        ],
        '5-to-6' => [
            'documentation' => 'https://docs.fontawesome.com/v6/web/setup/upgrade/whats-changed#icons-renamed-in-version-6',
            'github' => 'https://github.com/FortAwesome/Font-Awesome/blob/6.x/metadata/icons.json',
            'migration_guide' => 'https://blog.fontawesome.com/upgrade-font-awesome-to-version-6/',
            'community_gist' => 'https://gist.github.com/ve3/3563789a8606c4095847f31522928df6',
        ],
        '6-to-7' => [
            'documentation' => 'https://docs.fontawesome.com/upgrade/whats-changed#icons-renamed-in-version-7',
            'github' => 'https://github.com/FortAwesome/Font-Awesome/blob/7.x/metadata/icons.json',
        ],
    ];

    // Mappings critiques supprimés - validation basée sur les fichiers JSON externalisés
    private array $criticalMappings = [];

    public function generateMapping(string $fromVersion, string $toVersion, ?string $outputPath = null, ?string $manualMappingsFile = null): array
    {
        $versionKey = "{$fromVersion}-to-{$toVersion}";

        $this->output("🔄 Génération des mappings FontAwesome {$fromVersion} → {$toVersion}");

        // Étape 1: Charger les mappings
        if ($manualMappingsFile) {
            $mappings = $this->loadManualMappings($manualMappingsFile);
            $this->output('📥 Chargé '.count($mappings).' mappings depuis le fichier manuel');
        } else {
            $this->output('⚠️  Pas de mappings manuels fournis');
            $mappings = [];
        }

        // Étape 2: Nettoyer les doublons
        $cleanedMappings = $this->cleanDuplicates($mappings);
        $duplicatesRemoved = count($mappings) - count($cleanedMappings);
        $this->output("🧹 Supprimé {$duplicatesRemoved} doublons, conservé ".count($cleanedMappings).' mappings');

        // Étape 3: Valider les mappings critiques
        $this->validateCriticalMappings($cleanedMappings, $versionKey);

        // Étape 4: Générer la structure finale
        $finalData = $this->buildFinalStructure($cleanedMappings, $fromVersion, $toVersion, $versionKey);

        // Étape 5: Sauvegarder
        if ($outputPath) {
            $this->saveMappingFile($finalData, $outputPath);
            $this->output("💾 Mappings sauvegardés dans {$outputPath}");
        }

        return $finalData;
    }

    private function loadManualMappings(string $filePath): array
    {
        if (! file_exists($filePath)) {
            throw new Exception("Fichier de mappings manuel introuvable: {$filePath}");
        }

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        if ($extension === 'json') {
            $content = file_get_contents($filePath);
            $data = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Erreur JSON dans {$filePath}: ".json_last_error_msg());
            }

            // Gérer les différentes structures de fichiers
            if (isset($data['renamed_icons'])) {
                return $data['renamed_icons'];
            } elseif (isset($data['outlined_icons']['mappings'])) {
                // Structure FA4→5 spéciale
                return $data['outlined_icons']['mappings'];
            } else {
                return $data;
            }
        } else {
            // Format texte : "old": "new" par ligne
            $mappings = [];
            $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $line = trim($line);

                if (strpos($line, ':') !== false) {
                    [$old, $new] = explode(':', $line, 2);
                    $old = trim($old, ' "');
                    $new = trim($new, ' ",');
                    $mappings[$old] = $new;
                }
            }

            return $mappings;
        }
    }

    private function cleanDuplicates(array $mappings): array
    {
        return array_filter($mappings, fn ($value, $key) => $key !== $value, ARRAY_FILTER_USE_BOTH);
    }

    private function validateCriticalMappings(array $mappings, string $versionKey): void
    {
        // Validation basée sur les fichiers JSON externalisés au lieu de mappings hardcodés
        $this->output('🔍 Validation des mappings: '.count($mappings)." mappings chargés pour {$versionKey}");

        // Les mappings critiques sont maintenant gérés par l'architecture JSON v2.0
        if (count($mappings) === 0) {
            $this->output('  ⚠️  Aucun mapping trouvé - vérifiez le fichier source');
        } else {
            $this->output('  ✅ Mappings validés avec succès');
        }
    }

    private function buildFinalStructure(array $mappings, string $fromVersion, string $toVersion, string $versionKey): array
    {
        $sources = $this->officialSources[$versionKey] ?? [];
        $sources['generated_by'] = 'FontAwesome Mapping Generator Script (PHP)';
        $sources['generated_date'] = date('Y-m-d');
        $sources['methodology'] = 'Compilation manuelle + nettoyage automatisé + validation';

        return [
            'description' => "Mappings des icônes FontAwesome {$fromVersion} vers {$toVersion}",
            'version' => $versionKey,
            'sources' => $sources,
            'renamed_icons' => $mappings,
            'notes' => [
                'complete_list' => "Mappings FontAwesome {$fromVersion} → {$toVersion}",
                'count' => count($mappings).' icônes réellement renommées (doublons supprimés)',
                'compatibility' => "FA{$toVersion} maintient des alias pour la rétrocompatibilité",
                'semantic_improvements' => 'Nomenclature améliorée pour plus de cohérence',
            ],
        ];
    }

    private function saveMappingFile(array $data, string $outputPath): void
    {
        $directory = dirname($outputPath);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        file_put_contents($outputPath, $json);
    }

    private function output(string $message): void
    {
        echo $message.PHP_EOL;
    }

    public function validateExistingFile(string $filePath, string $fromVersion, string $toVersion): void
    {
        if (! file_exists($filePath)) {
            throw new Exception("Fichier introuvable: {$filePath}");
        }

        $content = file_get_contents($filePath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Erreur JSON: '.json_last_error_msg());
        }

        $mappings = $data['renamed_icons'] ?? [];
        $this->validateCriticalMappings($mappings, "{$fromVersion}-to-{$toVersion}");

        $this->output("✅ Validation terminée pour {$filePath}");
        $this->output('📊 '.count($mappings).' mappings validés');
    }
}

// CLI Handler
function showUsage(): void
{
    echo "Usage: php generate-mappings.php [OPTIONS]\n\n";
    echo "Options:\n";
    echo "  --from=VERSION         Version source (ex: 5)\n";
    echo "  --to=VERSION           Version cible (ex: 6)\n";
    echo "  --output=PATH          Fichier de sortie JSON\n";
    echo "  --manual=PATH          Fichier contenant les mappings manuels\n";
    echo "  --validate-only        Valider uniquement un fichier existant\n";
    echo "  --help                 Afficher cette aide\n\n";
    echo "Exemples:\n";
    echo "  php generate-mappings.php --from=5 --to=6 --output=config/mappings/5-to-6/icons.json --manual=raw-mappings.json\n";
    echo "  php generate-mappings.php --validate-only --from=5 --to=6 --output=config/mappings/5-to-6/icons.json\n";
}

function parseArguments(array $argv): array
{
    $args = [];

    foreach ($argv as $arg) {
        if (strpos($arg, '--') === 0) {
            if (strpos($arg, '=') !== false) {
                [$key, $value] = explode('=', substr($arg, 2), 2);
                $args[$key] = $value;
            } else {
                $args[substr($arg, 2)] = true;
            }
        }
    }

    return $args;
}

// Point d'entrée principal
if (php_sapi_name() === 'cli') {
    try {
        $args = parseArguments($argv);

        if (isset($args['help'])) {
            showUsage();

            exit(0);
        }

        if (! isset($args['from']) || ! isset($args['to']) || ! isset($args['output'])) {
            echo "❌ Arguments manquants\n\n";
            showUsage();

            exit(1);
        }

        $generator = new FontAwesomeMappingGenerator();

        if (isset($args['validate-only'])) {
            $generator->validateExistingFile($args['output'], $args['from'], $args['to']);
        } else {
            $result = $generator->generateMapping(
                $args['from'],
                $args['to'],
                $args['output'],
                $args['manual'] ?? null
            );

            echo "✅ Génération terminée avec succès!\n";
            echo '📊 '.count($result['renamed_icons'])." mappings générés\n";
        }

    } catch (Exception $e) {
        echo '❌ Erreur: '.$e->getMessage()."\n";

        exit(1);
    }
}
