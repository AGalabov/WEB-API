<?php
require 'UsersDBController.php';

class RequestController{
    private $users_db;

    public function __construct() {
        $this->users_db = new UsersDBController();
    }

    private function errorResponse($errorMsg,$errCode){
        http_response_code($errCode);
        echo json_encode(['error' => $errorMsg],JSON_UNESCAPED_UNICODE);
    }

    public function getAllUsers($data) {
        if(isset($_SESSION['email'])) {
            if($_SESSION['role'] === 'Admin') {
                $users = $this->users_db->getAllUsersInfo();
                echo json_encode($users,JSON_UNESCAPED_UNICODE);
            } else {
                $this->errorResponse('Нямате правомощията за тази операция!',401);
            }
        } else {
            $this->errorResponse('Не сте влезнали в системата!',403);
        }
    }

    private function getInputError($newUser) {
        if(strlen($newUser['email']) > 255) {
            return 'Прекалено дълъг email!';
        }
        if(strlen($newUser['first_name']) > 100) {
            return 'Прекалено дългo първо име!';
        }
        if(strlen($newUser['family_name']) > 100) {
            return 'Прекалено дългo фамилно име!';
        }
        return false;
    }

    public function register($newUser) {
        $error = $this->getInputError($newUser); 
        if(!$error) {
            if(!$this->users_db->getAuthenticationInfo($newUser['email'])) {
                $newUser['password'] = password_hash($newUser['password'], PASSWORD_DEFAULT);
                $users = $this->users_db->insert($newUser);
                $user_info = $this->users_db->getPersonalInfo($newUser['email']);
                echo json_encode($user_info,JSON_UNESCAPED_UNICODE);   //returns information of user -- maybe use get
            } else {
                $this->errorResponse('Този email адрес вече е използван!',401);
            }
        } else {
            $this->errorResponse($error,400);
        }
    }

    public function deleteUser($email) {
        if(isset($_SESSION['email'])) {
            if($_SESSION['role'] === 'Admin') {
                $user_info = $this->users_db->getPersonalInfo($email);
                if($user_info) {
                    $this->users_db->delete($email);
                    echo json_encode($user_info,JSON_UNESCAPED_UNICODE); 
                } else {
                    $this->errorResponse('Не съществува потребител с такъв имейл!',400);
                }
            } else {
                $this->errorResponse('Нямате правомощията за тази операция!',401);
            }
        } else {
            $this->errorResponse('Не сте влезнали в системата!',403);
        }
    }

    public function login($userData) {
        $content = $this->users_db->getAuthenticationInfo($userData['email']);
        if(!isset($_SESSION['email'])) {
            if($content && password_verify($userData['password'],$content['password'])) {
                $_SESSION['email'] = $content['email'];
                $_SESSION['role'] = $content['role'];
                setcookie($content['email'],$content['role'],time() + 86400,'/'); //timeout = 86400(1 day)
                $user_info = $this->users_db->getPersonalInfo($userData['email']);
                echo json_encode($user_info,JSON_UNESCAPED_UNICODE); 
            } else {
                $this->errorResponse('Няма потребител с такива имейл и парола!',400);
            }
        } else {
            $this->errorResponse('Вече сте влезнали в системата!',403);
        }
    }
    
    public function logout() {
        if(isset($_SESSION['email'])) {
            setcookie($_SESSION['email'],$_SESSION['role'],time()-1,'/');
            session_unset();
            session_destroy();
        } else {
            $this->errorResponse('Не сте влезнали в системата!',403);
        }
    }
}