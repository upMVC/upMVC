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

        // Convert view path to file path
        $viewFile = \THIS_DIR . '/Modules/' . str_replace('/', '/', $viewPath) . '.php';

        // Check if view exists
        if (!file_exists($viewFile)) {
            throw new \Exception("View file not found: {$viewFile}");
        }

        // Start output buffering
        // ob_start();

        // Include the view file
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





