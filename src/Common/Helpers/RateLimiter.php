<?php

namespace App\Common\Helpers;

/**
 * RateLimiter — Action-based, file-persisted rate limiting.
 *
 * Complements Security::rateLimit() (simple boolean) with a richer API:
 * per-action defaults, remaining count, retry-after, and security logging.
 *
 * Storage: storage/rate-limits/   (blocked from direct HTTP access)
 * Logs:    storage/logs/
 *
 * Usage:
 *   $ip    = RateLimiter::getClientIp();
 *   $check = RateLimiter::checkAction($ip, 'login');
 *   if (!$check['allowed']) {
 *       http_response_code(429);
 *       die(json_encode(['error' => $check['message']]));
 *   }
 *   // …on failure:
 *   RateLimiter::recordFailure($ip, 'login');
 *   // …on success:
 *   RateLimiter::clearAttempts($ip, 'login');
 *
 * @package upMVC\Common\Helpers
 */
class RateLimiter
{
    /**
     * Default per-action limits.
     * Override individual values via environment variables:
     *   RATE_LIMIT_LOGIN_MAX, RATE_LIMIT_LOGIN_WINDOW, etc.
     */
    private static array $defaults = [
        'login'    => ['max' => 5,   'window' => 900],    // 5 / 15 min
        'signup'   => ['max' => 3,   'window' => 3600],   // 3 / 1 h
        'register' => ['max' => 3,   'window' => 3600],
        'api'      => ['max' => 100, 'window' => 3600],   // generic API
    ];

    private static string $storageDir = '';
    private static string $logDir     = '';

    // =========================================================================
    // Public API
    // =========================================================================

    /**
     * Check if an action is allowed for the given identifier.
     *
     * @param string $identifier  IP address or email
     * @param string $action      e.g. 'login'
     * @param int    $maxAttempts
     * @param int    $windowSeconds
     * @return array{allowed: bool, remaining: int, retry_after: int, message?: string}
     */
    public static function check(
        string $identifier,
        string $action,
        int    $maxAttempts   = 5,
        int    $windowSeconds = 900
    ): array {
        self::ensureStorageDir();

        $file = self::filePath($identifier, $action);
        $data = self::loadData($file);
        $now  = time();

        $data['attempts'] = array_values(array_filter(
            $data['attempts'] ?? [],
            static fn(int $ts) => ($now - $ts) < $windowSeconds
        ));

        $count = count($data['attempts']);

        if ($count >= $maxAttempts) {
            $retryAfter = $windowSeconds - ($now - min($data['attempts']));
            return [
                'allowed'     => false,
                'remaining'   => 0,
                'retry_after' => max(1, $retryAfter),
                'message'     => 'Too many attempts. Try again in ' . ceil($retryAfter / 60) . ' minutes.',
            ];
        }

        return [
            'allowed'     => true,
            'remaining'   => $maxAttempts - $count,
            'retry_after' => 0,
        ];
    }

    /**
     * Check using built-in defaults (env-overridable).
     *
     * @param string $ip
     * @param string $action  Key must exist in self::$defaults or env vars
     * @return array{allowed: bool, remaining: int, retry_after: int, message?: string}
     */
    public static function checkAction(string $ip, string $action): array
    {
        $uc     = strtoupper($action);
        $max    = (int) (getenv("RATE_LIMIT_{$uc}_MAX")    ?: (self::$defaults[$action]['max']    ?? 5));
        $window = (int) (getenv("RATE_LIMIT_{$uc}_WINDOW") ?: (self::$defaults[$action]['window'] ?? 900));
        return self::check($ip, $action, $max, $window);
    }

    /**
     * Record a failed attempt.
     */
    public static function recordFailure(string $identifier, string $action): void
    {
        self::ensureStorageDir();
        $file               = self::filePath($identifier, $action);
        $data               = self::loadData($file);
        $data['attempts'][] = time();
        self::saveData($file, $data);
    }

    /**
     * Clear all recorded attempts (call on successful action).
     */
    public static function clearAttempts(string $identifier, string $action): void
    {
        $file = self::filePath($identifier, $action);
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Determine the real client IP, respecting common proxy headers.
     */
    public static function getClientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'] as $h) {
            if (!empty($_SERVER[$h])) {
                $ip = trim(explode(',', $_SERVER[$h])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    /**
     * Delete stale rate-limit files older than $maxAgeSeconds.
     * Call from a cron job or scheduled task.
     */
    public static function cleanup(int $maxAgeSeconds = 3600): int
    {
        self::ensureStorageDir();
        $deleted = 0;
        $now     = time();
        foreach (glob(self::$storageDir . '*.json') ?: [] as $f) {
            if (($now - filemtime($f)) > $maxAgeSeconds) {
                @unlink($f);
                $deleted++;
            }
        }
        return $deleted;
    }

    // =========================================================================
    // Security Logging
    // =========================================================================

    /**
     * Append a failed-login line to storage/logs/failed-logins.log.
     */
    public static function logFailedLogin(string $email, string $status = 'FAILED', ?int $attemptNumber = null): void
    {
        self::ensureLogDir();
        $info = $attemptNumber ? " ({$attemptNumber} attempt(s))" : '';
        $line = sprintf("[%s] IP: %s | Email: %s | %s%s\n",
            date('Y-m-d H:i:s'), self::getClientIp(), $email, $status, $info);
        @file_put_contents(self::$logDir . 'failed-logins.log', $line, FILE_APPEND | LOCK_EX);
    }

    /**
     * Append a successful-login line to storage/logs/logins.log.
     */
    public static function logSuccessfulLogin(string $email): void
    {
        self::ensureLogDir();
        $line = sprintf("[%s] IP: %s | Email: %s | SUCCESS\n",
            date('Y-m-d H:i:s'), self::getClientIp(), $email);
        @file_put_contents(self::$logDir . 'logins.log', $line, FILE_APPEND | LOCK_EX);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    private static function ensureStorageDir(): void
    {
        if (self::$storageDir === '') {
            // src/Common/Helpers/ → up 3 levels → project root
            self::$storageDir = dirname(__DIR__, 3)
                . DIRECTORY_SEPARATOR . 'storage'
                . DIRECTORY_SEPARATOR . 'rate-limits'
                . DIRECTORY_SEPARATOR;
        }
        if (!is_dir(self::$storageDir)) {
            mkdir(self::$storageDir, 0755, true);
            file_put_contents(self::$storageDir . '.htaccess', "Require all denied\n");
        }
    }

    private static function ensureLogDir(): void
    {
        if (self::$logDir === '') {
            self::$logDir = dirname(__DIR__, 3)
                . DIRECTORY_SEPARATOR . 'storage'
                . DIRECTORY_SEPARATOR . 'logs'
                . DIRECTORY_SEPARATOR;
        }
        if (!is_dir(self::$logDir)) {
            mkdir(self::$logDir, 0755, true);
            file_put_contents(self::$logDir . '.htaccess', "Require all denied\n");
        }
    }

    private static function filePath(string $identifier, string $action): string
    {
        return self::$storageDir . $action . '_' . md5($identifier) . '.json';
    }

    private static function loadData(string $file): array
    {
        if (file_exists($file)) {
            $content = file_get_contents($file);
            return json_decode((string) $content, true) ?? ['attempts' => []];
        }
        return ['attempts' => []];
    }

    private static function saveData(string $file, array $data): void
    {
        file_put_contents($file, json_encode($data), LOCK_EX);
    }
}
