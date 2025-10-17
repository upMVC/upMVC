<?php

/*
 *   Created on October 17, 2025
 *   
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 */

namespace Reacthmr;

use Reacthmr\View;
use Reacthmr\Model;

/**
 * ReactHMR Controller
 * 
 * Demonstrates Hot Module Reload for ES Modules using Server-Sent Events (SSE)
 * No webpack, no babel - just PHP watching files and browser auto-reloading
 */
class Controller 
{
    private $watchPaths = [
        'modules/reacthmr/templates/',
        'modules/reacthmr/components/',
        'modules/reacthmr/View.php',
        'modules/reacthmr/Controller.php'
    ];

    public function display($reqRoute, $reqMet)
    {
        // Debug: Log the route (remove this later)
        error_log("[ReactHMR] Route requested: " . $reqRoute);
        
        switch ($reqRoute) {
            case '/reacthmr':
                $this->index($reqRoute, $reqMet);
                break;
            
            case '/reacthmr/hmr':
                $this->hmrStream();
                break;
            
            case '/reacthmr/component':
                $this->serveComponent();
                break;
                
            default:
                // If route doesn't match exactly, show index
                if (strpos($reqRoute, '/reacthmr') === 0) {
                    $this->index($reqRoute, $reqMet);
                } else {
                    $this->index($reqRoute, $reqMet);
                }
                break;
        }
    }

    /**
     * Main page - Show ES modules with HMR
     */
    private function index($reqRoute, $reqMet) 
    {
        $model = new Model(); 
        $view = new View();

        // Get some data to pass to JS components
        $data = [
            'users' => $model->getSampleUsers(),
            'stats' => $model->getStats(),
            'todos' => $model->getTodos(),
            'view' => 'index'
        ];

        $view->render($data); 
    }

    /**
     * HMR Stream - Server-Sent Events for file watching
     * Sends 'reload' event when watched files change
     */
    private function hmrStream()
    {
        // Set headers for SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no'); // Disable nginx buffering

        // Disable output buffering
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Store initial file modification times
        $fileHashes = $this->getFileHashes();

        // Keep connection alive and check for changes
        $timeout = 30; // seconds before reconnect
        $startTime = time();

        while (true) {
            // Check if connection is still alive
            if (connection_aborted()) {
                break;
            }

            // Check for file changes
            $currentHashes = $this->getFileHashes();
            
            if ($currentHashes !== $fileHashes) {
                // Files changed - send reload event
                echo "event: reload\n";
                echo "data: " . json_encode([
                    'timestamp' => time(),
                    'message' => 'Files changed - reloading...'
                ]) . "\n\n";
                
                // Flush output
                if (ob_get_level()) {
                    ob_flush();
                }
                flush();

                // Update file hashes
                $fileHashes = $currentHashes;
            }

            // Send heartbeat to keep connection alive
            if (time() - $startTime > 10) {
                echo ": heartbeat\n\n";
                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
                $startTime = time();
            }

            // Reconnect after timeout
            if (time() - $startTime > $timeout) {
                break;
            }

            // Sleep before next check (1 second)
            sleep(1);
        }
    }

    /**
     * Get file hashes for all watched paths
     * 
     * @return string Combined hash of all file modification times
     */
    private function getFileHashes(): string
    {
        $hash = '';

        foreach ($this->watchPaths as $path) {
            $fullPath = THIS_DIR . '/' . $path;

            if (is_file($fullPath)) {
                // Single file
                $hash .= filemtime($fullPath);
            } elseif (is_dir($fullPath)) {
                // Directory - watch all files recursively
                $files = $this->getFilesRecursive($fullPath);
                foreach ($files as $file) {
                    $hash .= filemtime($file);
                }
            }
        }

        return md5($hash);
    }

    /**
     * Get all files in directory recursively
     * 
     * @param string $dir Directory path
     * @return array Array of file paths
     */
    private function getFilesRecursive(string $dir): array
    {
        $files = [];
        
        if (!is_dir($dir)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                // Only watch relevant file types
                $ext = $file->getExtension();
                if (in_array($ext, ['php', 'js', 'html', 'css'])) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    /**
     * Serve JavaScript component
     * Serves ES module components from the components directory
     */
    private function serveComponent()
    {
        header('Content-Type: application/javascript; charset=utf-8');
        header('Access-Control-Allow-Origin: *'); // Allow CORS
        
        // Get component file name from query parameter
        $file = $_GET['file'] ?? 'TodoApp.js';
        
        // Sanitize filename (security)
        $file = basename($file);
        
        // Read component file
        $componentPath = THIS_DIR . '/modules/reacthmr/components/' . $file;
        
        if (file_exists($componentPath)) {
            readfile($componentPath);
        } else {
            http_response_code(404);
            echo "console.error('Component not found: " . htmlspecialchars($file) . "');";
            echo "export default function() { return 'Component not found'; }";
        }
        exit; // Important: stop execution after serving file
    }
}
