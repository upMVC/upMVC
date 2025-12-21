<?php
namespace App\Modules\TestDashboard;

use App\Common\Bmvc\BaseView;

/**
 * Enhanced App\Modules\TestDashboard Dashboard View
 */
class View extends BaseView
{
    private $layoutPath;
    
    public function __construct()
    {
        $this->layoutPath = __DIR__ . '/views/layouts/';
    }

    /**
     * Enhanced render method for dashboard
     */
    public function render(string $template, array $data = []): void
    {
        $data['app_env'] = \App\Etc\Config\Environment::get('APP_ENV', 'production');
        $data['debug_mode'] = \App\Etc\Config\Environment::isDevelopment();
        $data['module_name'] = 'App\Modules\TestDashboard';
        
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
}