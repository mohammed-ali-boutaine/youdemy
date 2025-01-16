<?php 
session_start();
$user = $_SESSION["user"];
echo "<pre>";
print_r($user);
echo "</pre>";