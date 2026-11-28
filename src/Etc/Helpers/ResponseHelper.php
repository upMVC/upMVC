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

namespace App\Etc\Helpers;

class ResponseHelper
{
    /**
     * Render a view file
     */
    public static function view(string $path, array $data = []): void
    {
        if (str_contains($path, '..') || str_contains($path, '\\') || str_contains($path, "\0")) {
            throw new \InvalidArgumentException('Invalid view path');
        }
        $relative = ltrim(str_replace('.', '/', $path), '/');
        $modulesRoot = realpath(__DIR__ . '/../../Modules');
        if ($modulesRoot === false) {
            throw new \RuntimeException('Modules directory missing');
        }
        $candidate = $modulesRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative) . '.php';
        $viewPath = realpath($candidate);
        if ($viewPath === false || !str_starts_with($viewPath, $modulesRoot . DIRECTORY_SEPARATOR)) {
            throw new \RuntimeException("View not found: {$path}");
        }

        (static function (string $__file, array $__data): void {
            extract($__data, EXTR_SKIP);
            include $__file;
        })($viewPath, $data);
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





