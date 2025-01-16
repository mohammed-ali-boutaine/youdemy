<?php
require_once __DIR__ . "/Database.php";

abstract class User
{

    protected $id;
    protected $username;
    protected $email;
    protected $password;
    protected $picture_path;

    // Constructor to initialize the properties
    function __construct($id, $username, $email, $password, $picture_path)
    {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->password = $password;
        $this->picture_path = $picture_path;
    }


    public function getId()
    {
        return $this->id;
    }
    static function generateToken(int $userId): bool
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            // Generate and store a token
            $token = bin2hex(random_bytes(16));
            $ip_address = htmlspecialchars($_SERVER['REMOTE_ADDR'], ENT_QUOTES, 'UTF-8');
            // $browser = htmlspecialchars($_SERVER['HTTP_USER_AGENT'], ENT_QUOTES, 'UTF-8');

            // insert token and info
            $stmt = $pdo->prepare(
                "INSERT INTO user_login (user_id, ip_address, browser, token) 
                VALUES (:user_id, :ip_address, :browser, :token)"
            );
            $stmt->execute([
                'user_id' => $userId,
                'ip_address' => $ip_address,
                'browser' => null,
                'token' => $token,
            ]);
            // echo $token;
            // Set cookies
            setcookie("auth_token", $token, time() + (86400 * 7), "/", "");

            return true;
        } catch (PDOException $e) {
            error_log("Error generating token: " . $e->getMessage());
            echo $e->getMessage();
            return false;
        }
    }
    // Get user by ID
    public static function getById(int $id): ?User
    {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            switch ($result['role']) {
                case 'student':
                    return new Student(
                        $result['id'],
                        $result['username'],
                        $result['email'],
                        $result['password'],
                        $result['picture_path']
                    );
                case 'teacher':
                    return new Teacher(
                        $result['id'],
                        $result['username'],
                        $result['email'],
                        $result['password'],
                        $result['picture_path']
                    );
                case 'admin':
                    return new Admin(
                        $result['id'],
                        $result['username'],
                        $result['email'],
                        $result['password'],
                        $result['picture_path']
                    );
                default:
                    return null;
            }
        }

        return null; // User not found
    }
    static function isAuth($token){

    }
    static function isStudent(){

    }
    static function isAdmin($token){

    }
    static function findByEmail(string $email): array
    {
        try {
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);

            if ($stmt->rowCount() === 0) {
                return ['status' => 'error', 'message' => 'user Not Found', "ok" => false];
            }
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['status' => 'succes', 'user' => $user, "ok" => true];
        } catch (PDOException $e) {
            return ['status' => 'error', 'message' => 'Error fetching user: ' . $e->getMessage(), 'ok' => false];
        }
    }
    static function login(string $email, string $password)
    {

        // if user exists show message
        $response = self::findByEmail($email);
        if (!$response["ok"]) {
            return $response;
        }

        // check pass ect
        $user = $response["user"];
        // var_dump($user);

        password_hash($password, PASSWORD_BCRYPT);


        $passwordIsValid = password_verify($password, $user['password']);

        if ($passwordIsValid) {

            switch ($user["role"]) {
                case "student":
                    // Create a student instance
                    $u = new Student($user["id"], $user["username"], $user["email"], $user["password"], $user["picture_path"]);
                    break;

                case "teacher":
                    // Create a teacher instance (Assuming a Teacher class exists)
                    $u = new Teacher($user["id"], $user["username"], $user["email"], $user["password"], $user["picture_path"]);
                    break;

                case "admin":
                    // Create an admin instance (Assuming an Admin class exists)
                    $u = new Admin($user["id"], $user["username"], $user["email"], $user["password"], $user["picture_path"]);
                    break;

                default:
                    return [
                        'status' => 'error',
                        'message' => 'Role not recognized',
                        'ok' => false
                    ];
            }
            $_SESSION["user"] = $u;
            // var_dump($_SESSION["user"]);
            self::generateToken($user["id"]);
            // echo "seccuess";
            return [
                'status' => 'success',
                'message' => 'Logged in succeflly',
                'ok' => true
            ];
        } else {
            // Invalid password
            return [
                'status' => 'error',
                'message' => 'Invalid password',
                'ok' => false
            ];
        }
    }
    public static function logout() : array
    {
        try {
            // Start the session if not already started
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            // Check if cookies exist
            if (isset($_COOKIE['auth_token'])) {

                $pdo = Database::getInstance()->getConnection();
                $auth_token = $_COOKIE['auth_token'];
                // Prepare the statement to update the is_active status to 0
                $stmt = $pdo->prepare(
                    "UPDATE user_login 
                     SET is_active = 0, logout_time = NOW() 
                     WHERE token = :token"
                );

                // Execute the query
                $stmt->execute(['token' => $auth_token]);

                // Clear the session
                $_SESSION = [];
                session_unset();
                session_destroy();

                // Clear the cookies
                setcookie("auth_token", "", time() - 3600, "/", "", true, true);

                return ['status' => 'success', 'message' => 'Logout successfully', 'ok' => true];
            } else {
                return ['status' => 'success', 'message' => 'Error, No token found', 'ok' => false];
            }
        } catch (PDOException $e) {
            return ['status' => 'error', 'message' => 'Failed to logout: ' . $e->getMessage(), 'ok' => false];
        }
    }
}
