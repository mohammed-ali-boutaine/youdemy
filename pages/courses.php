<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link
          rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
          integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw=="
          crossorigin="anonymous"
          referrerpolicy="no-referrer" />
    <title>Available Courses</title>
    <link rel="stylesheet" href="/public/css/style.css">
</head>
<body>
<?php
     include "./inc/nav.php";
     ?>
    <div class="container">
        <h1>Available Courses</h1>
        <div class="course-grid">
            <div class="course-card">
                <img src="https://placehold.co/300x200" alt="Introduction to React" class="course-image">
                <div class="course-content">
                    <h2>Introduction to React</h2>
                    <p>Learn the basics of React, including components, state, and props.</p>
                    <div class="course-actions">
                        <button class="btn btn-outline">View Details</button>
                        <button class="btn btn-primary">Enroll</button>
                    </div>
                </div>
            </div>
            <div class="course-card">
                <img src="https://placehold.co/300x200" alt="Advanced JavaScript Concepts" class="course-image">
                <div class="course-content">
                    <h2>Advanced JavaScript Concepts</h2>
                    <p>Dive deep into JavaScript with topics like closures, prototypes, and async programming.</p>
                    <div class="course-actions">
                        <button class="btn btn-outline">View Details</button>
                        <button class="btn btn-primary">Enroll</button>
                    </div>
                </div>
            </div>
            <div class="course-card">
                <img src="https://placehold.co/300x200" alt="CSS Mastery" class="course-image">
                <div class="course-content">
                    <h2>CSS Mastery</h2>
                    <p>Master CSS with advanced layouts, animations, and responsive design techniques.</p>
                    <div class="course-actions">
                        <button class="btn btn-outline">View Details</button>
                        <button class="btn btn-primary">Enroll</button>
                    </div>
                </div>
            </div>
            <div class="course-card">
                <img src="https://placehold.co/300x200" alt="Node.js Fundamentals" class="course-image">
                <div class="course-content">
                    <h2>Node.js Fundamentals</h2>
                    <p>Build server-side applications with Node.js and Express.</p>
                    <div class="course-actions">
                        <button class="btn btn-outline">View Details</button>
                        <button class="btn btn-primary">Enroll</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
     include "./inc/footer.php";
     ?>
</body>
</html>

