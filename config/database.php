<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'serveruser');
define('DB_PASS', 'password');
define('DB_NAME', 'pet_rescue');

define('MAPS_API_KEY', 'AIzaSyB7jHJijFFPuaiTlq29s26V9e5CGXuvk-4');

class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
                DB_USER,
                DB_PASS,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );
        } catch(PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if(!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
}
?>