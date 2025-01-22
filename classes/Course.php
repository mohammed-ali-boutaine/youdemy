<?php

abstract class Course
{
     protected $id;
     protected $title;
     protected $description;
     protected $image;
     protected $category_id;
     protected $teacher_id;
     protected $type;

     public function __construct($id, $title, $description, $image, $category_id, $teacher_id, $type)
     {
          $this->id = $id;
          $this->title = $title;
          $this->description = $description;
          $this->image = $image;
          $this->category_id = $category_id;
          $this->teacher_id = $teacher_id;
          $this->type = $type;
     }


     // Getters
     public function getId()
     {
          return $this->id;
     }
     public function getTitle()
     {
          return $this->title;
     }
     public function getDescription()
     {
          return $this->description;
     }
     public function getImage()
     {
          return $this->image;
     }
     public function getCategoryId()
     {
          return $this->category_id;
     }
     public function getTeacherId()
     {
          return $this->teacher_id;
     }
     public function getType()
     {
          return $this->type;
     }

     // Setters
     public function setId($id)
     {
          $this->id = $id;
     }
     public function setTitle($title)
     {
          $this->title = $title;
     }
     public function setDescription($description)
     {
          $this->description = $description;
     }
     public function setImage($image)
     {
          $this->image = $image;
     }
     public function setCategoryId($category_id)
     {
          $this->category_id = $category_id;
     }
     public function setTeacherId($teacher_id)
     {
          $this->teacher_id = $teacher_id;
     }
     public function setType($type)
     {
          $this->type = $type;
     }



     public static function enrollStudent($courseId, $studentId)
     {
          $pdo = Database::getInstance()->getConnection();
          $stmt = $pdo->prepare("INSERT INTO enroll (course_id, student_id) VALUES (:course_id, :student_id)");
          $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
          $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);

          return $stmt->execute();
     }


     // Method to remove a student from a course
     public static function removeStudentFromCourse($courseId, $studentId)
     {
          $pdo = Database::getInstance()->getConnection();
          $stmt = $pdo->prepare("DELETE FROM enroll WHERE course_id = :course_id AND student_id = :student_id");
          $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
          $stmt->bindParam(':student_id', $studentId, PDO::PARAM_INT);

          return $stmt->execute();
     }



     // id , description , image , categorie_id,teacher_id,created_at

     // Method to get students enrolled in a course
     public static function getEnrolledStudents($courseId)
     {
          $pdo = Database::getInstance()->getConnection();
          $stmt = $pdo->prepare("
         SELECT u.* 
         FROM users u
         JOIN enroll e ON u.id = e.student_id
         WHERE e.course_id = :course_id
     ");
          $stmt->bindParam(':course_id', $courseId, PDO::PARAM_INT);
          $stmt->execute();

          return $stmt->fetchAll(PDO::FETCH_ASSOC);
     }

     public static function getCourseData($courseId, $teacherId)
     {
          $pdo = Database::getInstance()->getConnection();
          $stmt = $pdo->prepare("SELECT * FROM course WHERE id = :id AND teacher_id = :teacher_id");
          $stmt->bindParam(':id', $courseId);
          $stmt->bindParam(':teacher_id', $teacherId);
          $stmt->execute();
          return $stmt->fetch(PDO::FETCH_ASSOC);
     }

     public static function getTeacherCourses($teacherId)
     {
          $pdo = Database::getInstance()->getConnection();

          $stmt = $pdo->prepare("SELECT * FROM course WHERE teacher_id = :teacher_id");
          $stmt->bindParam(':teacher_id', $teacherId, PDO::PARAM_INT);
          $stmt->execute();

          $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

          $courses = [];
          foreach ($result as $row) {
               $courses[] = self::createCourseFromRow($row);
          }

          return $courses;
     }


     public static function searchCourses($categoryId = null, $searchText = '')
     {
          try {
               $pdo = Database::getInstance()->getConnection();

               $sql = "SELECT * FROM course";
               $conditions = [];
               $params = [];

               // Filter by category if provided
               if ($categoryId !== null) {
                    $conditions[] = "category_id = :category_id";
                    $params[':category_id'] = $categoryId;
               }

               // Add condition for search text
               if (!empty($searchText)) {
                    $conditions[] = "title LIKE :search_text";
                    $params[':search_text'] = '%' . $searchText . '%';
               }

               // Combine conditions into query
               if (!empty($conditions)) {
                    $sql .= " WHERE " . implode(" AND ", $conditions);
               }

               // Order by relevance (title match) if search text is provided
               if (!empty($searchText)) {
                    $sql .= " ORDER BY 
                       CASE 
                           WHEN title LIKE :exact_match THEN 1
                           ELSE 2
                       END, title ASC";
                    $params[':exact_match'] = $searchText . '%'; // Titles starting with the text are prioritized
               }

               $stmt = $pdo->prepare($sql);

               // Bind parameters
               foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value);
               }

               $stmt->execute();
               $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

               $courses = [];
               foreach ($result as $row) {
                    $courses[] = self::createCourseFromRow($row); // Transform DB rows into Course objects
               }

               return $courses;
          } catch (PDOException $e) {
               error_log("Error searching courses: " . $e->getMessage());
               return [];
          }
     }


     abstract public function addCourse();
     abstract public function showCourse();
     public static function createCourseFromRow($row)
     {
          switch ($row['type']) {
               case 'pdf':
                    return new CoursePdf($row['id'], $row['title'], $row['description'], $row['image'], $row['category_id'], $row['teacher_id'], $row['link']);
               case 'video':
                    return new CourseVideo($row['id'], $row['title'], $row['description'], $row['image'], $row['category_id'], $row['teacher_id'], $row['link']);
               case 'text':
                    return new courseText($row['id'], $row['title'], $row['description'], $row['image'], $row['category_id'], $row['teacher_id'], $row['content']);
               default:
                    throw new Exception("Unknown course type: " . $row['type']);
          }
     }
     public static function getAllCourses()
     {
          $pdo = Database::getInstance()->getConnection();
          $stmt = $pdo->query("SELECT * FROM course");
          $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

          $courses = [];
          foreach ($result as $row) {
               $courses[] = self::createCourseFromRow($row);
          }

          return $courses;
     }

     public static function deleteCourse($courseId)
     {
          $pdo = Database::getInstance()->getConnection();
          $stmt = $pdo->prepare("DELETE FROM course WHERE id = :id");
          $stmt->bindParam(':id', $courseId, PDO::PARAM_INT);
          return $stmt->execute();
     }
}

