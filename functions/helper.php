<?php


// Helper function to sanitize input
function sanitize_input($data) 
{
    return htmlspecialchars(stripslashes(trim($data)));
}

// Helper function to redirect
function redirect($url)
{
    header("Location: $url");
    exit();
}
function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function check_user_authentication()
{

    // authentification login here

}

function requireAuth()
{
    if (!check_user_authentication()) {
        header("Location: login.php");
        exit();
    }
}

