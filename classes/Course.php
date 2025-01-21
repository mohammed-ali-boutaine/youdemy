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

     abstract public function addCourse();
     abstract public function showCourse();
     public static function createCourseFromRow($row)
     {
          switch ($row['type']) {
               case 'pdf':
                    return new CoursePdf($row['id'], $row['titre'], $row['description'], $row['image'], $row['category_id'], $row['teacher_id'], $row['link']);
               case 'video':
                    return new CourseVideo($row['id'], $row['titre'], $row['description'], $row['image'], $row['category_id'], $row['teacher_id'], $row['link']);
               case 'texte':
                    return new courseText($row['id'], $row['titre'], $row['description'], $row['image'], $row['category_id'], $row['teacher_id'], $row['content']);
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
