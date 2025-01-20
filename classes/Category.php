<?php

require_once __DIR__ . "/Database.php";
class Category
{
    private $id;
    private $name;
    private $admin_id;

    public function __construct($id = null, $name = "general", $admin_id = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->admin_id = $admin_id;
    }



    // getter and setter 
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
    }




    // ******************************************
    //  for teacher and student
    public static function getAllCategories():array
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stm = $pdo->query("SELECT * FROM category");
            return $stm->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error fetching categories: " . $e->getMessage());
            return [];
        }
    }
    // ***************************************************
    public function save(): bool
    {
        try {
            $pdo = Database::getInstance()->getConnection();

            if ($this->id) {
                // Update existing record
                $stm = $pdo->prepare("UPDATE category SET name = :name, admin_id = :admin_id WHERE id = :id");
                $stm->bindParam(":id", $this->id, PDO::PARAM_INT);
            } else {
                // Insert new record
                $stm = $pdo->prepare("INSERT INTO category (name, admin_id) VALUES (:name, :admin_id)");
            }

            $stm->bindParam(":name", $this->name, PDO::PARAM_STR);
            $stm->bindParam(":admin_id", $this->admin_id, PDO::PARAM_INT);
            $result = $stm->execute();

            if ($result && !$this->id) {
                $this->id = $pdo->lastInsertId();
            }

            return $result;
        } catch (Exception $e) {
            error_log("Error saving category: " . $e->getMessage());
            return false;
        }
    }


    // *******************************************
    // for admin 
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance()->getConnection();

            $stm = $pdo->prepare("DELETE FROM category WHERE id = :id");

            $stm->bindParam(":id", $id, PDO::PARAM_INT);

            if ($stm->execute()) {
                if ($stm->rowCount() > 0) {
                    return true;
                } else {
                    throw new Exception("No category found with the provided ID.");
                }
            } else {
                throw new Exception("Failed to execute the delete query.");
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public static function getCategoriesForAdmin()
    {
        $pdo = Database::getInstance()->getConnection();
        $stm = $pdo->query("
            SELECT c.*, u.username as admin_name 
            FROM category c
            JOIN users u ON c.admin_id = u.id 
            
        ");
        // $stm->bindParam(":admin_id", $admin_id, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getCategoriesByAdminId($admin_id)
    {
        $pdo = Database::getInstance()->getConnection();
        $stm = $pdo->prepare("SELECT * FROM category WHERE admin_id = :admin_id");
        $stm->bindParam(":admin_id", $admin_id, PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchAll(PDO::FETCH_ASSOC);
    }
}
