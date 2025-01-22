<?php


require_once  $_SERVER['DOCUMENT_ROOT'] .'/classes/Course.php';
require_once  $_SERVER['DOCUMENT_ROOT'] .'/classes/Auth.php';
require_once  $_SERVER['DOCUMENT_ROOT'] .'/classes/User.php';
require_once  $_SERVER['DOCUMENT_ROOT'] .'/classes/Teacher.php';


Auth::init();
$isAuth = Auth::isTeacher();

session_start();

if($isAuth){
     $user = $_SESSION["user"];
 }else{
     redirect("/pages/login.php");
 }
 $user = $user->getUser();
 $teacherId = $user["id"];

//  echo "<pre>";
//  print_r($user);
//  echo "</pre>";
//  $teacherId = $user["id"];
// session_start();

// // Ensure teacher is logged in
// if (!isset($_SESSION['teacher_id'])) {
//     header('Location: login.php');
//     exit;
// }

// $teacherId = $_SESSION['teacher_id'];
$courses = Course::getTeacherCourses($teacherId);

// Handle course deletion
if (isset($_POST['delete_course']) && isset($_POST['course_id'])) {
    $courseId = $_POST['course_id'];
    if (Course::deleteCourse($courseId)) {
        header('Location: ' . $_SERVER['PHP_SELF'] . '?message=Course+deleted+successfully');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses</title>
    <link rel="stylesheet" href="/public/css/teacher.css">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<nav class="navbar">
        <div class="logo">
            <img src="/public/images/youdemy_logo.png" style="width: 100px;height:60px" alt="Logo" class="logo-img">

        </div>
        <ul class="nav-links">
            <li><a href="./index.php">Add Course</a></li>
            <li><a href="./courses.php"  class="active">Courses</a></li>
            <li><a href="./analytics.php">Analytics</a></li>
        </ul>
        <div class="profile-section">
            <img src=<?= "/public/uploads/users/" . $user["picture_path"] ?> alt="Profile" class="profile-img">
            <div class="profile-dropdown">
                <button class="profile-dropdown-btn"><?= $user["username"] ?> <i class="fas fa-caret-down"></i></button>
                <div class="profile-dropdown-content">
                    <!-- <a href="#"><i class="fas fa-user"></i> Profile</a>
                    <a href="#"><i class="fas fa-cog"></i> Settings</a> -->
                    <a id="logout_btn">
                        <i class="fas fa-sign-out-alt"></i>
                        Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold">My Courses</h1>
            <a href="./index.php" 
               class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Add New Course
            </a>
        </div>

        <?php if (isset($_GET['message'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($_GET['message']) ?>
            </div>
        <?php endif; ?>

        <?php if (empty($courses)): ?>
            <p class="text-gray-600">No courses available.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($courses as $course): ?>
                    <div class="bg-white rounded-lg shadow-md overflow-hidden">
                        <?php if ($course->getImage()): ?>
                            <img src="/public/uploads/courses/<?= htmlspecialchars($course->getImage()) ?>" 
                                 alt="Course thumbnail" 
                                 class="w-full h-48 object-cover">
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <h2 class="text-xl font-semibold mb-2"><?= htmlspecialchars($course->getTitle()) ?></h2>
                            <p class="text-gray-600 mb-4"><?= htmlspecialchars($course->getDescription()) ?></p>
                            
                            <div class="flex justify-between items-center">
                                <span class="inline-block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700">
                                    <?= ucfirst(htmlspecialchars($course->getType())) ?>
                                </span>
                                
                                <div class="space-x-2">
                                    <a href="edit_course.php?id=<?= $course->getId() ?>" 
                                       class="text-blue-500 hover:text-blue-700">Edit</a>
                                    
                                    <a href="show_students.php?course_id=<?= $course->getId() ?>" 
                                       class="text-green-500 hover:text-green-700">Students</a>
                                    
                                    <form action="" method="POST" class="inline">
                                        <input type="hidden" name="course_id" value="<?= $course->getId() ?>">
                                        <button type="submit" 
                                                name="delete_course" 
                                                class="text-red-500 hover:text-red-700"
                                                onclick="return confirm('Are you sure you want to delete this course?')">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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
                        window.location.href = './login.php'; // Redirect to login page after logout
                    } else {
                        console.error('Logout failed');
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    </script>
</body>
</html>