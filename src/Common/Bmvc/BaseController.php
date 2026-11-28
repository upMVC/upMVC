<?php
/*
 *   Created on Tue Oct 31 2023
 *   Copyright (c) 2023 BitsHost
 *   All rights reserved.
 *
 *   Permission is hereby granted, free of charge, to any person obtaining a copy
 *   of this software and associated documentation files (the "Software"), to deal
 *   in the Software without restriction, including without limitation the rights
 *   to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *   copies of the Software, and to permit persons to whom the Software is
 *   furnished to do so, subject to the following conditions:
 *
 *   The above copyright notice and this permission notice shall be included in all
 *   copies or substantial portions of the Software.
 *
 *   THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *   IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *   FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *   AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *   LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *   OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 *   SOFTWARE.
 *   Here you may host your app for free:
 *   https://bitshost.biz/
 */

namespace App\Common\Bmvc;

use App\Etc\Security;

/**
 * Controller
 */
class BaseController
{
    protected array $routeParams = [];
    protected array $errors = [];
    
    public function setRouteParams(array $params): void
    {
        $this->routeParams = $params;
    }
    
    protected function getParam(string $key, $default = null)
    {
        return $this->routeParams[$key] ?? $_GET[$key] ?? $_POST[$key] ?? $default;
    }
    
    protected function validate(array $data, array $rules): bool
    {
        $this->errors = Security::validateInput($data, $rules);
        return empty($this->errors);
    }
    
    protected function sanitize($input)
    {
        return Security::sanitizeInput($input);
    }
    
    protected function requireCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return Security::validateCsrf((string) $token);
    }
    
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function redirect(string $url, int $status = 302): void
    {
        http_response_code($status);
        header("Location: $url");
        exit;
    }
    
    protected function render(string $view, array $data = []): void
    {
        $data['csrf_token'] = Security::csrfToken();
        $data['errors'] = $this->errors;

        $viewFile = $this->resolveModuleView($view);
        // Isolate extract scope so path locals never overwrite view data (e.g. $base)
        (static function (string $__file, array $__data): void {
            extract($__data, EXTR_SKIP);
            include_once $__file;
        })($viewFile, $data);
    }

    /**
     * Resolve a view path strictly under THIS_DIR/Modules.
     *
     * @throws \InvalidArgumentException|\RuntimeException
     */
    protected function resolveModuleView(string $view): string
    {
        if (str_contains($view, '..') || str_contains($view, "\0") || str_contains($view, '\\')) {
            throw new \InvalidArgumentException('Invalid view path');
        }

        $relative = ltrim($view, '/');
        if ($relative === '' || !preg_match('#^[a-zA-Z0-9_./-]+$#', $relative)) {
            throw new \InvalidArgumentException('Invalid view path');
        }

        $modulesRoot = realpath(THIS_DIR . '/Modules');
        if ($modulesRoot === false) {
            throw new \RuntimeException('Modules directory missing');
        }

        $full = $modulesRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative) . '.php';
        $real = realpath($full);
        if ($real === false || !str_starts_with($real, $modulesRoot . DIRECTORY_SEPARATOR)) {
            throw new \RuntimeException('View file not found: ' . $view);
        }

        return $real;
    }
    
    protected function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    protected function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    protected function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}





