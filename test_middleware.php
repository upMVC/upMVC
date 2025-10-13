<?php
require_once 'vendor/autoload.php';

// Initialize the full application flow
echo "<h2>Testing Dashboard without Global Auth Middleware</h2>";

// Simulate the full application startup
$start = new upMVC\Start();

echo "<h3>1. Testing route dispatch...</h3>";

try {
    // Clear any existing session
    session_destroy();
    session_start();
    
    // Capture the output to avoid headers sent errors
    ob_start();
    
    // This should now go directly to dashboard controller without auth middleware interference
    $start->upMVC();
    
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "✓ Application ran without fatal errors<br>";
    echo "Output captured: " . (empty($output) ? "No output" : strlen($output) . " characters") . "<br>";
    
    if (strpos($output, '/dashboard/login') !== false) {
        echo "✅ Correctly redirected to dashboard login!<br>";
    } else if (strpos($output, '/auth') !== false) {
        echo "❌ Still redirecting to system auth<br>";
    } else {
        echo "Output preview: " . htmlspecialchars(substr($output, 0, 300)) . "...<br>";
    }
    
} catch (Exception $e) {
    ob_end_clean();
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}