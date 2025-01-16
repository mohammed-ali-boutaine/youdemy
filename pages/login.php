<?php 
require_once "../functions/helper.php";
require_once "../classes/User.php";
require_once "../classes/Teacher.php";
require_once "../classes/Student.php";
require_once "../classes/Admin.php";
session_start();


$error = ''; // Placeholder for error messages, if needed

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize_input($_POST['email']);
    $password = sanitize_input($_POST['password']);

    // Input validation
    if (empty($email) || empty($password)) {
        $error = "Both email and password are required.";
    } elseif (!validate_email($email)) {
        $error = "Invalid email format.";
    }

    $user = User::login($email, $password); // Assuming User::authenticate handles password verification
    if($user["ok"]){
        redirect("/pages/dashboard/index.php");
    }else{
        $error = $user["message"];
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
      referrerpolicy="no-referrer"
    />
     <title>Login</title>
</head>
<body>

<?php  
     include "./inc/nav.php";
     ?>
     <main class="form">
          <div class="container" id="form-container">
               <h2 id="form-title">Login</h2>
               <form id="loginForm" action="login.php" method="POST">

               <div id="errorMessages" class="error">
               <?php if (!empty($error)): ?>
                              <?= htmlspecialchars($error) ?>
                    <?php endif; ?>
               </div>

                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" placeholder="Enter email" required>

                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter password" required>

                    <button type="submit">Login</button>

                    <div class="toggle">
                         Don't have an account? <a href="/pages/register.php">Register</a>
                    </div>
               </form>
          </div>
     </main>

<?php 
include "./inc/footer.php";
?>

<script>
        // Optional: Add client-side validation for login if needed
        document.getElementById('loginForm').addEventListener('submit', function(event) {
            const errorMessages = [];
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value.trim();

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                errorMessages.push('Enter a valid email address.');
            }

            // Password validation
            // if (password.length < 6) {
            //     errorMessages.push('Password must be at least 6 characters long.');
            // }

            if (errorMessages.length > 0) {
                event.preventDefault();
                document.getElementById('errorMessages').innerHTML = errorMessages.join('<br>');
            }
        });
     </script>
</body>
</html>