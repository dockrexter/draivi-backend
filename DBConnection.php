<?php
require 'vendor/autoload.php';
use DevCoder\DotEnv;


class DBConnection
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        $host = getenv('HOST');
        $db = getenv('DB');
        $user = getenv('DB_USER');  
        $pass = getenv('DB_PASS'); 

        $this->pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new DBConnection();
        }

        return self::$instance->pdo;
    }
}
