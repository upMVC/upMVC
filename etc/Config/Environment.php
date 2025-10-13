<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Environment Configuration
 */

namespace upMVC\Config;

use upMVC\Exceptions\ConfigurationException;

/**
 * Environment
 * 
 * Manages environment variables and configuration
 */
class Environment
{
    /**
     * @var array
     */
    private static array $env = [];

    /**
     * @var bool
     */
    private static bool $loaded = false;

    /**
     * Load environment file
     *
     * @param string $path
     * @return void
     * @throws ConfigurationException
     */
    public static function load(string $path = '.env'): void
    {
        if (self::$loaded) {
            return;
        }

        $baseDir = defined('THIS_DIR') ? THIS_DIR : dirname(__DIR__);
        $envFile = $baseDir . '/' . $path;
        
        if (!file_exists($envFile)) {
            // Create default .env file if it doesn't exist
            self::createDefaultEnvFile($envFile);
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if ($lines === false) {
            throw new ConfigurationException("Failed to read environment file: {$envFile}");
        }

        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments and empty lines
            if (empty($line) || $line[0] === '#') {
                continue;
            }
            
            // Parse key=value pairs
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove inline comments (anything after # outside of quotes)
                if (strpos($value, '#') !== false) {
                    // Check if # is inside quotes
                    $inQuotes = false;
                    $quoteChar = null;
                    for ($i = 0; $i < strlen($value); $i++) {
                        $char = $value[$i];
                        if (($char === '"' || $char === "'") && !$inQuotes) {
                            $inQuotes = true;
                            $quoteChar = $char;
                        } elseif ($char === $quoteChar && $inQuotes) {
                            $inQuotes = false;
                            $quoteChar = null;
                        } elseif ($char === '#' && !$inQuotes) {
                            // Found comment outside quotes - trim it
                            $value = trim(substr($value, 0, $i));
                            break;
                        }
                    }
                }
                
                // Remove quotes if present
                if (strlen($value) >= 2) {
                    if (($value[0] === '"' && $value[-1] === '"') || 
                        ($value[0] === "'" && $value[-1] === "'")) {
                        $value = substr($value, 1, -1);
                    }
                }
                
                // Set environment variable
                self::$env[$key] = $value;
                
                // Also set in $_ENV and putenv for compatibility
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }

        self::$loaded = true;
    }

    /**
     * Get environment variable
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

        return self::$env[$key] ?? $_ENV[$key] ?? getenv($key) ?: $default;
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
        self::$env[$key] = $value;
        $_ENV[$key] = $value;
        putenv("{$key}={$value}");
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

        return self::$env;
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

        return isset(self::$env[$key]) || isset($_ENV[$key]) || getenv($key) !== false;
    }

    /**
     * Get current environment
     *
     * @return string
     */
    public static function current(): string
    {
        return self::get('APP_ENV', 'production');
    }

    /**
     * Check if in development environment
     *
     * @return bool
     */
    public static function isDevelopment(): bool
    {
        return in_array(self::current(), ['development', 'dev', 'local']);
    }

    /**
     * Check if in production environment
     *
     * @return bool
     */
    public static function isProduction(): bool
    {
        return self::current() === 'production';
    }

    /**
     * Check if in testing environment
     *
     * @return bool
     */
    public static function isTesting(): bool
    {
        return in_array(self::current(), ['testing', 'test']);
    }

    /**
     * Create default .env file
     *
     * @param string $path
     * @return void
     */
    private static function createDefaultEnvFile(string $path): void
    {
        $defaultContent = <<<ENV
# upMVC Environment Configuration
# Copy this file to .env and modify according to your needs

# Application Environment (development, production, testing)
APP_ENV=development

# Application Debug Mode (true/false)
APP_DEBUG=true

# Application URL
APP_URL=https://yourdomain.com

# Application Path (empty if in root, e.g., /app if in subdirectory)
APP_PATH=

# Database Configuration
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=upmvc
DB_USER=root
DB_PASS=

# Cache Configuration
CACHE_DRIVER=file
CACHE_TTL=3600

# Session Configuration
SESSION_LIFETIME=120
SESSION_SECURE=false
SESSION_HTTP_ONLY=true

# Mail Configuration
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="upMVC Application"

# Security
APP_KEY=
BCRYPT_ROUNDS=12

# Logging
LOG_LEVEL=debug
LOG_CHANNEL=file

# Rate Limiting
RATE_LIMIT_REQUESTS=60
RATE_LIMIT_MINUTES=1
ENV;

        file_put_contents($path, $defaultContent);
    }

    /**
     * Validate required environment variables
     *
     * @param array $required
     * @return void
     * @throws ConfigurationException
     */
    public static function validateRequired(array $required): void
    {
        $missing = [];
        
        foreach ($required as $key) {
            if (!self::has($key)) {
                $missing[] = $key;
            }
        }
        
        if (!empty($missing)) {
            throw new ConfigurationException(
                'Missing required environment variables: ' . implode(', ', $missing)
            );
        }
    }
}