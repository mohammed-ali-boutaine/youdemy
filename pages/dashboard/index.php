<?php 
// require_once "/classes/Auth.php";
require_once "../../functions/helper.php";
// $isAuth = Auth::isStudent();

// requireRole("student");

session_start();
$user = $_SESSION["user"];
echo "<pre>";
print_r($user);
echo "</pre>";

?>
<form method="POST" action="./inc/logout.php">
    <button type="submit">Logout</button>
</form>