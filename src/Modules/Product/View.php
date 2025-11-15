<?php
namespace App\Modules\Product;

use App\Common\Bmvc\BaseView;

/**
 * Enhanced App\Modules\Product CRUD View
 */
class View extends BaseView
{
    private $layoutPath;
    
    public function __construct()
    {
        $this->layoutPath = __DIR__ . '/views/layouts/';
    }

    /**
     * Render view template
     */
    public function render(string $template, array $data = []): void
    {
        $data['app_env'] = \App\Etc\Config\Environment::get('APP_ENV', 'production');
        $data['debug_mode'] = \App\Etc\Config\Environment::isDevelopment();
        $data['module_name'] = 'App\Modules\Product';
        
        extract($data);
        
        include $this->layoutPath . 'header.php';
        
        $templateFile = __DIR__ . "/views/{$template}.php";
        if (file_exists($templateFile)) {
            include $templateFile;
        } else {
            echo "<div class='alert alert-danger'>Template {$template} not found</div>";
        }
        
        include $this->layoutPath . 'footer.php';
    }

    /**
     * Render flash messages
     */
    public function renderFlashMessages(): void
    {
        if (isset($_SESSION['success'])) {
            echo "<div class='alert alert-success alert-dismissible fade show'>";
            echo "<i class='fas fa-check-circle'></i> " . htmlspecialchars($_SESSION['success']);
            echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
            echo "</div>";
            unset($_SESSION['success']);
        }
        
        if (isset($_SESSION['error'])) {
            echo "<div class='alert alert-danger alert-dismissible fade show'>";
            echo "<i class='fas fa-exclamation-circle'></i> " . htmlspecialchars($_SESSION['error']);
            echo "<button type='button' class='btn-close' data-bs-dismiss='alert'></button>";
            echo "</div>";
            unset($_SESSION['error']);
        }
    }
}