<?php
/**
 * Database.php - PDO Database Connection Manager (Hybrid Configuration)
 * 
 * This class provides database connectivity for upMVC with hybrid configuration:
 * - PDO-based MySQL connection
 * - HYBRID CONFIG: .env first, ConfigDatabase as fallback
 * - UTF-8 character encoding support
 * - Connection error handling
 * 
 * Configuration Priority (Hybrid Approach):
 * 1. .env file (RECOMMENDED for production)
 *    - DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_CHARSET
 * 2. ConfigDatabase.php (fallback for development)
 *    - db.host, db.name, db.user, db.pass
 * 
 * Usage:
 * - Instantiate to load database configuration
 * - Call getConnection() to establish PDO connection
 * - Returns PDO object for database operations
 * 
 * Security Best Practices:
 * - Production: Use .env file (not committed to Git)
 * - Development: Use ConfigDatabase.php (with dummy data)
 * - Never commit real credentials to version control
 * 
 * @package upMVC
 * @author BitsHost
 * @copyright 2025 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 */

namespace App\Etc;

use PDO;
use PDOException;
use App\Etc\Config\Environment;

class Database
{
    // ========================================
    // Properties
    // ========================================
    
    /**
     * Database host address
     * 
     * @var string
     */
    private $host;
    
    /**
     * Database name
     * 
     * @var string
     */
    private $databaseName;
    
    /**
     * Database username
     * 
     * @var string
     */
    private $username;
    
    /**
     * Database password
     * 
     * @var string
     */
    private $password;
    
    /**
     * Database port
     * 
     * @var int
     */
    private $port;
    
    /**
     * Database charset
     * 
     * @var string
     */
    private $charset;
    
    /**
     * PDO connection object
     * 
     * @var PDO|null
     */
    public $conn;

    // ========================================
    // Initialization
    // ========================================

    /**
     * Constructor - Load database configuration (Hybrid)
     * 
     * Automatically loads database credentials using hybrid approach:
     * 1. Checks .env file first (production priority)
     * 2. Falls back to ConfigDatabase (development)
     */
    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * Load database configuration using hybrid approach
     * 
     * PRIORITY ORDER:
     * 1. .env file (DB_HOST, DB_NAME, DB_USER, DB_PASS, DB_PORT, DB_CHARSET)
     * 2. ConfigDatabase.php (db.host, db.name, db.user, db.pass)
     * 
     * This allows:
     * - Production: Secure .env credentials (not in Git)
     * - Development: Convenient ConfigDatabase.php defaults
     * - Easy deployment: Just update .env on server
     * 
     * @return void
     * 
     * @example
     * // Production .env:
     * // DB_HOST=prod-server.com
     * // DB_NAME=production_db
     * // DB_USER=prod_user
     * // DB_PASS=secure_password
     * 
     * @example
     * // Development ConfigDatabase.php:
     * // 'db.host' => 'localhost'
     * // 'db.name' => 'dev_database'
     */
    private function loadConfig()
    {
        // Ensure Environment is loaded
        Environment::load();
        
        // Check if .env database config exists (production priority)
        if (Environment::has('DB_HOST')) {
            // Use .env configuration (RECOMMENDED for production)
            $this->host = Environment::get('DB_HOST');
            $this->databaseName = Environment::get('DB_NAME', '');
            $this->username = Environment::get('DB_USER', '');
            $this->password = Environment::get('DB_PASS', '');
            $this->port = Environment::get('DB_PORT', 3306);
            $this->charset = Environment::get('DB_CHARSET', 'utf8mb4');
        } else {
            // Fallback to ConfigDatabase.php (development)
            $this->host = ConfigDatabase::get('db.host');
            $this->databaseName = ConfigDatabase::get('db.name');
            $this->username = ConfigDatabase::get('db.user');
            $this->password = ConfigDatabase::get('db.pass');
            $this->port = ConfigDatabase::get('db.port', 3306);
            $this->charset = ConfigDatabase::get('db.charset', 'utf8mb4');
        }
    }

    // ========================================
    // Connection Management
    // ========================================

    /**
     * Establish and return PDO database connection
     * 
     * Creates a new PDO connection to MySQL database with:
     * - Configurable charset (from .env or config)
     * - Optional port specification
     * - Error handling for connection failures
     * 
     * Connection uses credentials from hybrid config (see loadConfig()).
     * 
    * On connection failure, a PDOException is thrown.
    * 
    * @return PDO|null PDO connection object or null on failure
    * @throws PDOException
     * 
     * @example
     * // Basic usage
     * $database = new Database();
     * $conn = $database->getConnection();
     * if ($conn) {
     *     // Use $conn for database operations
     *     $stmt = $conn->prepare("SELECT * FROM users");
     *     $stmt->execute();
     * }
     */
    public function getConnection()
    {
        $this->conn = null;
        
        try {
            // Build DSN with optional port
            $dsn = "mysql:host=" . $this->host;
            
            // Add port if not default
            if ($this->port && $this->port != 3306) {
                $dsn .= ";port=" . $this->port;
            }
            
            $dsn .= ";dbname=" . $this->databaseName;
            $dsn .= ";charset=" . $this->charset;
            
            // Create PDO connection
            $this->conn = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
        } catch (PDOException $exception) {
            // Let the global error handler or caller manage connection failures
            throw $exception;
        }
        
        return $this->conn;
    }
}





