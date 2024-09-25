<?php
require 'vendor/autoload.php';
use DevCoder\DotEnv;


class DBConnection {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        // Load environment variables
        $absolutePathToEnvFile = __DIR__ . '/.env';
        
        if (!file_exists($absolutePathToEnvFile)) {
            die("The .env file does not exist.");
        }

        (new DotEnv($absolutePathToEnvFile))->load();

        $host = getenv('HOST');
        $db = getenv('DB');
        $user = getenv('DB_USER');  
        $pass = getenv('DB_PASS'); 

        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Could not connect to the database: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new DBConnection();
        }
        return self::$instance->pdo;
    }
}
