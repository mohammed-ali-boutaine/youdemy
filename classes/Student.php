<?php
require_once __DIR__ . "/User.php";

class Student extends User
{

    // use register;

    private $enrolledCoures = [];

    protected $role; 
        private $is_active;

    public function __construct($id, $username, $email, $password, $picture_path)
    {
        parent::__construct($id, $username, $email, $password, $picture_path);
        $this->role = "student";
        $this->is_active = 1;
    }


    // getter

    public function getId(){
        return $this->id;
    }
    private function save()
    {

        // get connection
        $pdo = Database::getInstance()->getConnection();

        try {
            if ($this->id) {
                // Update user

                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET username = :username, email = :email, password = :password, picture_path = :picture_path 
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':username' => $this->username,
                    ':email' => $this->email,
                    ':password' => $this->password,
                    ':picture_path' => $this->picture_path,
                    ':id' => $this->id,
                ]);
            } else {
                // insert
               // Insert new user
               $stmt = $pdo->prepare("
               INSERT INTO users (username, email, password, picture_path, role, is_active) 
               VALUES (:username, :email, :password, :picture_path, :role, :is_active)
           ");
           $stmt->execute([
               ':username' => $this->username,
               ':email' => $this->email,
               ':password' => $this->password,
               ':picture_path' => $this->picture_path,
               ':role' => $this->role,
               ':is_active' => $this->is_active,
           ]);
           $this->id = $pdo->lastInsertId();
            }
        } catch (PDOException $e) {
            // error handling
            throw new Exception("Error saving user: " . $e->getMessage());

        }
    }

    static public function register($username, $email, $password, $picture_path)
    {
       // if user exists show message
        $response = self::findByEmail($email);
        if ($response["ok"]) {

            $response["ok"]=false;
            $response["status"]="error";
            $response["user"] = null;
            $response["message"]="student email aleardy exists";
            return $response;

        }

        // hash password
        $password = password_hash($password,PASSWORD_BCRYPT);
        $user = new Student(null, $username, $email, $password, $picture_path);


        $user->save();

        $_SESSION["user"] = $user;
        parent::generateToken($user->getId());
        return ['status' => 'succes', 'message' => "register succesfly", "ok" => true];

    }



}
