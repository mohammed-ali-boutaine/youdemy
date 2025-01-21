<?php 
require_once "../../classes/Auth.php";
require_once "../../functions/helper.php";

Auth::init();
$isAuth = Auth::isAdmin();
session_start();
if (!$isAuth) {
     redirect("./index.php");
}


?>

admin