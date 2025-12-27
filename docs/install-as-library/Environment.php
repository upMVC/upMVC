<?php
/*
 *   Enhanced Environment example for "install as library" usage.
 *   NOT active in core yet – kept here as reference only.
 */

namespace App\Etc\Config;

use App\Etc\Exceptions\ConfigurationException;

class Environment
{
    private static array $vars = [];
    private static bool $loaded = false;
    private static ?string $envFile = null;

    /**
     * Load environment variables from .env file
     *
     * Resolution order:
     *  1. Explicit parameter ($envFile)
     *  2. env vars: UPMVC_ENV_FILE or APP_ENV_FILE
     *  3. Project root .env (one level above src)
     *  4. Fallback: src/Etc/.env (current core behaviour)
     */
    public static function load(?string $envFile = null): void
    {
        if (self::$loaded) {
            return;
        }

        // Base Etc directory from library (src/Etc)
        $etcDir = dirname(__DIR__);

        // 1) Explicit path passed in wins
        if ($envFile !== null) {
            self::$envFile = $envFile;
        } else {
            // 2) Allow host application to point to a custom .env file
            //    via environment variable (set e.g. in public/index.php)
            $customEnvFile = getenv('UPMVC_ENV_FILE') ?: getenv('APP_ENV_FILE');

            if (!empty($customEnvFile)) {
                self::$envFile = $customEnvFile;
            } else {
                // 3) Fallback: try project root .env (one level above src)
                $projectRoot = dirname($etcDir);
                $rootEnv = $projectRoot . '/.env';

                if (file_exists($rootEnv)) {
                    self::$envFile = $rootEnv;
                } else {
                    // 4) Final fallback: original behaviour (src/Etc/.env)
                    self::$envFile = $etcDir . '/.env';
                }
            }
        }

        // Create .env if it doesn't exist at the resolved location
        if (!file_exists(self::$envFile)) {
            self::createDefaultEnvFile();
        }

        // Mark as loaded before parsing to avoid recursive load() calls
        self::$loaded = true;

        self::loadEnvFile(self::$envFile);
        self::loadSystemVars();
    }

    public static function get(string $key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$vars[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        if (!self::$loaded) {
            self::load();
        }

        self::$vars[$key] = $value;
        putenv("{$key}={$value}");
    }

    public static function has(string $key): bool
    {
        if (!self::$loaded) {
            self::load();
        }

        return isset(self::$vars[$key]);
    }

    public static function all(): array
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$vars;
    }

    private static function loadEnvFile(string $file): void
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new ConfigurationException("Environment file not found or not readable: {$file}");
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                if (strpos($value, '#') !== false) {
                    if (!preg_match('/^["\']/', $value)) {
                        $value = explode('#', $value)[0];
                        $value = trim($value);
                    }
                }

                $value = self::parseValue($value);

                self::$vars[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    private static function parseValue(string $value): string
    {
        if (preg_match('/^( ["\'] )(.*)\\1$/x', $value, $matches)) {
            $value = $matches[2];
        }

        $value = preg_replace_callback('/\$\{([A-Z_][A-Z0-9_]*)\}/', function ($matches) {
            return self::get($matches[1], '');
        }, $value);

        $value = preg_replace_callback('/\$([A-Z_][A-Z0-9_]*)/', function ($matches) {
            return self::get($matches[1], '');
        }, $value);

        return $value;
    }

    private static function loadSystemVars(): void
    {
        foreach ($_ENV as $key => $value) {
            if (!isset(self::$vars[$key])) {
                self::$vars[$key] = $value;
            }
        }

        foreach ($_SERVER as $key => $value) {
            if (!isset(self::$vars[$key]) && is_string($value)) {
                self::$vars[$key] = $value;
            }
        }

        $envVars = getenv();
        if (is_array($envVars)) {
            foreach ($envVars as $key => $value) {
                if (!isset(self::$vars[$key])) {
                    self::$vars[$key] = $value;
                }
            }
        }
    }

    private static function createDefaultEnvFile(): void
    {
        $template = <<<'ENV'
# Application Configuration
APP_NAME="upMVC Application"
APP_ENV=production
APP_DEBUG=false
APP_KEY=
APP_TIMEZONE=UTC
APP_LOCALE=en

# Domain Configuration
DOMAIN_NAME=https://yourdomain.com
SITE_PATH=

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=upmvc
DB_USER=root
DB_PASS=

# Cache Configuration
CACHE_DRIVER=file
CACHE_TTL=3600

# Session Configuration
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_EXPIRE_ON_CLOSE=false
SESSION_ENCRYPT=false
SESSION_COOKIE=upmvc_session
SESSION_DOMAIN=
SESSION_SECURE=false
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Security Configuration
CSRF_PROTECTION=true
RATE_LIMIT=100

# CORS Configuration
CORS_ENABLED=false
CORS_ALLOWED_ORIGINS=*
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_ENCRYPTION=tls
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="${APP_NAME}"

# Logging Configuration
LOG_CHANNEL=file
LOG_LEVEL=debug

# Log path (relative to app root or absolute)
LOG_PATH=src/logs

ENV;

        file_put_contents(self::$envFile, $template);
    }

    public static function isDevelopment(): bool
    {
        return strtolower(self::get('APP_ENV', 'production')) === 'development';
    }

    public static function isProduction(): bool
    {
        return strtolower(self::get('APP_ENV', 'production')) === 'production';
    }

    public static function isTesting(): bool
    {
        return strtolower(self::get('APP_ENV', 'production')) === 'testing';
    }
}
