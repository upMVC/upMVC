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
        $this->host = configDatabase::get('db.host');
        $this->databaseName = configDatabase::get('db.name');
        $this->username = configDatabase::get('db.user');
        $this->password = configDatabase::get('db.pass');
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
