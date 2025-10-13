<?php
namespace Dashusers;

class View {
    protected $viewPath;

    public function __construct() {
        $this->viewPath = __DIR__ . '/views/';
    }

    /**
     * Render a view with data
     */
    public function render($view, $data = []) {
        // Start output buffering
        ob_start();
        
        // Extract data to variables
        extract($data);
        
        // Include the view file
        $viewFile = $this->viewPath . $view . '.php';
        
        if (file_exists($viewFile)) {
            include $viewFile;
        } else {
            throw new \Exception("View file not found: " . $viewFile);
        }
        
        // Get the content and clean the buffer
        $content = ob_get_clean();
        
        // Output the content
        echo $content;
    }

    /**
     * Render JSON response
     */
    public function renderJson($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Include a partial view
     */
    public function partial($view, $data = []) {
        extract($data);
        $partialFile = $this->viewPath . 'partials/' . $view . '.php';
        
        if (file_exists($partialFile)) {
            include $partialFile;
        } else {
            echo "<!-- Partial not found: {$view} -->";
        }
    }

    /**
     * Escape HTML output
     */
    public function e($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Format date for display
     */
    public function formatDate($date, $format = 'M j, Y g:i A') {
        return date($format, strtotime($date));
    }

    /**
     * Generate pagination links
     */
    public function pagination($currentPage, $totalPages, $baseUrl, $queryParams = []) {
        if ($totalPages <= 1) return '';

        $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
        
        // Previous button
        $prevPage = max(1, $currentPage - 1);
        $prevClass = $currentPage <= 1 ? 'disabled' : '';
        $prevParams = array_merge($queryParams, ['page' => $prevPage]);
        $html .= "<li class=\"page-item {$prevClass}\"><a class=\"page-link\" href=\"{$baseUrl}?" . http_build_query($prevParams) . "\">Previous</a></li>";
        
        // Page numbers
        $start = max(1, $currentPage - 2);
        $end = min($totalPages, $currentPage + 2);
        
        for ($i = $start; $i <= $end; $i++) {
            $activeClass = $i == $currentPage ? 'active' : '';
            $pageParams = array_merge($queryParams, ['page' => $i]);
            $html .= "<li class=\"page-item {$activeClass}\"><a class=\"page-link\" href=\"{$baseUrl}?" . http_build_query($pageParams) . "\">{$i}</a></li>";
        }
        
        // Next button
        $nextPage = min($totalPages, $currentPage + 1);
        $nextClass = $currentPage >= $totalPages ? 'disabled' : '';
        $nextParams = array_merge($queryParams, ['page' => $nextPage]);
        $html .= "<li class=\"page-item {$nextClass}\"><a class=\"page-link\" href=\"{$baseUrl}?" . http_build_query($nextParams) . "\">Next</a></li>";
        
        $html .= '</ul></nav>';
        return $html;
    }

    /**
     * Generate status badge
     */
    public function statusBadge($status) {
        $classes = [
            'active' => 'success',
            'inactive' => 'secondary',
            'suspended' => 'danger',
            'pending' => 'warning'
        ];
        
        $class = $classes[$status] ?? 'secondary';
        return "<span class=\"badge bg-{$class}\">" . ucfirst($status) . "</span>";
    }

    /**
     * Generate role badge
     */
    public function roleBadge($role) {
        $classes = [
            'super_admin' => 'danger',
            'admin' => 'warning',
            'editor' => 'info',
            'author' => 'primary',
            'user' => 'secondary'
        ];
        
        $class = $classes[$role] ?? 'secondary';
        $displayRole = ucfirst(str_replace('_', ' ', $role));
        return "<span class=\"badge bg-{$class}\">{$displayRole}</span>";
    }

    /**
     * Truncate text
     */
    public function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) <= $length) {
            return $text;
        }
        return substr($text, 0, $length) . $suffix;
    }

    /**
     * Format file size
     */
    public function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Check if current user has permission
     */
    public function hasPermission($permission) {
        $user = $_SESSION['dashboard_user'] ?? null;
        if (!$user) return false;
        
        $role = $user['role'] ?? 'user';
        
        $permissions = [
            'super_admin' => ['*'],
            'admin' => ['manage_users', 'manage_content', 'view_analytics'],
            'editor' => ['manage_content', 'view_analytics'],
            'author' => ['create_content', 'edit_own_content'],
            'user' => ['view_content']
        ];
        
        $userPermissions = $permissions[$role] ?? [];
        
        return in_array('*', $userPermissions) || in_array($permission, $userPermissions);
    }

    /**
     * Generate avatar URL or placeholder
     */
    public function avatar($user, $size = 40) {
        // For now, return a placeholder or initials-based avatar
        $initials = '';
        if (!empty($user['first_name'])) $initials .= strtoupper($user['first_name'][0]);
        if (!empty($user['last_name'])) $initials .= strtoupper($user['last_name'][0]);
        if (empty($initials)) $initials = strtoupper($user['username'][0] ?? 'U');
        
        return [
            'initials' => $initials,
            'size' => $size,
            'url' => null // Could implement Gravatar or file uploads later
        ];
    }
}