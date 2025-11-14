<?php
namespace App\Modules\DashboardExample;

use App\Common\Bmvc\BaseView;

class View extends BaseView {
    public function render($template, $data = []) {
        // Debug log current settings
        error_log("View render - Globals before merge: " . print_r($this->globals, true));
        error_log("View render - Template data before merge: " . print_r($data, true));
        
        // Merge template data with globals (globals take precedence)
        $data = array_merge($data, $this->globals);
        
        error_log("View render - Final merged data: " . print_r($data, true));
        error_log("View render - Theme setting: " . ($data['settings']['theme'] ?? 'not set'));
        
        // Extract data to make variables available in template
        extract($data);
        
        // Start output buffering
        ob_start();
        
        // Include header template
        include __DIR__ . '/templates/layout/header.php';
        
        // Include the main template
        include __DIR__ . '/templates/' . $template . '.php';
        
        // Include footer template
        include __DIR__ . '/templates/layout/footer.php';
        
        // Get contents and clean buffer
        echo ob_get_clean();
    }
}











