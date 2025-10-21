<?php
/**
 * Database.php - PDO Database Connection Manager
 * 
 * This class provides database connectivity for upMVC:
 * - PDO-based MySQL connection
 * - Configuration loading from ConfigDatabase
 * - UTF-8 character encoding support
 * - Connection error handling
 * 
 * Usage:
 * - Instantiate to load database configuration
 * - Call getConnection() to establish PDO connection
 * - Returns PDO object for database operations
 * 
 * Configuration:
 * - Requires ConfigDatabase class with db.host, db.name, db.user, db.pass
 * 
 * @package upMVC
 * @author BitsHost
 * @copyright 2023 BitsHost
 * @license MIT License
 * @link https://bitshost.biz/
 */

namespace upMVC;

use PDO;
use PDOException;

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
     * PDO connection object
     * 
     * @var PDO|null
     */
    public $conn;

    // ========================================
    // Initialization
    // ========================================

    /**
     * Constructor - Load database configuration
     * 
     * Automatically loads database credentials from ConfigDatabase.
     */
    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * Load database configuration from ConfigDatabase
     * 
     * Retrieves database credentials:
     * - db.host: Database server address
     * - db.name: Database name
     * - db.user: Database username
     * - db.pass: Database password
     * 
     * @return void
     */
    private function loadConfig()
    {
        $this->host = ConfigDatabase::get('db.host');
        $this->databaseName = ConfigDatabase::get('db.name');
        $this->username = ConfigDatabase::get('db.user');
        $this->password = ConfigDatabase::get('db.pass');
    }

    // ========================================
    // Connection Management
    // ========================================

    /**
     * Establish and return PDO database connection
     * 
     * Creates a new PDO connection to MySQL database with:
     * - UTF-8 character encoding
     * - Error handling for connection failures
     * 
     * On connection failure, outputs error message and returns null.
     * 
     * @return PDO|null PDO connection object or null on failure
     * 
     * @example
     * $database = new Database();
     * $conn = $database->getConnection();
     * if ($conn) {
     *     // Use $conn for database operations
     * }
     */
    public function getConnection()
    {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->databaseName,
                $this->username,
                $this->password
            );
            
            // Set UTF-8 character encoding
            $this->conn->exec("set names utf8");
            
        } catch (PDOException $exception) {
            echo "Database could not be connected: " . $exception->getMessage();
        }
        
        return $this->conn;
    }
}
