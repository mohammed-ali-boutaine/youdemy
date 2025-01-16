<?php 
// require_once __DIR__ . "trait/trait_register";

class Teacher extends User {

     protected $role;
     protected $is_active;

    public function __construct($id, $username, $email,$password, $picture_path) {
        parent::__construct($id, $username, $email,$password, $picture_path);
        $this->role = "teacher";
        $this->is_active = 0;
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

    public function updateProfile(){

    }
    static public function register($username, $email, $password, $picture_path)
    {
       // if user exists show message
        $response = self::findByEmail($email);
        if ($response["ok"]) {

            $response["ok"]=false;
            $response["status"]="error";
            $response["user"] = null;
            $response["message"]="teacher email aleardy exists";
            return $response;

        }

        // hash password
        $password = password_hash($password,PASSWORD_BCRYPT);
        $user = new Teacher(null, $username, $email, $password, $picture_path);


        $user->save();
        $_SESSION["user"] = $user;

        self::generateToken($user->getId());
    }
}
?>