<?php
require 'UsersDB.php';
class UsersDBController{
    private $connection;

    public function __construct()
    {
        $this->connection = UsersDB::getInstance()->getConnection();
    }

    public function getAllUsersInfo() {
        $sql = "SELECT email, first_name, family_name, role FROM users";
        $query = $this->connection->query($sql);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAuthenticationInfo($email){
        $sql = "SELECT email,password,role FROM users WHERE email = :email";
        $pQuery = $this->connection->prepare($sql);
        $pQuery->execute(['email' => $email]);
        return $pQuery->fetch(PDO::FETCH_ASSOC);
    }

    public function getPersonalInfo($email){
        $sql = "SELECT email, first_name, family_name, role FROM users WHERE email = :email";
        $pQuery = $this->connection->prepare($sql);
        $pQuery->execute(['email' => $email]);
        return $pQuery->fetch(PDO::FETCH_ASSOC);
    }

    public function insert($newUser) {
        $sql = "INSERT INTO users (email, first_name, family_name, password)
                VALUES (:email, :first_name, :family_name, :password)";
        $query = $this->connection->prepare($sql);
        return $query->execute($newUser);
    }

    public function delete($email) {
        $sql = "DELETE FROM users WHERE email = :email";
        $query = $this->connection->prepare($sql);
        return $query->execute(['email' => $email]);
    }
}
