<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Course.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Student.php';
// require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/User.php';

Auth::init();
$isAuth = Auth::isStudent();

session_start();

if ($isAuth) {
     $user = $_SESSION["user"];
} else {
     redirect("/pages/login.php");
}
$user = $user->getUser();
$studentId = $user["id"];

// Check if course ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
     die("Course ID is required.");
}

$courseId = (int)$_GET['id'];
$course = Course::getById($courseId);

if (!$course) {
     die("Course not found.");
}

// Check if already enrolled
$isEnrolled = COurse::checkEnrollment($courseId,$studentId);

if ($isEnrolled) {
     // Redirect to a page showing enrolled courses or course content
     header("Location: my_courses.php");
     exit();
}

// Handle enrollment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     $enrollmentResult = Course::enrollStudent($courseId,$studentId);

     if ($enrollmentResult) {
          // Successful enrollment
          $_SESSION['enrollment_success'] = "You have successfully enrolled in the course!";
          header("Location: my_courses.php");
          exit();
     } else {
          $enrollmentError = "Enrollment failed. Please try again.";
     }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Enroll in <?php echo htmlspecialchars($course->getTitle()); ?></title>
     <link rel="stylesheet" href="/public/css/style.css">
     <link rel="stylesheet" href="/public/css/teacher.css">
     <link rel="preconnect" href="https://fonts.googleapis.com" />
     <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
     <link href="https://fonts.googleapis.com/css2?family=REM:ital,wght@0,100;0,200;0,400;0,500;0,700;1,600&display=swap" rel="stylesheet" />
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
     <style>
          .enrollment-container {
               max-width: 600px;
               margin: 0 auto;
               padding: 20px;
               background-color: #f9f9f9;
               border-radius: 8px;
               box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
               text-align: center;
          }

          .enrollment-details {
               margin-bottom: 20px;
          }

          .confirm-btn {
               background-color: #4CAF50;
               color: white;
               border: none;
               padding: 10px 20px;
               border-radius: 5px;
               cursor: pointer;
               margin-right: 10px;
          }

          .cancel-btn {
               background-color: #f44336;
               color: white;
               border: none;
               padding: 10px 20px;
               border-radius: 5px;
               cursor: pointer;
          }

          .error-message {
               color: red;
               margin-bottom: 15px;
          }
     </style>
</head>

<body>
     <nav class="navbar">
          <div class="logo">
               <img src="/public/images/youdemy_logo.png" style="width: 100px;height:60px" alt="Logo" class="logo-img">
          </div>
          <ul class="nav-links">
               <li><a href="./index.php">Add Course</a></li>
               <li><a href="./courses.php">Courses</a></li>
               <li><a href="./analytics.php">Analytics</a></li>
          </ul>
          <div class="profile-section">
               <img src=<?= "/public/uploads/users/" . $user["picture_path"] ?> alt="Profile" class="profile-img">
               <div class="profile-dropdown">
                    <button class="profile-dropdown-btn"><?= $user["username"] ?> <i class="fas fa-caret-down"></i></button>
                    <div class="profile-dropdown-content">
                         <a id="logout_btn">
                              <i class="fas fa-sign-out-alt"></i>
                              Logout
                         </a>
                    </div>
               </div>
          </div>
     </nav>

     <div class="container">
          <div class="enrollment-container">
               <div class="enrollment-details">
                    <h2>Enroll in Course</h2>
                    <p>You are about to enroll in: <strong><?php echo htmlspecialchars($course->getTitle()); ?></strong></p>

                    <?php if (isset($enrollmentError)): ?>
                         <div class="error-message">
                              <?php echo $enrollmentError; ?>
                         </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                         <button type="submit" class="confirm-btn">Confirm Enrollment</button>
                         <a href="courses.php" class="cancel-btn">Cancel</a>
                    </form>
               </div>
          </div>
     </div>

   

<script>
        document.getElementById('logout_btn').addEventListener('click', function(event) {
            event.preventDefault(); // Prevent the default action

            fetch('../inc/logout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    }
                })
                .then(response => {
                    if (response.ok) {
                        window.location.href = '../login.php'; // Redirect to login page after logout
                    } else {
                        console.error('Logout failed');
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    </script>
</body>

</html>