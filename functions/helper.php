<?php

require_once __DIR__ . "/../classes/Auth.php";

// Helper function to sanitize input
function sanitize_input($data)
{
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
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
function tagHandler($content)
{

    // content example
    // $content = "hello this is a post about #coding and #programming";
    $tags = [];
    $words = explode(" ", $content);
    for ($i = 0; $i < sizeof($words); $i++) {
        $word = $words[$i];

        if ($words[$i][0] === "#" && strlen($word) > 1) {

            $tag = substr(strtolower($words[$i]), 1);

            if (!in_array($tag, $tags)) {
                $tags[] = $tag;
            }
            $words[$i] = "<a href='page#$tag'>" . $words[$i] . "</a>";
        }
    }

    $content = implode(" ", $words);

    $result = ["tags" => $tags, "content" => $content];

    return $result;
}


function requireRole($role="student")
{
    Auth::init(); // Initialize the database connection

    if (!Auth::isAuth()) {
        redirect("/pages/login.php");
    }

    $hasRole = false;
    switch ($role) {
        case 'admin':
            $hasRole = Auth::isAdmin();
            break;
        case 'teacher':
            $hasRole = Auth::isTeacher();
            break;
        case 'student':
            $hasRole = Auth::isStudent();
            break;
    }

    if (!$hasRole) {
        redirect("/pages/login.php");
    }
}
