<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Course.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Auth.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/User.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Teacher.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Student.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Category.php';

Auth::init();
$isAuth = Auth::isStudent();

session_start();

if($isAuth){
     $user = $_SESSION["user"];
}else{
     redirect("/pages/login.php");
}
$user = $user->getUser();

// Check if course ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Course ID is required.");
}

$courseId = (int)$_GET['id'];
$course = Course::getById($courseId);

if (!$course) {
    die("Course not found.");
}

// Get course details
$courseTitle = $course->getTitle();
$courseDescription = $course->getDescription();
$courseImage = $course->getImage();
$courseCategory = Category::getCategoryById($course->getCategoryId());
$courseInstructor = User::getById($course->getTeacherId());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($courseTitle); ?> - Course Details</title>
    <link rel="stylesheet" href="/public/css/style.css">
    <link rel="stylesheet" href="/public/css/teacher.css">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=REM:ital,wght@0,100;0,200;0,400;0,500;0,700;1,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        .course-details {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .course-details img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .course-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .enroll-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="/public/images/youdemy_logo.png" style="width: 100px;height:60px" alt="Logo" class="logo-img">
        </div>
        <ul class="nav-links">
            <li><a href="./index.php" >Courses</a></li>
            <li><a href="./courses.php">Mes Courses</a></li>
            <!-- <li><a href="./analytics.php">Analytics</a></li> -->
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
        <div class="course-details">
            <img src="/public/uploads/courses/<?php echo htmlspecialchars($courseImage); ?>" alt="<?php echo htmlspecialchars($courseTitle); ?>">
            <h1><?php echo htmlspecialchars($courseTitle); ?></h1>
            <div class="course-meta">
                <p><strong>Category:</strong> <?php echo htmlspecialchars($courseCategory->getName()); ?></p>
                <p><strong>Instructor:</strong> <?php echo htmlspecialchars($courseInstructor->getUsername()); ?></p>
            </div>
            <p><?php echo htmlspecialchars($courseDescription); ?></p>
            <a href="enroll_course.php?id=<?php echo $courseId; ?>" class="enroll-btn">Enroll Now</a>
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