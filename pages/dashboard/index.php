<?php 
require_once "../../classes/Auth.php";
require_once "../../functions/helper.php";
require_once "../../classes/User.php"; 
require_once "../../classes/Teacher.php"; 
require_once "../../classes/Admin.php"; 
require_once "../../classes/Student.php"; 


Auth::init();
$isAuth = Auth::isAuth();

session_start();
// echo $isAuth? "auth": "no";

if($isAuth){
    $user = $_SESSION["user"];
    $role = $user->getRole();
    redirect("./$role.php");
}else{
    redirect("/pages/login.php");
}
echo $user->getRole();

?>
<!-- <form method="POST" action="./inc/logout.php">
    <button type="submit">Logout</button>
</form> -->