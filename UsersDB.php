<?php
class UsersDB{
    private $connection = null;
    private static $instance = null;

    private function __construct()
    {
        try {
            $this->connection = new PDO('mysql:host=localhost;dbname=usersdb;charset=utf8', 'root'); 
        } catch(PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Проблем при свързването към базата'],JSON_UNESCAPED_UNICODE);
        }
    }

    public static function getInstance(){
        if(!self::$instance){
            self::$instance = new UsersDB();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}