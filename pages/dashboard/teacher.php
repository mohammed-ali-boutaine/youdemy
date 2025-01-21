<?php
require_once "../../classes/Auth.php";
require_once "../../classes/User.php";
require_once "../../classes/Teacher.php";
require_once "../../classes/Course.php";
require_once "../../classes/Category.php";
require_once "../../functions/helper.php";

Auth::init();
$isAuth = Auth::isTeacher();
session_start();
if (!$isAuth) {
    redirect("./index.php");
}
$user = $_SESSION["user"];
$user = $user->getUser();
$teacher_id = $user["id"];


$categories = Category::getAllCategories();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // 32-byte random token
}

$csrf_token = $_SESSION['csrf_token'];
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {


    // csrf protection
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Invalid CSRF token.";
    }
    // input validation

    $title = sanitize_input($_POST["course-title"]);
    $description = sanitize_input($_POST["course-description"]);
    $category = sanitize_input($_POST["course-category"]);
    $type = sanitize_input($_POST["course-type"]);

    // Validate form inputs
    if (empty($title)) $errors[] = "Course title is required.";
    if (empty($description)) $errors[] = "Course description is required.";
    if (empty($category)) $errors[] = "Please select a category.";
    if (empty($type) || !in_array($type, ['pdf', 'text', 'video'])) {
        $errors[] = "Invalid course type.";
    }

   // Handle course content upload
   if (in_array($type, ['video', 'pdf'])) {
    $contentFile = $_FILES["course-{$type}"] ?? null;
    if (!$contentFile || $contentFile['error'] !== UPLOAD_ERR_OK) {
        $errors[] = ucfirst($type) . " upload is required.";
    } else {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . ($type === "video" ? "/public/uploads/videos/" : "/public/uploads/pdfs/");
        $uniqueName = uniqid("course_{$type}_", true) . '.' . pathinfo($contentFile['name'], PATHINFO_EXTENSION);

        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            $errors[] = "Failed to create the upload directory.";
        } elseif (!move_uploaded_file($contentFile["tmp_name"], $uploadDir . $uniqueName)) {
            $errors[] = ucfirst($type) . " upload failed.";
        }
    }
}

    // Profile Image Handling
    $imagePath = "";
    if (!empty($_FILES['course-image']['name'])) {
        $uploadDir = $_SERVER['DOCUMENT_ROOT'] . "/public/uploads/courses/"; // Folder to store uploaded images
        $fileType = pathinfo($_FILES["course-image"]["name"], PATHINFO_EXTENSION);
        $imageUniqueName = uniqid('course_image_', true) . '.' . $fileType;

        // Construct the full path for the file (only storing the name and directory)
        $imagePath = $uploadDir . $imageUniqueName;

        // Allow only image file types
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($fileType), $allowedTypes)) {
            $errors[] = "Invalid file type. Only JPG, PNG, and GIF are allowed.";
        } elseif (!is_dir($uploadDir) && !mkdir($uploadDir, 0777, true)) {
            $errors[] = "Failed to create the upload directory.";
        } elseif (!move_uploaded_file($_FILES["course-image"]["tmp_name"], $imagePath)) {
            $errors[] = "Failed to upload course image.";
        }
    } else {
        $errors[] = "Course image is required.";
    }


    if (empty($errors)) {
        switch ($type) {
            case "pdf":
                handlePdf($teacher_id, $_POST, $_FILES, $imageUniqueName);
                break;
            case "text":
                handleText($teacher_id, $_POST, $imageUniqueName);
                break;
            case "video":
                handleVideo($teacher_id, $_POST, $_FILES, $imageUniqueName);
                break;
            default:
                $errors[] = "Invalid course type.";
        }
    }

    // print_r($errors);
}



