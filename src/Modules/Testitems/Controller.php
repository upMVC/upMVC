<?php
namespace App\Modules\Testitems;

use App\Common\Bmvc\BaseController;

/**
 * Enhanced TestItems Controller
 * 
 * Auto-discovered by InitModsImproved.php
 * Generated with enhanced upMVC features
 */
class Controller extends BaseController
{
    private $view;

    public function __construct()
    {
        parent::__construct();
        $this->view = new View();
        
                // Enhanced: Middleware integration ready
        // Uncomment and configure as needed:
        // $this->addMiddleware('auth'); // Requires authentication
        // $this->addMiddleware('admin'); // Requires admin role
        // $this->addMiddleware('cors'); // Enable CORS
    }

    /**
     * Main display method - auto-routed
     */
    public function display($reqRoute, $reqMet): void
    {
        $data = [
            'title' => 'TestItems Module',
            'message' => 'Welcome to the enhanced TestItems module!',
            'features' => [
                'Auto-discovery by InitModsImproved.php',
                'Environment-aware configuration',
                'Caching support',
                'Middleware integration ready',
                'Modern PHP 8.1+ features'
            ],
            'route_info' => [
                'current_route' => $reqRoute,
                'method' => $reqMet,
                'module' => 'TestItems'
            ]
        ];
        
        $this->view->render('index', $data);
    }

    /**
     * About page with module information
     */
    public function about($reqRoute, $reqMet): void
    {
        $data = [
            'title' => 'About TestItems',
            'content' => 'This module was generated with the enhanced upMVC module generator.',
            'tech_info' => [
                'auto_discovery' => 'Enabled via InitModsImproved.php',
                'environment' => \App\Etc\Config\Environment::get('APP_ENV', 'production'),
                'caching' => $this->isCachingEnabled() ? 'Enabled' : 'Disabled',
                'generated_at' => date('Y-m-d H:i:s')
            ]
        ];
        
        $this->view->render('about', $data);
    }

    /**
     * API endpoint for module information
     */
    public function api($reqRoute, $reqMet): void
    {
        header('Content-Type: application/json');
        
        $response = [
            'success' => true,
            'module' => 'TestItems',
            'version' => '2.0-enhanced',
            'features' => [
                'auto_discovery' => true,
                'caching' => $this->isCachingEnabled(),
                'middleware_ready' => true,
                'submodule_support' => true
            ],
            'request' => [
                'route' => $reqRoute,
                'method' => $reqMet,
                'timestamp' => date('c')
            ]
        ];
        
        echo json_encode($response, JSON_PRETTY_PRINT);
    }

    /**
     * Check if caching is enabled for this module
     */
    private function isCachingEnabled(): bool
    {
        return \App\Etc\Config\Environment::get('ROUTE_USE_CACHE', 'true') === 'true';
    }
}
// Enhanced CRUD methods would be added here










