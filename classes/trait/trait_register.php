<?php
/*
     a trait is a mechanism for code reuse in single inheritance. Traits are used to declare methods that can be used in multiple classes, offering a way to share methods across unrelated classes without requiring inheritance.
*/


trait register
{
    static public function register($email,$password,$)
    {


        try {

            // get connection
            $pdo = Database::getInstance()->getConnection();

            // Check if user already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->rowCount() > 0) {
                return ["status" => "error", "message" => "This email already exists", "ok" => false];
            }

            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new user
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, email, password, profile_picture) 
              VALUES (:username, :email, :password, :picture_path)"
            );
            $success = $stmt->execute([
                'username' => $username,
                'email' => $email,
                'password' => $hashedPassword,
                'picture_path' => $picture_path,
            ]);

            if ($success) {
                $userId = $pdo->lastInsertId();
                $createToken = self::generateToken($userId, $this->pdo);

                if ($createToken) {
                    return ["message" => "success", "message" => "user created ", "ok" => true];
                }

                return ['status' => 'error', 'message' => 'faild to create user', "ok" => false];
            }

            return ["status" => "error", "message" => "Registration failed", "ok" => false];
        } catch (PDOException $e) {
            $message = "Failed to register user:" . $e->getMessage();
            return ['status' => 'error', 'message' => $message, "ok" => false];
        }
    }
}
