<?php
/*
 *   Created on October 11, 2025
 *   Copyright (c) 2025 BitsHost
 *   All rights reserved.
 *
 *   Enhanced upMVC - Configuration Manager
 */

namespace App\Etc\Config;

use App\Etc\Config\Environment;
use App\Etc\Exceptions\ConfigurationException;

/**
 * ConfigManager
 * 
 * Enhanced configuration management with environment support
 */
class ConfigManager
{
    /**
     * @var array
     */
    private static array $config = [];

    /**
     * @var bool
     */
    private static bool $loaded = false;

    /**
     * Load all configuration files
     *
     * @return void
     */
    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        // Load environment first
        Environment::load();

        // Load configuration files
        self::loadConfigFiles();

        self::$loaded = true;
    }

    /**
     * Get configuration value using dot notation
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

        return self::getNestedValue(self::$config, $key, $default);
    }

    /**
     * Set configuration value using dot notation
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

        self::setNestedValue(self::$config, $key, $value);
    }

    /**
     * Check if configuration key exists
     *
     * @param string $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::getNestedValue(self::$config, $key) !== null;
    }

    /**
     * Get all configuration
     *
     * @return array
     */
    public static function all(): array
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$config;
    }

    /**
     * Load configuration files from Etc/ directory
     *
     * @return void
     */
    private static function loadConfigFiles(): void
    {
        $baseDir = defined('THIS_DIR') ? THIS_DIR : dirname(dirname(dirname(__DIR__)));
        $configDir = $baseDir . '/src/Etc';
        $configFiles = [
            'app' => $configDir . '/Config.php',
            'database' => $configDir . '/ConfigDatabase.php'
        ];

        foreach ($configFiles as $name => $file) {
            if (file_exists($file)) {
                self::loadConfigFile($name, $file);
            }
        }

        // Load additional config files
        self::loadAdditionalConfigs();
    }

    /**
     * Load a single configuration file
     *
     * @param string $name
     * @param string $file
     * @return void
     */
    private static function loadConfigFile(string $name, string $file): void
    {
        try {
            if ($name === 'app') {
                self::loadAppConfig();
            } elseif ($name === 'database') {
                self::loadDatabaseConfig();
            }
        } catch (\Exception $e) {
            throw new ConfigurationException("Failed to load config file {$file}: " . $e->getMessage());
        }
    }

    /**
     * Load application configuration
     *
     * @return void
     */
    private static function loadAppConfig(): void
    {
        self::$config['app'] = [
            'name' => Environment::get('APP_NAME', 'upMVC Application'),
            'env' => Environment::get('APP_ENV', 'production'),
            'debug' => filter_var(Environment::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN),
            'url' => Environment::get('DOMAIN_NAME', 'https://yourdomain.com'),
            'path' => Environment::get('SITE_PATH', ''),
            'key' => Environment::get('APP_KEY', ''),
            'timezone' => Environment::get('APP_TIMEZONE', 'UTC'),
            'locale' => Environment::get('APP_LOCALE', 'en'),
            'cors' => [
                'enabled' => filter_var(Environment::get('CORS_ENABLED', 'false'), FILTER_VALIDATE_BOOLEAN),
                'allowed_origins' => explode(',', Environment::get('CORS_ALLOWED_ORIGINS', '*')),
                'allowed_methods' => explode(',', Environment::get('CORS_ALLOWED_METHODS', 'GET,POST,PUT,DELETE,OPTIONS')),
                'allowed_headers' => explode(',', Environment::get('CORS_ALLOWED_HEADERS', 'Content-Type,Authorization')),
            ],
        ];
    }

    /**
     * Load database configuration
     *
     * @return void
     */
    private static function loadDatabaseConfig(): void
    {
        self::$config['database'] = [
            'default' => Environment::get('DB_CONNECTION', 'mysql'),
            'connections' => [
                'mysql' => [
                    'driver' => 'mysql',
                    'host' => Environment::get('DB_HOST', '127.0.0.1'),
                    'port' => Environment::get('DB_PORT', '3306'),
                    'database' => Environment::get('DB_NAME', 'upmvc'),
                    'username' => Environment::get('DB_USER', 'root'),
                    'password' => Environment::get('DB_PASS', ''),
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'options' => [
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                        \PDO::ATTR_EMULATE_PREPARES => false,
                    ]
                ]
            ]
        ];
    }

    /**
     * Load additional configuration files
     *
     * @return void
     */
    private static function loadAdditionalConfigs(): void
    {
        $baseDir = defined('THIS_DIR') ? THIS_DIR : dirname(dirname(dirname(__DIR__)));
        
        // Cache configuration
        self::$config['cache'] = [
            'default' => Environment::get('CACHE_DRIVER', 'file'),
            'stores' => [
                'file' => [
                    'driver' => 'file',
                    'path' => $baseDir . '/storage/cache',
                ],
                'array' => [
                    'driver' => 'array',
                ],
            ],
            'ttl' => (int) Environment::get('CACHE_TTL', 3600),
        ];

        // Session configuration
        self::$config['session'] = [
            'driver' => Environment::get('SESSION_DRIVER', 'file'),
            'lifetime' => (int) Environment::get('SESSION_LIFETIME', 120),
            'expire_on_close' => filter_var(Environment::get('SESSION_EXPIRE_ON_CLOSE', 'false'), FILTER_VALIDATE_BOOLEAN),
            'encrypt' => filter_var(Environment::get('SESSION_ENCRYPT', 'false'), FILTER_VALIDATE_BOOLEAN),
            'files' => $baseDir . '/storage/sessions',
            'connection' => null,
            'table' => 'sessions',
            'store' => null,
            'lottery' => [2, 100],
            'cookie' => [
                'name' => Environment::get('SESSION_COOKIE', 'upmvc_session'),
                'path' => '/',
                'domain' => Environment::get('SESSION_DOMAIN', null),
                'secure' => filter_var(Environment::get('SESSION_SECURE', 'false'), FILTER_VALIDATE_BOOLEAN),
                'http_only' => filter_var(Environment::get('SESSION_HTTP_ONLY', 'true'), FILTER_VALIDATE_BOOLEAN),
                'same_site' => Environment::get('SESSION_SAME_SITE', 'lax'),
            ],
        ];

        // Security configuration
        self::$config['security'] = [
            'csrf_protection' => filter_var(Environment::get('CSRF_PROTECTION', 'true'), FILTER_VALIDATE_BOOLEAN),
            'rate_limit' => (int) Environment::get('RATE_LIMIT', 100),
        ];

        // Mail configuration
        self::$config['mail'] = [
            'default' => Environment::get('MAIL_MAILER', 'smtp'),
            'mailers' => [
                'smtp' => [
                    'transport' => 'smtp',
                    'host' => Environment::get('MAIL_HOST'),
                    'port' => Environment::get('MAIL_PORT', 587),
                    'encryption' => Environment::get('MAIL_ENCRYPTION', 'tls'),
                    'username' => Environment::get('MAIL_USERNAME'),
                    'password' => Environment::get('MAIL_PASSWORD'),
                ],
            ],
            'from' => [
                'address' => Environment::get('MAIL_FROM_ADDRESS', 'noreply@example.com'),
                'name' => Environment::get('MAIL_FROM_NAME', 'upMVC'),
            ],
        ];

        // Logging configuration
        self::$config['logging'] = [
            'default' => Environment::get('LOG_CHANNEL', 'file'),
            'channels' => [
                'file' => [
                    'driver' => 'file',
                    'path' => $baseDir . '/logs/app.log',
                    'level' => Environment::get('LOG_LEVEL', 'debug'),
                ],
                'daily' => [
                    'driver' => 'daily',
                    'path' => $baseDir . '/logs/app.log',
                    'level' => Environment::get('LOG_LEVEL', 'debug'),
                    'days' => 14,
                ],
            ],
        ];
    }

    /**
     * Get nested value using dot notation
     *
     * @param array $array
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private static function getNestedValue(array $array, string $key, $default = null)
    {
        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return $default;
            }
            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Set nested value using dot notation
     *
     * @param array &$array
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private static function setNestedValue(array &$array, string $key, $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $i => $segment) {
            if ($i === count($keys) - 1) {
                $current[$segment] = $value;
            } else {
                if (!isset($current[$segment]) || !is_array($current[$segment])) {
                    $current[$segment] = [];
                }
                $current = &$current[$segment];
            }
        }
    }

    /**
     * Validate configuration
     *
     * @return void
     * @throws ConfigurationException
     */
    public static function validate(): void
    {
        $required = [
            'app.url',
            'database.connections.mysql.host',
            'database.connections.mysql.database'
        ];

        $missing = [];
        foreach ($required as $key) {
            if (!self::has($key)) {
                $missing[] = $key;
            }
        }

        if (!empty($missing)) {
            throw new ConfigurationException(
                'Missing required configuration keys: ' . implode(', ', $missing)
            );
        }
    }
}
