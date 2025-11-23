<?php
session_start();

// Database connection
$host = 'localhost';
$db   = 'volunteer_connect';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

/**
 * Helpers
 */

// Escape output
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// Check if logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Get current user
function current_user($pdo) {
    if (!is_logged_in()) return null;
    static $user;
    if ($user) return $user;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    return $user;
}

// Require login
function require_login() {
    if (!is_logged_in()) {
        header('Location: ../auth/login.php');
        exit;
    }
}

// Require admin
function require_admin() {
    require_login();
    $user = current_user($GLOBALS['pdo']);
    if (!$user['is_admin']) {
        die('Access denied. Admins only.');
    }
    return $user;
}

// Flash messages (optional)
function flash($key, $message = null) {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return true;
    }
    if (!isset($_SESSION['flash'][$key])) return null;
    $msg = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $msg;
}

// Redirect helper
function redirect($url) {
    header('Location: ' . $url);
    exit;
}
