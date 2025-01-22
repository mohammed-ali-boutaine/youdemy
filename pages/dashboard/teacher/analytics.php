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

// Get basic statistics
function getAnalytics($teacherId) {
    $pdo = Database::getInstance()->getConnection();
    
    // Total courses count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM course WHERE teacher_id = :teacher_id");
    $stmt->bindParam(':teacher_id', $teacherId);
    $stmt->execute();
    $totalCourses = $stmt->fetchColumn();
    
    // Total students count (unique students across all courses)
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT e.student_id) 
        FROM enroll e 
        JOIN course c ON e.course_id = c.id 
        WHERE c.teacher_id = :teacher_id
    ");
    $stmt->bindParam(':teacher_id', $teacherId);
    $stmt->execute();
    $totalStudents = $stmt->fetchColumn();
    
    // Courses by type
    $stmt = $pdo->prepare("
        SELECT type, COUNT(*) as count 
        FROM course 
        WHERE teacher_id = :teacher_id 
        GROUP BY type
    ");
    $stmt->bindParam(':teacher_id', $teacherId);
    $stmt->execute();
    $coursesByType = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Most popular courses (by enroll)
    $stmt = $pdo->prepare("
        SELECT c.id, c.title, COUNT(e.student_id) as enroll_count 
        FROM course c 
        LEFT JOIN enroll e ON c.id = e.course_id 
        WHERE c.teacher_id = :teacher_id 
        GROUP BY c.id 
        ORDER BY enroll_count DESC 
        LIMIT 5
    ");
    $stmt->bindParam(':teacher_id', $teacherId);
    $stmt->execute();
    $popularCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Monthly enroll trends (last 6 months)
    $stmt = $pdo->prepare("
        SELECT DATE_FORMAT(e.enroll_at, '%Y-%m') as month, 
               COUNT(*) as enroll_count 
        FROM enroll e 
        JOIN course c ON e.course_id = c.id 
        WHERE c.teacher_id = :teacher_id 
        AND e.enroll_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) 
        GROUP BY month 
        ORDER BY month ASC
    ");
    $stmt->bindParam(':teacher_id', $teacherId);
    $stmt->execute();
    $enrollTrends = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return [
        'totalCourses' => $totalCourses,
        'totalStudents' => $totalStudents,
        'coursesByType' => $coursesByType,
        'popularCourses' => $popularCourses,
        'enrollTrends' => $enrollTrends
    ];
}

$analytics = getAnalytics($teacherId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Analytics</title>
    <link rel="stylesheet" href="/public/css/teacher.css">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
</head>
<body class="bg-gray-100">

<nav class="navbar">
        <div class="logo">
            <img src="/public/images/youdemy_logo.png" style="width: 100px;height:60px" alt="Logo" class="logo-img">

        </div>
        <ul class="nav-links">
            <li><a href="./index.php">Add Course</a></li>
            <li><a href="./courses.php">Courses</a></li>
            <li><a href="./analytics.php"  class="active">Analytics</a></li>
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
        <h1 class="text-3xl font-bold mb-8">Course Analytics Dashboard</h1>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium mb-2">Total Courses</h3>
                <div class="text-3xl font-bold text-blue-600"><?= $analytics['totalCourses'] ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium mb-2">Total Students</h3>
                <div class="text-3xl font-bold text-green-600"><?= $analytics['totalStudents'] ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium mb-2">Average Students per Course</h3>
                <div class="text-3xl font-bold text-purple-600">
                    <?= $analytics['totalCourses'] ? round($analytics['totalStudents'] / $analytics['totalCourses'], 1) : 0 ?>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-gray-500 text-sm font-medium mb-2">Course Types</h3>
                <div class="text-3xl font-bold text-orange-600"><?= count($analytics['coursesByType']) ?></div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Enrollment Trends Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Monthly Enrollment Trends</h3>
                <canvas id="enrollTrendsChart"></canvas>
            </div>
            
            <!-- Course Types Chart -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold mb-4">Courses by Type</h3>
                <canvas id="courseTypesChart"></canvas>
            </div>
        </div>

        <!-- Popular Courses Table -->
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Most Popular Courses</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrollments</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($analytics['popularCourses'] as $course): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($course['title']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= $course['enroll_count'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
                        window.location.href = './login.php'; // Redirect to login page after logout
                    } else {
                        console.error('Logout failed');
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    </script>
    <script>
        // Enrollment Trends Chart
        const enrollTrendsCtx = document.getElementById('enrollTrendsChart').getContext('2d');
        new Chart(enrollTrendsCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($analytics['enrollTrends'], 'month')) ?>,
                datasets: [{
                    label: 'Enrollments',
                    data: <?= json_encode(array_column($analytics['enrollTrends'], 'enroll_count')) ?>,
                    borderColor: 'rgb(59, 130, 246)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Course Types Chart
        const courseTypesCtx = document.getElementById('courseTypesChart').getContext('2d');
        new Chart(courseTypesCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode(array_column($analytics['coursesByType'], 'type')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($analytics['coursesByType'], 'count')) ?>,
                    backgroundColor: [
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(139, 92, 246)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>