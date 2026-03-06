<?php

require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../services/AuthService.php";


class UserController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($username, $password) {
        return AuthService::authenticate($this->pdo, $username, $password);
    }
}
?>
