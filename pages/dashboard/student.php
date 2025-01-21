<?php
require_once "../../classes/Auth.php";
require_once "../../classes/User.php";
require_once "../../classes/Student.php";
require_once "../../functions/helper.php";

Auth::init();
$isAuth = Auth::isStudent();
session_start();
if (!$isAuth) {
     redirect("./index.php");
}

$user = $_SESSION["user"];
$user = $user->getUser();
?>

<!DOCTYPE html>
<html lang="en">

<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Student Dashboard</title>
     <link rel="stylesheet" href="/public/css/student.css">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
     <nav class="navbar">
          <div class="logo">
               <!-- <img src="https://via.placeholder.com/50" alt="Logo" class="logo-img"> -->
               <img src="/public/images/youdemy_logo.png" style="width: 100px;height:60px" alt="Logo" class="logo-img">
          </div>
          <ul class="nav-links">
               <li><a href="#" class="active">Dashboard</a></li>
               <li><a href="#" id="my-courses-link">My Courses</a></li>
               <li><a href="#" id="available-courses-link">Available Courses</a></li>
          </ul>
          <div class="profile-section">
               <img src=<?= "/public/uploads/users/".$user["picture_path"] ?> alt="Profile" class="profile-img">
               <div class="profile-dropdown">
                    <button class="profile-dropdown-btn"><?=$user["username"] ?>  <i class="fas fa-caret-down"></i></button>
                    <div class="profile-dropdown-content">
                         <a href="#"><i class="fas fa-user"></i> Profile</a>
                         <a href="#"><i class="fas fa-cog"></i> Settings</a>
                         <a id="logout_btn">
                              <i class="fas fa-sign-out-alt"></i>
                              Logout
                         </a>
                    </div>
               </div>
          </div>
     </nav>

     <main class="dashboard-content">
          <section id="my-courses" class="course-section">
               <h1>My Courses</h1>
               <div class="course-grid">
                    <div class="course-card">
                         <img src="https://via.placeholder.com/300x200" alt="Course 1" class="course-image">
                         <div class="course-content">
                              <h2>Introduction to Python</h2>
                              <p>Learn the basics of Python programming language.</p>
                              <button class="btn btn-primary">Continue Course</button>
                         </div>
                    </div>
                    <div class="course-card">
                         <img src="https://via.placeholder.com/300x200" alt="Course 2" class="course-image">
                         <div class="course-content">
                              <h2>Web Development Fundamentals</h2>
                              <p>Master HTML, CSS, and JavaScript for web development.</p>
                              <button class="btn btn-primary">Continue Course</button>
                         </div>
                    </div>
               </div>
          </section>

          <section id="available-courses" class="course-section" style="display: none;">
               <h1>Available Courses</h1>
               <div class="course-grid">
                    <div class="course-card">
                         <img src="https://via.placeholder.com/300x200" alt="Course 3" class="course-image">
                         <div class="course-content">
                              <h2>Data Science Essentials</h2>
                              <p>Explore the world of data science and analytics.</p>
                              <button class="btn btn-secondary">Enroll Now</button>
                         </div>
                    </div>
                    <div class="course-card">
                         <img src="https://via.placeholder.com/300x200" alt="Course 4" class="course-image">
                         <div class="course-content">
                              <h2>Mobile App Development</h2>
                              <p>Learn to create mobile apps for iOS and Android.</p>
                              <button class="btn btn-secondary">Enroll Now</button>
                         </div>
                    </div>
                    <div class="course-card">
                         <img src="https://via.placeholder.com/300x200" alt="Course 5" class="course-image">
                         <div class="course-content">
                              <h2>Machine Learning Fundamentals</h2>
                              <p>Dive into the basics of machine learning algorithms.</p>
                              <button class="btn btn-secondary">Enroll Now</button>
                         </div>
                    </div>
                    <div class="course-card">
                         <img src="https://via.placeholder.com/300x200" alt="Course 6" class="course-image">
                         <div class="course-content">
                              <h2>Graphic Design Masterclass</h2>
                              <p>Master the principles of graphic design and visual communication.</p>
                              <button class="btn btn-secondary">Enroll Now</button>
                         </div>
                    </div>
               </div>
               <div class="pagination">
                    <button class="btn btn-outline"><i class="fas fa-chevron-left"></i> Previous</button>
                    <span class="page-info">Page 1 of 3</span>
                    <button class="btn btn-outline">Next <i class="fas fa-chevron-right"></i></button>
               </div>
          </section>

          <section id="course-detail" class="course-section" style="display: none;">
               <h1>Course Detail</h1>
               <div class="course-detail-content">
                    <img src="https://via.placeholder.com/800x400" alt="Course Detail" class="course-detail-image">
                    <h2>Data Science Essentials</h2>
                    <p class="course-description">
                         Explore the world of data science and analytics in this comprehensive course.
                         You'll learn about data collection, cleaning, analysis, and visualization techniques.
                         The course covers popular tools and libraries used in the field, including Python,
                         pandas, and matplotlib. By the end of this course, you'll have the skills to start
                         your journey as a data scientist.
                    </p>
                    <h3>Course Outline</h3>
                    <ul class="course-outline">
                         <li>Introduction to Data Science</li>
                         <li>Data Collection and Cleaning</li>
                         <li>Exploratory Data Analysis</li>
                         <li>Data Visualization Techniques</li>
                         <li>Introduction to Machine Learning</li>
                         <li>Final Project: Real-world Data Analysis</li>
                    </ul>
                    <button class="btn btn-primary">Enroll in This Course</button>
               </div>
          </section>
     </main>

     <script>
          document.getElementById('logout_btn').addEventListener('click', function(event) {
               event.preventDefault(); // Prevent the default action

               fetch('./inc/logout.php', {
                         method: 'POST',
                         headers: {
                              'Content-Type': 'application/x-www-form-urlencoded'
                         }
                    })
                    .then(response => {
                         if (response.ok) {
                              window.location.href = './login.php'; // Redirect to login page after logout
                         } else {
                              console.error('Logout failed');
                         }
                    })
                    .catch(error => console.error('Error:', error));
          });
          document.getElementById('my-courses-link').addEventListener('click', function(e) {
               e.preventDefault();
               document.getElementById('my-courses').style.display = 'block';
               document.getElementById('available-courses').style.display = 'none';
               document.getElementById('course-detail').style.display = 'none';
          });

          document.getElementById('available-courses-link').addEventListener('click', function(e) {
               e.preventDefault();
               document.getElementById('my-courses').style.display = 'none';
               document.getElementById('available-courses').style.display = 'block';
               document.getElementById('course-detail').style.display = 'none';
          });

          document.querySelectorAll('.btn-secondary').forEach(function(btn) {
               btn.addEventListener('click', function() {
                    document.getElementById('my-courses').style.display = 'none';
                    document.getElementById('available-courses').style.display = 'none';
                    document.getElementById('course-detail').style.display = 'block';
               });
          });
     </script>
</body>

</html>