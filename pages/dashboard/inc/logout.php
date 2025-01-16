<?php
require_once "../../../classes/User.php";
include "../../../functions/helper.php";

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = User::logout();
    // Optionally log the response or handle it if needed
    // print_r($response);
    
    // Redirect to login page after logging out
    redirect("/pages/login.php");
} else {
    // Optionally, you can redirect if it's not a POST request
    redirect("/pages/login.php");
}
