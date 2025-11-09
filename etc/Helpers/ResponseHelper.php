<?php
/**
 * ResponseHelper.php - HTTP Response Helpers
 * 
 * Handles views, HTTP status codes, and JSON responses.
 * 
 * @package upMVC\Helpers
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 */

namespace upMVC\Helpers;

class ResponseHelper
{
    /**
     * Render a view file
     */
    public static function view(string $path, array $data = []): void
    {
        extract($data);
        $viewPath = __DIR__ . '/../../modules/' . str_replace('.', '/', $path) . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \RuntimeException("View not found: {$path}");
        }
        
        include $viewPath;
    }
    
    /**
     * Abort with HTTP status code
     */
    public static function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        
        if ($message) {
            echo $message;
        }
        
        exit;
    }
    
    /**
     * Return JSON response
     */
    public static function json($data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
