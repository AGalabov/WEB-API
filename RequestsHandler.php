<?php
require 'RequestController.php';

class RequestsHandler
{
    private $requests;
    private $request_controller;

    public function __construct() {
        $this->requests = [];
        $this->request_controller = new RequestController();
        $this->setRequests();
    }

    private function setRequests() {
        $this->addNewRequest('GET', 'users', function($data){
            $this->request_controller->getAllUsers($data);
        });
        $this->addNewRequest('POST', 'register', function($newUser){
            $this->request_controller->register($newUser);
        });
        $this->addNewRequest('DELETE', 'user', function($email){
            $this->request_controller->deleteUser($email);
        });
        $this->addNewRequest('POST', 'login', function($email){
            $this->request_controller->login($email);
        });
        $this->addNewRequest('GET', 'logout', function($email){
            $this->request_controller->logout($email);
        });
    }

    private function addNewRequest($method, $cmd, $callback) {
        array_push($this->requests, [
            'method' => $method,
            'cmd' => $cmd,
            'callback' => $callback
        ]);
    }
    
    public function run(){ //check if DB class is synchronized
        $method = $_SERVER['REQUEST_METHOD'];
        $cmd = $_SERVER['PATH_INFO'];
        $payload = json_decode(file_get_contents('php://input'),true);

        session_start();

        $cmd_args = explode('/',$cmd);
        if(count($cmd_args) > 2) {
            $payload = $cmd_args[2];
        } 
        
        foreach($this->requests as $request){
            if($request['method'] === $method && $request['cmd'] === $cmd_args[1]){
                $request['callback']($payload);
                break;
            }
        }
    }
}