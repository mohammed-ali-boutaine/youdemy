<?php
require_once __DIR__. '/User.php';
require_once __DIR__. '/Database.php';
class Admin extends User
{
    private $role;
    private $is_active;
    public function __construct($id, $username, $email, $password, $picture_path)
    {
        parent::__construct($id, $username, $email, $password, $picture_path);
        $this->role = "admin";
        $this->is_active = 1;
    }


}
