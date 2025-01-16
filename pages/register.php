<?php
require_once "../functions/helper.php";
require_once "../classes/Student.php";
require_once "../classes/Teacher.php";
require_once "../classes/User.php";
session_start();

// Handle login logic here (optional)
$error = ''; // Placeholder for error messages, if needed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

     // input validation
     $username = sanitize_input($_POST["username"]);
     $email = sanitize_input($_POST["email"]);
     $password = sanitize_input($_POST["password"]);
     $role = sanitize_input($_POST["role"]);

     $isValid = true;
     $error = "";

     // Validate form inputs
     if (empty($email) || empty($password) || empty($username)) {
          $error = "All fields are required.";
          $isValid = false;

          // email validation
     } elseif (!validate_email($email)) {
          $error = "Invalid email format.";
          $isValid = false;
     }
     if (empty($role) || ($role !== "student" && $role !== "teacher")) {
          $error = "Invalide role.";
          $isValid = false;
     }


     // Profile Image Handling
     if (!empty($_FILES['profile_image']['name'])) {
          $uploadDir = "/public/uploads/users/"; // Folder to store uploaded images
          $fileType = pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION);
          $uniqueName = uniqid('user_', true) . '.' . $fileType;

          // Construct the full path for the file (only storing the name and directory)
          $fileName = $uploadDir . $uniqueName;

          // Allow only image file types
          $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
          if (!in_array(strtolower($fileType), $allowedTypes)) {
               $error = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
               $isValid = false;
          } elseif (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
               $error = "Failed to create the upload directory.";
               $isValid = false;
          } elseif (!move_uploaded_file($_FILES["profile_image"]["tmp_name"], $fileName)) {
               $error = "Failed to upload profile image.";
               $isValid = false;
          }
     } else {
          $error = "Profile image is required.";
          $isValid = false;
     }


     if ($isValid) {

          $response;
          if ($role == "teacher") {
               $response = Teacher::register($username, $email, $password, $uniqueName);
          } else if ($role == "student") {
               $response = Student::register($username, $email, $password, $uniqueName);
          }

          if ($response) {
               redirect("/pages/dashboard/index.php");
              
          } else {
               $error = "Registration failed. Please try again.";
          }
     }

}

?>


<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href="/public/css/style.css">
     <link
          rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous"
          referrerpolicy="no-referrer" />
     <title>Regsiter</title>
</head>

<body>

     <?php

     include "./inc/nav.php";
     ?>
     <main class="form">
          <div class="container" id="form-container">
               <h2 id="form-title">Register</h2>
               <form id="form" method="POST" enctype="multipart/form-data">

                    <div id="errorMessages" class="error">
                         <?php if (!empty($error)): ?>
                              <?= htmlspecialchars($error) ?>
                         <?php endif; ?>
                    </div>


                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter username" required>

                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter email" required>

                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>

                    <label for="profile_image">Profile Image</label>
                    <input type="file" id="profile_image" name="profile_image" accept="image/*">

                    <label>Role</label>
                    <div class="role">
                         <label><input type="radio" name="role" value="student" required checked> Student</label>
                         <label><input type="radio" name="role" value="teacher" required> Teacher</label>
                    </div>

                    <button type="submit" name="action" value="register">Register</button>

                    <div class="toggle">
                         Already have an account? <a href="/pages/login.php">Login</a>
                    </div>
               </form>
          </div>
     </main>

     <?php

     include "./inc/footer.php";

     ?>

     <script>
          document.getElementById('registerForm').addEventListener('submit', function(event) {
               const errorMessages = [];
               const username = document.getElementById('username').value.trim();
               const email = document.getElementById('email').value.trim();
               const password = document.getElementById('password').value.trim();
               const profileImage = document.getElementById('profile_image').files[0];

               // Username validation
               if (username.length < 3 || username.length > 15) {
                    errorMessages.push('Username must be between 3 and 15 characters.');
               }

               // Email validation
               const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
               if (!emailRegex.test(email)) {
                    errorMessages.push('Enter a valid email address.');
               }

               // Password validation
               if (password.length < 6) {
                    errorMessages.push('Password must be at least 6 characters long.');
               }

               // Profile image validation
               if (profileImage && !['image/jpeg', 'image/png', 'image/gif'].includes(profileImage.type)) {
                    errorMessages.push('Profile image must be a JPEG, PNG, or GIF.');
               }

               if (errorMessages.length > 0) {
                    event.preventDefault();
                    document.getElementById('errorMessages').innerHTML = errorMessages.join('<br>');
               }
          });
     </script>