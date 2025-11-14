<?php
/**
 * Test route discovery with new capitalized folder structure
 */

require 'vendor/autoload.php';

use App\Etc\Config\ConfigManager;
use App\Etc\InitModsImproved;

echo "Testing route discovery with capitalized folders...\n\n";

// Enable debug output
putenv('ROUTE_DEBUG_OUTPUT=true');
putenv('ROUTE_SUBMODULE_DISCOVERY=true');

try {
    $config = new ConfigManager();
    echo "✓ ConfigManager initialized\n";
    
    $modulesPath = $config->get('app.path') . '/src/Modules';
    echo "✓ Modules path: $modulesPath\n";
    
    // InitModsImproved will output debug info
    echo "\n--- Route Discovery Output ---\n";
    $init = new InitModsImproved($modulesPath);
    echo "--- End Route Discovery ---\n\n";
    
    echo "✓ Route discovery completed successfully with capitalized folders!\n";
    echo "  Check the output above for discovered modules\n";
    
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
