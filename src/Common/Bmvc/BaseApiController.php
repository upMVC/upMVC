<?php
/**
 * BaseApiController.php - Base class for all JSON API modules
 *
 * No views, no sessions, no HTML. Every SaaS domain controller extends this.
 * Tenant and user context come from $GLOBALS, pre-populated by middleware.
 *
 * Subclass usage:
 *
 *   class Controller extends BaseApiController
 *   {
 *       public function index(): never
 *       {
 *           $rows = (new Model($this->tenantId))->listAll();
 *           $this->success($rows);
 *       }
 *
 *       public function store(): never
 *       {
 *           $data = $this->body();
 *           // validate, insert…
 *           $this->success($data, 'Created', 201);
 *       }
 *   }
 *
 * @package upMVC\Common\Bmvc
 */

namespace App\Common\Bmvc;

use App\Etc\Database;

abstract class BaseApiController
{
    /** Current tenant row (set by TenantMiddleware) */
    protected array $tenant;

    /** Current user JWT payload (set by JwtAuthMiddleware) */
    protected array $user;

    /** Shortcut: current tenant ID */
    protected int $tenantId;

    /** Active PDO connection */
    protected \PDO $db;

    public function __construct()
    {
        $this->tenant   = $GLOBALS['current_tenant'] ?? [];
        $this->user     = $GLOBALS['current_user']   ?? [];
        $this->tenantId = (int) ($this->tenant['id'] ?? 0);
        $this->db       = (new Database())->getConnection();
    }

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
     * Send a success envelope response.
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
     * Send an error envelope response and halt.
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
     * Returns an empty array if the body is absent or not valid JSON.
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
     * Return a value from the decoded request body, with a fallback default.
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
     * Calls $this->error() (and exits) with a 422 if any are missing.
     *
     * @param array $fields
     * @return array The validated body
     */
    protected function requireFields(array $fields): array
    {
        $body = $this->body();
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
