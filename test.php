<?php

require_once "./classes/Student.php";
require_once "./classes/Teacher.php";
require_once "./classes/User.php";


// $test = Student::register("ali","test@gmail.com","test","test.png");
// $test = Teacher::register("teacher1","teacher@gmail.com","teacher","teacher.png");

// $test = User::login("test@gmail.com","test");
// $test = User::login("teacher@gmail.com","teacher");

// Assuming $pdo is your PDO connection
// The user input values (replace these with actual user input)
$username = 'admin';
$email = 'admin@gmail.com';
$password = 'admin';  // This will be hashed
$picture_path = 'ali.png';
$role = 'admin';  // Set role as admin
$is_active = 1;   // Assuming 1 means active

$pdo= Database::getInstance()->getConnection();
// Hash the password using password_hash
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Prepare the SQL query with placeholders
$stmt = $pdo->prepare("
    INSERT INTO users (username, email, password, picture_path, role, is_active) 
    VALUES (:username, :email, :password, :picture_path, :role, :is_active)
");

// Bind the values to the placeholders
$stmt->bindParam(':username', $username);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password', $hashed_password);
$stmt->bindParam(':picture_path', $picture_path);
$stmt->bindParam(':role', $role);
$stmt->bindParam(':is_active', $is_active);

// Execute the statement
if ($stmt->execute()) {
    echo "User added successfully!";
} else {
    echo "Error adding user.";
}


// echo "<pre>";
// print_r($test);
// echo "</pre>";
