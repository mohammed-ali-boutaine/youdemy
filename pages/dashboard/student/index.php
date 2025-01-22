<?php 


require_once $_SERVER['DOCUMENT_ROOT'] ."/classes/Auth.php";
require_once $_SERVER['DOCUMENT_ROOT'] ."/classes/User.php";
require_once $_SERVER['DOCUMENT_ROOT'] ."/classes/Student.php";
require_once $_SERVER['DOCUMENT_ROOT'] ."/classes/Course.php";
require_once $_SERVER['DOCUMENT_ROOT'] ."/classes/Category.php";
require_once $_SERVER['DOCUMENT_ROOT'] ."/functions/helper.php";

Auth::init();
$isAuth = Auth::isStudent();
session_start();
if (!$isAuth) {
    redirect("../../login.php");
}
$user = $_SESSION["user"];
$user = $user->getUser();
$studentId = $user["id"];

// $studentId = $_SESSION['user_id'];
$pdo = Database::getInstance()->getConnection();

// Get student information
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :student_id");
$stmt->execute([':student_id' => $studentId]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

// Get enrolled courses with progress
$stmt = $pdo->prepare("
    SELECT 
        c.*,
        e.enroll_at,
        u.username as teacher_name,
        cat.name as category_name
    FROM course c
    JOIN enroll e ON c.id = e.course_id
    JOIN users u ON c.teacher_id = u.id
    JOIN category cat ON c.category_id = cat.id
    WHERE e.student_id = :student_id
    ORDER BY e.enroll_at DESC
");
$stmt->execute([':student_id' => $studentId]);
$enrolledCourses = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get recent activities
// $stmt = $pdo->prepare("
//     SELECT 
//         'lesson_completion' as activity_type,
//         l.title as lesson_title,
//         c.description as course_name,
//         cl.completion_date as activity_date
//     FROM completed_lessons cl
//     JOIN lessons l ON cl.lesson_id = l.id
//     JOIN courses c ON l.course_id = c.id
//     WHERE cl.student_id = :student_id
//     ORDER BY cl.completion_date DESC
//     LIMIT 5
// ");
// $stmt->execute([':student_id' => $studentId]);
// $recentActivities = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
