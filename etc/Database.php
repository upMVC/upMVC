<?php


namespace upMVC;

use PDO;
use PDOException;

class Database
{
    private $host;
    private $databaseName;
    private $username;
    private $password;
    public $conn;

    public function __construct()
    {
        $this->loadConfig();
    }

    private function loadConfig()
    {
        $this->host = ConfigDatabase::get('db.host');
        $this->databaseName = ConfigDatabase::get('db.name');
        $this->username = ConfigDatabase::get('db.user');
        $this->password = ConfigDatabase::get('db.pass');
    }

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->databaseName, $this->username, $this->password);
            $this->conn->exec("set names utf8");
        } catch (PDOException $exception) {
            echo "Database could not be connected: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
