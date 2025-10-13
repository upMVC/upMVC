<?php
require_once 'vendor/autoload.php';

// Initialize config properly
$config = new upMVC\Config();
$config->getReqRoute('/');

echo "<h2>Testing Dashboardexample Login Flow</h2>";

try {
    // Clear session
    session_destroy();
    session_start();
    
    echo "<h3>1. Testing login with correct credentials...</h3>";
    
    $controller = new Dashboardexample\Controller();
    
    // Simulate POST request to login
    $_POST['email'] = 'admin@example.com';
    $_POST['password'] = 'admin123';
    $_SERVER['REQUEST_METHOD'] = 'POST';
    
    echo "Attempting login with admin@example.com / admin123<br>";
    
    ob_start();
    $controller->login();
    $output = ob_get_clean();
    
    echo "Login method executed<br>";
    echo "Session after login: <pre>";
    print_r($_SESSION);
    echo "</pre>";
    
    if (isset($_SESSION['authenticated']) && $_SESSION['authenticated']) {
        echo "✅ Authentication successful!<br>";
        
        echo "<h3>2. Testing dashboard access...</h3>";
        
        // Now test accessing the main dashboard
        unset($_POST); // Clear POST data
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $controller->index();
        $dashboardOutput = ob_get_clean();
        
        echo "Dashboard index() executed<br>";
        if (strpos($dashboardOutput, 'Dashboard') !== false) {
            echo "✅ Dashboard rendered successfully!<br>";
        } else {
            echo "❌ Dashboard rendering failed<br>";
            echo "Output preview: " . htmlspecialchars(substr($dashboardOutput, 0, 200)) . "...<br>";
        }
    } else {
        echo "❌ Authentication failed<br>";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}