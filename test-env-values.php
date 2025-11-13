<?php
/**
 * Quick test for ROUTE_SUBMODULE_DISCOVERY value
 */

require_once __DIR__ . '/vendor/autoload.php';

use App\Etc\Config\Environment;

Environment::load();

echo "Environment Variables:\n";
echo "ROUTE_SUBMODULE_DISCOVERY (raw): '" . Environment::get('ROUTE_SUBMODULE_DISCOVERY', 'NOT_SET') . "'\n";
echo "ROUTE_VERBOSE_LOGGING (raw): '" . Environment::get('ROUTE_VERBOSE_LOGGING', 'NOT_SET') . "'\n";
echo "ROUTE_DEBUG_OUTPUT (raw): '" . Environment::get('ROUTE_DEBUG_OUTPUT', 'NOT_SET') . "'\n";

echo "\nBoolean Conversion:\n";
$submoduleDiscovery = filter_var(
    Environment::get('ROUTE_SUBMODULE_DISCOVERY', 'true'), 
    FILTER_VALIDATE_BOOLEAN
);
echo "ROUTE_SUBMODULE_DISCOVERY (bool): " . ($submoduleDiscovery ? 'TRUE' : 'FALSE') . "\n";

$verboseLogging = filter_var(
    Environment::get('ROUTE_VERBOSE_LOGGING', Environment::isDevelopment() ? 'true' : 'false'), 
    FILTER_VALIDATE_BOOLEAN
);
echo "ROUTE_VERBOSE_LOGGING (bool): " . ($verboseLogging ? 'TRUE' : 'FALSE') . "\n";

$debugOutput = filter_var(
    Environment::get('ROUTE_DEBUG_OUTPUT', 'false'), 
    FILTER_VALIDATE_BOOLEAN
);
echo "ROUTE_DEBUG_OUTPUT (bool): " . ($debugOutput ? 'TRUE' : 'FALSE') . "\n";
