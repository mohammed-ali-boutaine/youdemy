<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Course.php';
require_once  $_SERVER['DOCUMENT_ROOT'] . '/classes/Auth.php';
require_once  $_SERVER['DOCUMENT_ROOT'] . '/classes/User.php';

require_once  $_SERVER['DOCUMENT_ROOT'] . '/classes/Teacher.php';
require_once  $_SERVER['DOCUMENT_ROOT'] . '/classes/Student.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Category.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Database.php';


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


$courses = Course::getCoursesByStudentId($studentId);



?>
<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Available Courses</title>
     <link rel="stylesheet" href="/public/css/teacher.css">

     <link rel="stylesheet" href="/public/css/style.css">
     <!-- google font -->
     <link rel="preconnect" href="https://fonts.googleapis.com" />
     <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
     <link
          href="https://fonts.googleapis.com/css2?family=REM:ital,wght@0,100;0,200;0,400;0,500;0,700;1,600&display=swap"
          rel="stylesheet" /> <!--  font awsome link -->
     <link
          rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous"
          referrerpolicy="no-referrer" />
     <style>
          .pagination {
               display: flex;
               justify-content: center;
               margin-top: 20px;
          }

          .pagination a {
               margin: 0 5px;
               padding: 5px 10px;
               text-decoration: none;
               color: blue;
               border: 1px solid #ccc;
               border-radius: 4px;
          }

          .pagination a.active {
               font-weight: bold;
               text-decoration: underline;
               color: white;
               background-color: violet;
          }

          /* Search bar styling */
          .search-bar {
               margin-bottom: 20px;
               display: flex;
               justify-content: center;
          }

          .search-bar input[type="text"] {
               width: 300px;
               padding: 10px;
               border: 1px solid #ccc;
               border-radius: 4px;
          }

          .search-bar button {
               padding: 10px 20px;
               margin-left: 10px;
               background-color: blue;
               color: white;
               border: none;
               border-radius: 4px;
               cursor: pointer;
          }

          .search-bar button:hover {
               background-color: darkblue;
          }
     </style>
</head>

<body>

     <nav class="navbar">
          <div class="logo">
               <img src="/public/images/youdemy_logo.png" style="width: 100px;height:60px" alt="Logo" class="logo-img">
          </div>
          <ul class="nav-links">
               <li><a href="./index.php">Courses</a></li>
               <li><a href="./mes_courses.php" class="active">Mes Courses</a></li>
               <!-- <li><a href="./aa.php">Analytiaacs</a></li> -->
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
          <h1>Available Courses</h1>
          <!-- Search Bar -->
         
          <div class="course-grid">

               <?php
               // Dynamically generate course cards
               if (!empty($courses)) {
                    foreach ($courses as $course) {
                         echo '<div class="course-card">';
                         echo '<img src="/public/uploads/courses/' . htmlspecialchars($course->getImage(), ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($course->getTitle(), ENT_QUOTES, 'UTF-8') . '" class="course-image">';
                         echo '<div class="course-content">';
                         echo '<h2>' . htmlspecialchars($course->getTitle(), ENT_QUOTES, 'UTF-8') . '</h2>';
                         echo '<p>' . htmlspecialchars($course->getDescription(), ENT_QUOTES, 'UTF-8') . '</p>';
                         echo '<div class="course-actions">';
                         echo '<button class="btn btn-outline" onclick="window.location.href=\'view_course.php?id=' . $course->getId() . '\'">View Details</button>';
                         echo '<button class="btn btn-primary" onclick="window.location.href=\'enroll_course.php?id=' . $course->getId() . '\'">Enroll</button>';
                         echo '</div>';
                         echo '</div>';
                         echo '</div>';
                    }
               } else {
                    echo "<p>No courses found.</p>";
               }
               ?>
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