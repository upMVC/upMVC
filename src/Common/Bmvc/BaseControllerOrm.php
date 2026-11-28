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
        //define('THIS_DIR', str_replace("\\","/",dirname(__FILE__, 2)));


        if ($data) {
            extract($data, EXTR_SKIP);
        }

        $relative = str_replace(['\\', "\0", '..'], '', (string) $viewPath);
        $relative = ltrim($relative, '/');
        if ($relative === '' || !preg_match('#^[a-zA-Z0-9_./-]+$#', $relative)) {
            throw new \InvalidArgumentException('Invalid view path');
        }

        $base = realpath(\THIS_DIR . '/Modules');
        if ($base === false) {
            throw new \RuntimeException('Modules directory missing');
        }

        $candidate = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative) . '.php';
        $viewFile = realpath($candidate);
        if ($viewFile === false || !str_starts_with($viewFile, $base . DIRECTORY_SEPARATOR)) {
            throw new \RuntimeException("View file not found: {$viewPath}");
        }

        include $viewFile;

        // Return the buffered content
        //return ob_get_clean();
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





