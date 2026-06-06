<?php
/**
 * BaseApiController.php - Base class for JSON API controllers
 *
 * No views, no sessions, no HTML output.
 * Extend this for any module that serves a JSON API.
 *
 * Usage:
 *
 *   class Controller extends BaseApiController
 *   {
 *       public function index(): never
 *       {
 *           $rows = (new Model())->listAll();
 *           $this->success($rows);
 *       }
 *
 *       public function store(): never
 *       {
 *           $data = $this->requireFields(['name', 'email']);
 *           // insert...
 *           $this->success($data, 'Created', 201);
 *       }
 *   }
 *
 * @package upMVC\Common\Bmvc
 */

namespace App\Common\Bmvc;

abstract class BaseApiController
{
    // -----------------------------------------------------------------------
    // Response helpers
    // -----------------------------------------------------------------------

    /**
     * Send a raw JSON response and halt.
     *
     * @param mixed $data
     * @param int   $code HTTP status code
     * @return never
     */
    protected function json(mixed $data, int $code = 200): never
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send a success envelope: {"success":true,"message":"...","data":{...}}
     *
     * @param mixed  $data
     * @param string $message
     * @param int    $code
     * @return never
     */
    protected function success(mixed $data, string $message = 'OK', int $code = 200): never
    {
        $this->json(['success' => true, 'message' => $message, 'data' => $data], $code);
    }

    /**
     * Send an error envelope: {"error":"..."} and halt.
     *
     * @param string $message
     * @param int    $code
     * @return never
     */
    protected function error(string $message, int $code = 400): never
    {
        $this->json(['error' => $message], $code);
    }

    // -----------------------------------------------------------------------
    // Request helpers
    // -----------------------------------------------------------------------

    /**
     * Decode the JSON request body into an array.
     * Returns [] if the body is absent or not valid JSON.
     *
     * @return array
     */
    protected function body(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    /**
     * Return a single value from the decoded request body.
     *
     * @param string $key
     * @param mixed  $default
     * @return mixed
     */
    protected function input(string $key, mixed $default = null): mixed
    {
        return $this->body()[$key] ?? $default;
    }

    /**
     * Assert required fields are present in the request body.
     * Responds 422 and exits if any are missing.
     *
     * @param array $fields
     * @return array The validated body
     */
    protected function requireFields(array $fields): array
    {
        $body    = $this->body();
        $missing = [];
        foreach ($fields as $f) {
            if (!isset($body[$f]) || $body[$f] === '') {
                $missing[] = $f;
            }
        }
        if (!empty($missing)) {
            $this->error('Missing required fields: ' . implode(', ', $missing), 422);
        }
        return $body;
    }
}
