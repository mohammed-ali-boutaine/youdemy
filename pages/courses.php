<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Course.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Category.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/Database.php';


// Get search parameters
$searchText = isset($_GET['search']) ? trim($_GET['search']) : '';
$categoryId = isset($_GET['category']) ? (int)$_GET['category'] : null;

if($searchText || $categoryId){
    $courses = Course::searchCourses($categoryId, $searchText);
    $total = count($courses);

}else{

// Fetch all courses
$courses = Course::getAllCourses();
$total = count($courses);
}

$categories = Category::getAllCategories();

// Pagination parameters
$limit = 9; // Number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max($page, 1); // Ensure page number is at least 1
$offset = ($page - 1) * $limit; // Calculate offset

// Slice the array for the current page
$currentPageCourses = array_slice($courses, $offset, $limit);
$total_pages = ceil($total / $limit);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Courses</title>
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
        background-color: #007bff;
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

    <?php include "./inc/nav.php"; ?>
    <div class="container">
        <h1>Available Courses</h1>
            <!-- Search Bar -->
    <div class="search-bar">
    <form method="GET" action="">
            <select name="category">
                <option value="">All Categories</option>
                <?php
                   foreach ($categories as $category) {
                    $selected = ($category['id'] == $categoryId) ? 'selected' : '';
                    echo "<option value='{$category['id']}' $selected>" . htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') . "</option>";
                }
                ?>
            </select>
            <input type="text" name="search" placeholder="Search courses..." value="<?php echo htmlspecialchars($searchText, ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit">Search</button>
        </form>
    </div>
        <div class="course-grid">

            <?php
            // Dynamically generate course cards
            if (!empty($currentPageCourses)) {
                foreach ($currentPageCourses as $course) {
                    echo '<div class="course-card">';
                    echo '<img src="/public/uploads/courses/' . htmlspecialchars($course->getImage(), ENT_QUOTES, 'UTF-8') . '" alt="' . htmlspecialchars($course->getTitle(), ENT_QUOTES, 'UTF-8') . '" class="course-image">';
                    echo '<div class="course-content">';
                    echo '<h2>' . htmlspecialchars($course->getTitle(), ENT_QUOTES, 'UTF-8') . '</h2>';
                    echo '<p>' . htmlspecialchars($course->getDescription(), ENT_QUOTES, 'UTF-8') . '</p>';
                    echo '<div class="course-actions">';
                    echo '<button class="btn btn-outline" onclick="redirectToLogin()">View Details</button>';
                    echo '<button class="btn btn-primary" onclick="redirectToLogin()">Enroll</button>';
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
   
    <!-- Pagination links -->
    <div class="pagination">
        <?php
        for ($i = 1; $i <= $total_pages; $i++) {
            $class = ($i == $page) ? "class='active'" : "";
            echo "<a href='?page=$i' $class>$i</a> ";
        }
        ?>
    </div>


    <?php
     include "./inc/footer.php";
     ?>
    <script>
    // JavaScript function to handle redirection
    function redirectToLogin() {
        window.location.href = './login.php';
    }
</script>
</body>

</html>