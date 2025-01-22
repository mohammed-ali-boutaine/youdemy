<?php

require_once  $_SERVER['DOCUMENT_ROOT'] .'/classes/Course.php';
require_once  $_SERVER['DOCUMENT_ROOT'] .'/classes/Auth.php';
require_once  $_SERVER['DOCUMENT_ROOT'] .'/classes/User.php';
require_once  $_SERVER['DOCUMENT_ROOT'] .'/classes/Teacher.php';

Auth::init();
$isAuth = Auth::isTeacher();

session_start();


// Ensure teacher is logged in
if($isAuth){
    $user = $_SESSION["user"];
}else{
    redirect("/pages/login.php");
}
$user = $user->getUser();
$teacherId = $user["id"];

// $teacherId = $_SESSION['teacher_id'];
$isset =isset($_GET['course_id']);
$courseId =  $isset? intval($_GET['course_id']) : null;

// Get teacher's courses for the dropdown
$teacherCourses = Course::getTeacherCourses($teacherId);

// Get enrolled students
$enrolledStudents = Course::getEnrolledStudents($teacherId, $courseId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrolled Students</title>
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
        <h1 class="text-3xl font-bold mb-6">Enrolled Students</h1>
        
        <!-- Course Filter -->
        <form class="mb-6">
            <select name="course_id" onchange="this.form.submit()" class="p-2 border rounded">
                <option value="">All Courses</option>
                <?php foreach ($teacherCourses as $course): ?>
                    <option value="<?= htmlspecialchars($course->getId()) ?>" 
                            <?= ($courseId == $course->getId()) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($course->getTitle()) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Students Table -->
        <?php if (empty($enrolledStudents)): ?>
            <p class="text-gray-600">No students enrolled yet.</p>
        <?php else: ?>
            <div class="bg-white shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($enrolledStudents as $student): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($student['id']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($student['name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($student['email']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="student_details.php?id=<?= $student['id'] ?>" 
                                       class="text-indigo-600 hover:text-indigo-900">View Details</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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