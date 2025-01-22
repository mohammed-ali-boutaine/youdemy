<?php
require_once  $_SERVER['DOCUMENT_ROOT'] . '/classes/Course.php';
require_once  $_SERVER['DOCUMENT_ROOT'] . '/classes/Auth.php';
require_once  $_SERVER['DOCUMENT_ROOT'] . '/classes/User.php';
require_once  $_SERVER['DOCUMENT_ROOT'] . '/classes/Teacher.php';
require_once  $_SERVER['DOCUMENT_ROOT'] . '/classes/Category.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/functions/helper.php";


Auth::init();
$isAuth = Auth::isTeacher();

session_start();

if ($isAuth) {
    $user = $_SESSION["user"];
} else {
    redirect("/pages/login.php");
}
$user = $user->getUser();
$teacherId = $user["id"];

$isset = isset($_GET['id']);
$courseId =  $isset ? intval($_GET['id']) : null;


$categories = Category::getAllCategories();
$course = Course::getCourseData($courseId, $teacherId);
// echo $courseId . " , " . $teacherId  . ".";
// var_dump($course);
// if (!$course) {
//      redirect("./courses.php");
//  }


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // CSRF protection
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid CSRF token.";
    }

    // Input validation
    $title = sanitize_input($_POST["course-title"]);
    $description = sanitize_input($_POST["course-description"]);
    $category = sanitize_input($_POST["course-category"]);
    $type = $course['type']; // Keep original type

    // Validate form inputs
    if (empty($title)) $errors[] = "Course title is required.";
    if (empty($description)) $errors[] = "Course description is required.";
    if (empty($category)) $errors[] = "Please select a category.";

    // Handle course content update
    if (in_array($type, ['video', 'pdf']) && !empty($_FILES["course-{$type}"]['name'])) {
        $contentFile = $_FILES["course-{$type}"];
        if ($contentFile['error'] === UPLOAD_ERR_OK) {
            $uploadDir = $_SERVER['DOCUMENT_ROOT'] . ($type === "video" ? "/public/uploads/videos/" : "/public/uploads/pdfs/");
            $uniqueName = uniqid("course_{$type}_", true) . '.' . pathinfo($contentFile['name'], PATHINFO_EXTENSION);

            if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
                $errors[] = "Failed to create the upload directory.";
            } elseif (move_uploaded_file($contentFile["tmp_name"], $uploadDir . $uniqueName)) {
                // Delete old file if exists
                if (!empty($course['link'])) {
                    $oldFile = $uploadDir . $course['link'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
            } else {
                $errors[] = ucfirst($type) . " upload failed.";
            }
        }
    }

    // Image Handling
    $imageUniqueName = $course['image']; // Keep existing image by default
    if (!empty($_FILES['course-image']['name'])) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/public/uploads/courses/";
        $fileType = pathinfo($_FILES["course-image"]["name"], PATHINFO_EXTENSION);
        $imageUniqueName = uniqid('course_image_', true) . '.' . $fileType;
        $imagePath = $uploadDir . $imageUniqueName;

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($fileType), $allowedTypes)) {
            $errors[] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        } elseif (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            $errors[] = "Failed to create the upload directory.";
        } elseif (move_uploaded_file($_FILES["course-image"]["tmp_name"], $imagePath)) {
            // Delete old image if exists
            if (!empty($course['image'])) {
                $oldImage = $uploadDir . $course['image'];
                if (file_exists($oldImage)) {
                    unlink($oldImage);
                }
            }
        } else {
            $errors[] = "Failed to upload course image.";
        }
    }

    if (empty($errors)) {
        $pdo = Database::getInstance()->getConnection();

        // Base update query
        $sql = "UPDATE course SET 
                title = :title,
                description = :description,
                category_id = :category_id,
                image = :image";

        // Add type-specific fields
        if ($type === 'text') {
            $sql .= ", content = :content";
        } elseif (isset($uniqueName)) {
            $sql .= ", link = :link";
        }

        $sql .= " WHERE id = :id AND teacher_id = :teacher_id";

        $stmt = $pdo->prepare($sql);

        // Bind parameters
        $params = [
            ':title' => $title,
            ':description' => $description,
            ':category_id' => $category,
            ':image' => $imageUniqueName,
            ':id' => $course_id,
            ':teacher_id' => $teacher_id
        ];

        if ($type === 'text') {
            $params[':content'] = sanitize_input($_POST['course-text']);
        } elseif (isset($uniqueName)) {
            $params[':link'] = $uniqueName;
        }

        foreach ($params as $key => &$value) {
            $stmt->bindParam($key, $value);
        }

        if ($stmt->execute()) {
            redirect("./courses.php?message=Course+updated+successfully");
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course</title>
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
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold mb-6">Edit Course</h1>

            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <form action="" method="POST" enctype="multipart/form-data" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <input type="hidden" name="type" value="<?= htmlspecialchars($course['type']) ?>">

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                        Title
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="title"
                        type="text"
                        name="title"
                        value="<?= htmlspecialchars($course['title']) ?>"
                        required>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                        Description
                    </label>
                    <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                        id="description"
                        name="description"
                        rows="4"
                        required><?= htmlspecialchars($course['description']) ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="category">
                        Category
                    </label>
                    <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                        id="category"
                        name="category_id"
                        required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"
                                <?= $category['id'] == $course['category_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($category['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="image">
                        Course Image
                    </label>
                    <?php if ($course['image']): ?>
                        <img src="/public/uploads/courses/<?= htmlspecialchars($course['image']) ?>"
                            alt="Current course image"
                            class="mb-2 w-32 h-32 object-cover rounded">
                    <?php endif; ?>
                    <input type="file"
                        id="image"
                        name="image"
                        accept="image/*"
                        class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>

                <?php if ($course['type'] === 'pdf' || $course['type'] === 'video'): ?>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="link">
                            <?= ucfirst($course['type']) ?> Link
                        </label>
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            id="link"
                            type="text"
                            name="link"
                            value="<?= htmlspecialchars($course['link']) ?>"
                            required>
                    </div>
                <?php endif; ?>

                <?php if ($course['type'] === 'texte'): ?>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="content">
                            Course Content
                        </label>
                        <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                            id="content"
                            name="content"
                            rows="8"
                            required><?= htmlspecialchars($course['content']) ?></textarea>
                    </div>
                <?php endif; ?>

                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                        type="submit">
                        Update Course
                    </button>
                    <a href="show_courses.php"
                        class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                        Cancel
                    </a>
                </div>
            </form>
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
</body>

</html>