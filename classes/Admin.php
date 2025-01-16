<?php
require_once './User.php';
require_once './connection.php';
class Admin extends User
{
    public function __construct($id = null, $nom, $email, $password, $role = "admin")
    {
        parent::__construct($id, $nom, $email, $password, $role);
    }
}