function handlePdf($teacher_id, $postData, $fileData, $imagePath)
{

    $pdfFile = $fileData['course-pdf'];
    if ($pdfFile['error'] === UPLOAD_ERR_OK) {
        $fileType = pathinfo($pdfFile['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid('course_', true) . '.' . $fileType;
        $targetPath =  $_SERVER['DOCUMENT_ROOT'] . "/public/uploads/pdfs/" . $uniqueName;

        move_uploaded_file($pdfFile['tmp_name'], $targetPath);
            $pdfCourse = new CoursePdf(
                null,
                $postData["course-title"],
                $postData["course-description"],
                $imagePath,
                $postData["course-category"],
                $teacher_id,
                $uniqueName 
            );
            $pdfCourse->addCourse();
        //     echo "PDF Course successfully added!";
        // } else {
        //     echo "Error uploading PDF file.";
        // }
    } else {
        echo "PDF upload error: " . $pdfFile['error'];
    }
}

function handleText($teacher_id, $postData, $imagePath)
{

    $textCourse = new CourseText(
        null,
        $postData["course-title"],
        $postData["course-description"],
        $imagePath,
        $postData["course-category"],
        $teacher_id,
        $postData["course-text"]
    );

    if ($textCourse->addCourse()) {
        echo "Text Course successfully added!";
    } else {
        echo "Error adding Text Course.";
    }
}

function handleVideo($teacher_id, $postData, $fileData, $imagePath)
{

    $videoFile = $fileData['course-video'];
    if ($videoFile['error'] === UPLOAD_ERR_OK) {
        $fileType = pathinfo($videoFile['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid('video_', true) . '.' . $fileType;
        $targetPath = $_SERVER['DOCUMENT_ROOT'] . "/public/uploads/videos/" . $uniqueName;

        // if (
            move_uploaded_file($videoFile['tmp_name'], $targetPath);
            // ) {
            $videoCourse = new CourseVideo(
                null,
                $postData["course-title"],
                $postData["course-description"],
                $imagePath,
                $postData["course-category"],
                $teacher_id,
                $uniqueName // Store just the unique name
            );
            $videoCourse->addCourse();
            // echo "Video Course successfully added!";
        // } else {
        //     echo "Error uploading video file.";
        // }
    } else {
        echo "Video upload error: " . $videoFile['error'];
    }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard</title>
    <link rel="stylesheet" href="/public/css/teacher.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <nav class="navbar">
        <div class="logo">
            <img src="/public/images/youdemy_logo.png" style="width: 100px;height:60px" alt="Logo" class="logo-img">

        </div>
        <ul class="nav-links">
            <li><a href="#" class="active">Dashboard</a></li>
            <li><a href="#">Courses</a></li>
            <li><a href="#">Students</a></li>
            <li><a href="#">Analytics</a></li>
        </ul>
        <div class="profile-section">
            <img src=<?= "/public/uploads/users/" . $user["picture_path"] ?> alt="Profile" class="profile-img">
            <div class="profile-dropdown">
                <button class="profile-dropdown-btn"><?= $user["username"] ?> <i class="fas fa-caret-down"></i></button>
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
        <h1>Create New Course</h1>
        <form class="create-course-form" method="POST" enctype="multipart/form-data">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">

            <div class="form-group">
                <label for="course-title">Course Title</label>
                <input type="text" id="course-title" name="course-title" required>
            </div>
            <div class="form-group">
                <label for="course-description">Course Description</label>
                <textarea id="course-description" name="course-description" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="course-content">Course Content</label>
                <select id="course-content" name="course-type" required>
                    <option value="">Select content type</option>
                    <option value="pdf">PDF</option>
                    <option value="video">Video</option>
                    <option value="text">Text</option>
                </select>
            </div>
            <div class="form-group" id="content-input-group">
                <!-- Dynamic content input will appear here -->
            </div>
            <div class="form-group">
                <label for="course-category">Course Category</label>
                <select id="course-category" name="course-category" required>
                    <option value="">Select a category</option>
                    <?php
                    foreach ($categories as $c) {
                        echo "<option value='" . $c['id'] . "'>" . $c['name'] . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="course-image">Course Image</label>
                <input type="file" id="course-image" name="course-image" accept="image/*" required>
            </div>
            <button type="submit" class="submit-btn">Create Course</button>
        </form>
    </main>

    <script>
        document.getElementById("course-content").addEventListener("change", function() {
            const contentInputGroup = document.getElementById("content-input-group");
            const selectedValue = this.value;

            // Clear previous input
            contentInputGroup.innerHTML = "";

            // Add appropriate input field based on selected content type
            if (selectedValue === "pdf") {
                contentInputGroup.innerHTML = `
                <label for="course-pdf">Upload PDF</label>
                <input type="file" id="course-pdf" name="course-pdf" accept=".pdf" required>
            `;
            } else if (selectedValue === "video") {
                contentInputGroup.innerHTML = `
                <label for="course-video">Upload Video</label>
                <input type="file" id="course-video" name="course-video" accept="video/*" required>
            `;
            } else if (selectedValue === "text") {
                contentInputGroup.innerHTML = `
                <label for="course-text">Enter Text Content</label>
                <textarea id="course-text" name="course-text" rows="4" required></textarea>
            `;
            }
        });
    </script>

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
    </script>
</body>

</html>