<?php
require_once $_SERVER['DOCUMENT_ROOT'] ."/classes/Auth.php";
require_once $_SERVER['DOCUMENT_ROOT'] ."/classes/User.php";
require_once $_SERVER['DOCUMENT_ROOT'] ."/classes/Teacher.php";
require_once $_SERVER['DOCUMENT_ROOT'] ."/classes/Course.php";
require_once $_SERVER['DOCUMENT_ROOT'] ."/classes/Category.php";
require_once $_SERVER['DOCUMENT_ROOT'] ."/functions/helper.php";

Auth::init();
$isAuth = Auth::isTeacher();
session_start();
if (!$isAuth) {
    redirect("./index.php");
}

$user = $_SESSION["user"];
$user = $user->getUser();
$teacher_id = $user["id"];

// Get course ID from URL
$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get course data
$pdo = Database::getInstance()->getConnection();
$stmt = $pdo->prepare("SELECT * FROM course WHERE id = :id AND teacher_id = :teacher_id");
$stmt->bindParam(':id', $course_id);
$stmt->bindParam(':teacher_id', $teacher_id);
$stmt->execute();
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    redirect("./courses.php");
}

$categories = Category::getAllCategories();

// CSRF Protection
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
$errors = [];

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="/public/images/youdemy_logo.png" style="width: 100px;height:60px" alt="Logo" class="logo-img">
        </div>
        <ul class="nav-links">
            <li><a href="./index.php">Add Course</a></li>
            <li><a href="./courses.php" class="active">Courses</a></li>
            <li><a href="./analytics.php">Analytics</a></li>
        </ul>
        <div class="profile-section">
            <img src="<?= "/public/uploads/users/" . $user["picture_path"] ?>" alt="Profile" class="profile-img">
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

    <main class="dashboard-content">
        <h1>Edit Course</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach ($errors as $error): ?>
                    <p class="error"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form class="create-course-form" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label for="course-title">Course Title</label>
                <input type="text" id="course-title" name="course-title" value="<?= htmlspecialchars($course['title']) ?>" required>
            </div>

            <div class="form-group">
                <label for="course-description">Course Description</label>
                <textarea id="course-description" name="course-description" rows="4" required><?= htmlspecialchars($course['description']) ?></textarea>
            </div>

            <div class="form-group">
                <label for="course-category">Course Category</label>
                <select id="course-category" name="course-category" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $category['id'] == $course['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Current Course Type: <?= ucfirst($course['type']) ?></label>
            </div>

            <div class="form-group">
                <?php if ($course['type'] === 'text'): ?>
                    <label for="course-text">Course Content</label>
                    <textarea id="course-text" name="course-text" rows="4" required><?= htmlspecialchars($course['content']) ?></textarea>
                <?php else: ?>
                    <label for="course-<?= $course['type'] ?>">Update <?= ucfirst($course['type']) ?> File (Optional)</label>
                    <input type="file" id="course-<?= $course['type'] ?>" name="course-<?= $course['type'] ?>" 
                           accept="<?= $course['type'] === 'pdf' ? '.pdf' : 'video/*' ?>">
                    <?php if ($course['link']): ?>
                        <p>Current file: <?= htmlspecialchars($course['link']) ?></p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="course-image">Course Image (Optional)</label>
                <input type="file" id="course-image" name="course-image" accept="image/*">
                <?php if ($course['image']): ?>
                    <img src="<?= "/public/uploads/courses/" . $course['image'] ?>" 
                         alt="Current course image" class="preview-image">
                <?php endif; ?>
            </div>

            <button type="submit" class="submit-btn">Update Course</button>
        </form>
    </main>

    <script>
        document.getElementById('logout_btn').addEventListener('click', function(event) {
            event.preventDefault();
            fetch('./inc/logout.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
            .then(response => {
                if (response.ok) {
                    window.location.href = './login.php';
                } else {
                    console.error('Logout failed');
                }
            })
            .catch(error => console.error('Error:', error));
        });
    </script>
</body>
</html>