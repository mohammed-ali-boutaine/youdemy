<?php
require_once __DIR__ ."/Database.php";
class Auth {
    private static $pdo;

    public static function init() {
        self::$pdo = Database::getInstance()->getConnection();
    }

    public static function isAuth() {
        if (!isset($_COOKIE['auth_token'])) {
            return false;
        }

        $token = $_COOKIE['auth_token'];
        $stmt = self::$pdo->prepare(
            "SELECT ul.user_id, ul.login_time
             FROM user_login ul
             JOIN users u ON ul.user_id = u.id
             WHERE ul.token = :token"
        );

        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return false;
        }

        $loginTime = new DateTime($user['login_time']);
        $currentTime = new DateTime();
        $diff = $currentTime->diff($loginTime);

        return $diff->days <= 7;
    }

    public static function isAdmin() {
        return self::hasRole('admin');
    }

    public static function isTeacher() {
        return self::hasRole('teacher');
    }

    public static function isStudent() {
        return self::hasRole('student');
    }

    private static function hasRole($role) {
        if (!self::isAuth()) {
            return false;
        }

        $token = $_COOKIE['auth_token'];
        $stmt = self::$pdo->prepare(
            "SELECT u.role
             FROM user_login ul
             JOIN users u ON ul.user_id = u.id
             WHERE ul.token = :token"
        );

        $stmt->execute(['token' => $token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user && $user['role'] === $role;
    }
}