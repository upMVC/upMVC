<?php

namespace App\Common\Bmvc;

class BaseControllerOrm
{
    /**
     * Render a view
     *
     * @param string $viewPath
     * @param array $data
     * @return mixed
     */
    protected function view($viewPath, $data = [])
    {
        $viewPath = (string) $viewPath;
        if (str_contains($viewPath, '..') || str_contains($viewPath, "\0") || str_contains($viewPath, '\\')) {
            throw new \InvalidArgumentException('Invalid view path');
        }

        $relative = ltrim($viewPath, '/');
        if ($relative === '' || !preg_match('#^[a-zA-Z0-9_./-]+$#', $relative)) {
            throw new \InvalidArgumentException('Invalid view path');
        }

        $modulesRoot = realpath(\THIS_DIR . '/Modules');
        if ($modulesRoot === false) {
            throw new \RuntimeException('Modules directory missing');
        }

        $candidate = $modulesRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative) . '.php';
        $viewFile = realpath($candidate);
        if ($viewFile === false || !str_starts_with($viewFile, $modulesRoot . DIRECTORY_SEPARATOR)) {
            throw new \RuntimeException("View file not found: {$viewPath}");
        }

        // Isolate extract so locals like $modulesRoot never overwrite $data['base']
        (static function (string $__file, array $__data): void {
            extract($__data, EXTR_SKIP);
            include $__file;
        })($viewFile, is_array($data) ? $data : []);
    }

    /**
     * Redirect to another URL
     *
     * @param string $reqRoute The route to redirect to
     * @param string $reqMet The request method
     * @return void
     */
    protected function redirect($reqRoute, $reqMet)
    {
        $baseUrl = \BASE_URL;
        $path = '/' . trim((string) $reqRoute, '/');
        $fullUrl = $baseUrl . $path;

        http_response_code(302);
        header('Location: ' . $fullUrl);
        exit;
    }

    /**
     * Get POST data
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function post($key, $default = null)
    {
        return $_POST[$key] ?? $default;
    }

    /**
     * Get GET data
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function get($key, $default = null)
    {
        return $_GET[$key] ?? $default;
    }

    /**
     * Set flash message
     *
     * @param string $type
     * @param string $message
     * @return void
     */
    protected function setFlash($type, $message)
    {
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Get flash message
     *
     * @param string $type
     * @return string|null
     */
    protected function getFlash($type)
    {
        if (isset($_SESSION['flash'][$type])) {
            $message = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $message;
        }
        return null;
    }
}