class courseText extends Course
{
     private $content;

     public function __construct($id, $title, $description, $image, $category_id, $teacher_id, $content, $type = 'text')
     {
          parent::__construct($id, $title, $description, $image, $category_id, $teacher_id, $type);
          $this->content = $content;
     }


     // polymorphisme :
     public function addCourse()
     {
          $pdo = Database::getInstance()->getConnection();
          $stmt = $pdo->prepare("
               INSERT INTO course (title, description, category_id, image, teacher_id, type, content) 
               VALUES (:title, :description, :category_id, :image, :teacher_id, :type, :content)
         ");
          $stmt->bindParam(':title', $this->title);
          $stmt->bindParam(':description', $this->description);
          $stmt->bindParam(':category_id', $this->category_id);
          $stmt->bindParam(':image', $this->image);
          $stmt->bindParam(':teacher_id', $this->teacher_id);
          $stmt->bindParam(':type', $this->type);
          $stmt->bindParam(':content', $this->content);
          return $stmt->execute();
     }

     public function showCourse()
     {
          echo "<h2>{$this->title}</h2>";
          echo "<p>{$this->description}</p>";
          echo "<div>{$this->content}</div>";
     }
}




class CoursePdf extends Course
{
     private $link;

     public function __construct($id, $title, $description, $image, $category_id, $teacher_id, $link)
     {
          parent::__construct($id, $title, $description, $image, $category_id, $teacher_id, 'pdf');
          $this->link = $link;
     }

     public function addCourse()
     {
          $pdo = Database::getInstance()->getConnection();
          $stmt = $pdo->prepare("INSERT INTO course (title, description, category_id, image, teacher_id, type, link) VALUES (:title, :description, :category_id, :image, :teacher_id, :type, :link)");
          $stmt->bindParam(':title', $this->title);
          $stmt->bindParam(':description', $this->description);
          $stmt->bindParam(':category_id', $this->category_id);
          $stmt->bindParam(':image', $this->image);
          $stmt->bindParam(':teacher_id', $this->teacher_id);
          $stmt->bindParam(':type', $this->type);
          $stmt->bindParam(':link', $this->link);
          return $stmt->execute();
     }

     public function showCourse()
     {
          echo "<h2>{$this->title}</h2>";
          echo "<p>{$this->description}</p>";
          echo "<a href='{$this->link}' target='_blank'>View PDF</a>";
     }
}

class CourseVideo extends Course
{
     private $link;

     public function __construct($id, $title, $description, $image, $category_id, $teacher_id, $link)
     {
          parent::__construct($id, $title, $description, $image, $category_id, $teacher_id, 'video');
          $this->link = $link;
     }

     public function addCourse()
     {
          $pdo = Database::getInstance()->getConnection();
          $stmt = $pdo->prepare("INSERT INTO course (title, description, category_id, image, teacher_id, type, link) VALUES (:title, :description, :category_id, :image, :teacher_id, :type, :link)");
          $stmt->bindParam(':title', $this->title);
          $stmt->bindParam(':description', $this->description);
          $stmt->bindParam(':category_id', $this->category_id);
          $stmt->bindParam(':image', $this->image);
          $stmt->bindParam(':teacher_id', $this->teacher_id);
          $stmt->bindParam(':type', $this->type);
          $stmt->bindParam(':link', $this->link);
          return $stmt->execute();
     }

     public function showCourse()
     {
          echo "<h2>{$this->title}</h2>";
          echo "<p>{$this->description}</p>";
          echo "<a href='{$this->link}' target='_blank'>Watch Video</a>";
     }
}
