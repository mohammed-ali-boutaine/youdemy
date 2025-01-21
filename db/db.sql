CREATE DATABASE youdemy;

USE youdemy;

-- Users Table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    picture_path VARCHAR(255) DEFAULT 'unknown',
    is_active BOOLEAN DEFAULT TRUE,
    role ENUM('admin', 'student', 'teacher') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- User Login Table
CREATE TABLE user_login (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    browser VARCHAR(100),
    ip_address VARCHAR(45),
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    logout_time TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Category Table
CREATE TABLE category (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    admin_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Course Table
CREATE TABLE course (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    category_id INT ,
    teacher_id INT NOT NULL,
    type ENUM('pdf', 'video', 'text') NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    content TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES category(id) ON DELETE SET null,
    FOREIGN KEY (teacher_id) REFERENCES users(id)
);

-- Enroll Table (Many-to-Many: Students and Courses)
CREATE TABLE enroll (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enroll_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id),
    FOREIGN KEY (course_id) REFERENCES course(id)
);

-- Tags Table
CREATE TABLE tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE
);

-- Course_Tag Table (Many-to-Many: Courses and Tags)
CREATE TABLE course_tag (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tag_id INT NOT NULL,
    course_id INT NOT NULL,
    FOREIGN KEY (tag_id) REFERENCES tags(id),
    FOREIGN KEY (course_id) REFERENCES course(id)
);




-- check teacher
DELIMITER //

CREATE TRIGGER before_insert_user
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.role = 'student' THEN
        SET NEW.is_active = TRUE;
    ELSEIF NEW.role = 'teacher' THEN
        SET NEW.is_active = FALSE;
    END IF;
END;

//

DELIMITER ;