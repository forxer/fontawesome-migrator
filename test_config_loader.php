<?php

/**
 * Test simple pour vérifier que ConfigurationLoader fonctionne
 * À exécuter avec: php test_config_loader.php
 */

require_once __DIR__ . '/vendor/autoload.php';

use FontAwesome\Migrator\Services\ConfigurationLoader;

echo "Test ConfigurationLoader\n";
echo "=======================\n\n";

try {
    $configPath = __DIR__ . '/config/fontawesome-migrator';
    $loader = new ConfigurationLoader($configPath);
    
    // Test 1: Charger les mappings 4-to-5
    echo "1. Test chargement mappings 4-to-5:\n";
    $styleMappings = $loader->loadStyleMappings('4', '5');
    echo "   - Style mappings trouvés: " . count($styleMappings) . "\n";
    echo "   - Mapping 'fa': " . ($styleMappings['fa'] ?? 'NON TROUVÉ') . "\n";
    
    $iconMappings = $loader->loadIconMappings('4', '5');
    echo "   - Icon mappings trouvés: " . count($iconMappings) . "\n";
    
    // Test 2: Charger les mappings 5-to-6  
    echo "\n2. Test chargement mappings 5-to-6:\n";
    $styleMappings56 = $loader->loadStyleMappings('5', '6');
    echo "   - Style mappings trouvés: " . count($styleMappings56) . "\n";
    echo "   - Mapping 'fas': " . ($styleMappings56['fas'] ?? 'NON TROUVÉ') . "\n";
    
    // Test 3: Charger les mappings 6-to-7
    echo "\n3. Test chargement mappings 6-to-7:\n";
    $styleMappings67 = $loader->loadStyleMappings('6', '7');
    echo "   - Style mappings trouvés: " . count($styleMappings67) . "\n";
    
    $deprecatedIcons67 = $loader->loadDeprecatedIcons('6', '7');
    echo "   - Deprecated icons trouvés: " . count($deprecatedIcons67) . "\n";
    
    // Test 4: Migrations disponibles
    echo "\n4. Test migrations disponibles:\n";
    $migrations = $loader->getAvailableMigrations();
    echo "   - Migrations détectées: " . count($migrations) . "\n";
    foreach ($migrations as $migration) {
        echo "     * {$migration['from']} → {$migration['to']}\n";
    }
    
    echo "\n✅ Tous les tests ConfigurationLoader ont réussi!\n";
    
} catch (Exception $e) {
    echo "\n❌ Erreur: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\nTest avec FontAwesome4To5Mapper:\n";
echo "================================\n";

try {
    use FontAwesome\Migrator\Services\Mappers\FontAwesome4To5Mapper;
    
    // Test avec ConfigurationLoader
    $configPath = __DIR__ . '/config/fontawesome-migrator';
    $configLoader = new ConfigurationLoader($configPath);
    $mapper = new FontAwesome4To5Mapper([], $configLoader);
    
    echo "Mapper créé avec ConfigurationLoader\n";
    echo "Style mappings: " . count($mapper->getStyleMappings()) . "\n";
    echo "Icon mappings: " . count($mapper->getIconMappings()) . "\n";
    
    // Test mapping d'un style
    $result = $mapper->mapStyle('fa');
    echo "Mapping 'fa' → '$result'\n";
    
    // Test mapping d'une icône
    $iconResult = $mapper->mapIcon('fa-envelope-o');
    echo "Mapping 'fa-envelope-o' → '{$iconResult['new_name']}' (renamed: " . ($iconResult['renamed'] ? 'oui' : 'non') . ")\n";
    
    echo "\n✅ Test mapper avec ConfigurationLoader réussi!\n";
    
} catch (Exception $e) {
    echo "\n❌ Erreur mapper: " . $e->getMessage() . "\n";
}