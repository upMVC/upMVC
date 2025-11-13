<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Environment Manager
 */

namespace App\Etc\Config;

use App\Etc\Exceptions\ConfigurationException;

/**
 * Environment
 * 
 * Environment variable management with .env file support
 */
class Environment
{
    /**
     * @var array
     */
    private static array $vars = [];

    /**
     * @var bool
     */
    private static bool $loaded = false;

    /**
     * @var string|null
     */
    private static ?string $envFile = null;

    /**
     * Load environment variables from .env file
     *
     * @param string|null $envFile
     * @return void
     */
    public static function load(?string $envFile = null): void
    {
        if (self::$loaded) {
            return;
        }

        // .env file is located in src/Etc/ directory
        $etcDir = dirname(__DIR__); // Gets src/Etc/ directory
        self::$envFile = $envFile ?? $etcDir . '/.env';

        // Create .env if it doesn't exist
        if (!file_exists(self::$envFile)) {
            self::createDefaultEnvFile();
        }

        // Load .env file
        self::loadEnvFile(self::$envFile);

        // Load system environment variables
        self::loadSystemVars();

        self::$loaded = true;
    }

    /**
     * Get environment variable value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$vars[$key] ?? $default;
    }

    /**
     * Set environment variable
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public static function set(string $key, $value): void
    {
        if (!self::$loaded) {
            self::load();
        }

        self::$vars[$key] = $value;
        putenv("{$key}={$value}");
    }

    /**
     * Check if environment variable exists
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        if (!self::$loaded) {
            self::load();
        }

        return isset(self::$vars[$key]);
    }

    /**
     * Get all environment variables
     *
     * @return array
     */
    public static function all(): array
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$vars;
    }

    /**
     * Load .env file
     *
     * @param string $file
     * @return void
     * @throws ConfigurationException
     */
    private static function loadEnvFile(string $file): void
    {
        if (!file_exists($file) || !is_readable($file)) {
            throw new ConfigurationException("Environment file not found or not readable: {$file}");
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Skip comments and empty lines
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }

            // Parse line
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remove quotes from value
                $value = self::parseValue($value);

                // Set variable
                self::$vars[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }

    /**
     * Parse environment variable value
     *
     * @param string $value
     * @return string
     */
    private static function parseValue(string $value): string
    {
        // Remove surrounding quotes
        if (preg_match('/^(["\'])(.*)\\1$/', $value, $matches)) {
            $value = $matches[2];
        }

        // Handle variable substitution ${VAR}
        $value = preg_replace_callback('/\$\{([A-Z_][A-Z0-9_]*)\}/', function ($matches) {
            return self::get($matches[1], '');
        }, $value);

        // Handle variable substitution $VAR
        $value = preg_replace_callback('/\$([A-Z_][A-Z0-9_]*)/', function ($matches) {
            return self::get($matches[1], '');
        }, $value);

        return $value;
    }

    /**
     * Load system environment variables
     *
     * @return void
     */
    private static function loadSystemVars(): void
    {
        // Load $_ENV
        foreach ($_ENV as $key => $value) {
            if (!isset(self::$vars[$key])) {
                self::$vars[$key] = $value;
            }
        }

        // Load $_SERVER
        foreach ($_SERVER as $key => $value) {
            if (!isset(self::$vars[$key]) && is_string($value)) {
                self::$vars[$key] = $value;
            }
        }

        // Load getenv()
        $envVars = getenv();
        if (is_array($envVars)) {
            foreach ($envVars as $key => $value) {
                if (!isset(self::$vars[$key])) {
                    self::$vars[$key] = $value;
                }
            }
        }
    }

    /**
     * Create default .env file
     *
     * @return void
     */
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

ENV;

        file_put_contents(self::$envFile, $template);
    }

    /**
     * Check if running in development environment
     *
     * @return bool
     */
    public static function isDevelopment(): bool
    {
        return strtolower(self::get('APP_ENV', 'production')) === 'development';
    }

    /**
     * Check if running in production environment
     *
     * @return bool
     */
    public static function isProduction(): bool
    {
        return strtolower(self::get('APP_ENV', 'production')) === 'production';
    }

    /**
     * Check if running in testing environment
     *
     * @return bool
     */
    public static function isTesting(): bool
    {
        return strtolower(self::get('APP_ENV', 'production')) === 'testing';
    }
}
