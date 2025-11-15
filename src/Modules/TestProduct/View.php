<?php
namespace App\Modules\TestProduct;

use App\Common\Bmvc\BaseView;

/**
 * Enhanced App\Modules\TestProduct View
 * 
 * Supports modern templating and environment awareness
 */
class View extends BaseView
{
    private $layoutPath;
    
    public function __construct()
    {
        $this->layoutPath = __DIR__ . '/views/layouts/';
    }

    /**
     * Enhanced render method with layout support
     */
    public function render(string $template, array $data = []): void
    {
        // Enhanced: Add environment data
        $data['app_env'] = \upMVC\Config\Environment::current();
        $data['debug_mode'] = \upMVC\Config\Environment::isDevelopment();
        $data['module_name'] = 'App\Modules\TestProduct';
        
        // Extract data for template
        extract($data);
        
        // Include header
        include $this->layoutPath . 'header.php';
        
        // Include main template
        $templateFile = __DIR__ . "/views/{$template}.php";
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            $this->renderError("Template {$template} not found", $data);
        }
        
        // Include footer
        include $this->layoutPath . 'footer.php';
    }

    /**
     * Enhanced error rendering
     */
    private function renderError(string $message, array $data): void
    {
        echo "<div class='alert alert-danger'>";
        echo "<h4>Template Error</h4>";
        echo "<p>" . htmlspecialchars($message) . "</p>";
        
        if ($data['debug_mode'] ?? false) {
            echo "<details><summary>Debug Info</summary>";
            echo "<pre>" . print_r($data, true) . "</pre>";
            echo "</details>";
        }
        
        echo "</div>";
    }
}
// Enhanced CRUD view methods would be added here