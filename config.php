<?php
/**
 * Volunteer Connect - Configuration File
 * Database connection and utility functions
 */

// Start output buffering to prevent header issues
ob_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'volunteer_connect');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Configuration
define('APP_NAME', 'Volunteer Connect');
define('APP_URL', 'http://localhost/volunteer-connect');
define('UPLOAD_PATH', 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Security Configuration
define('HASH_COST', 12);
define('SESSION_LIFETIME', 86400); // 24 hours
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT', 900); // 15 minutes

// Start session with secure settings
if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    session_start();
}

// Database Connection Class
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch(PDOException $exception) {
            error_log("Database connection failed: " . $exception->getMessage());
            throw new Exception("Database connection failed. Please try again later.");
        }

        return $this->conn;
    }

    public function testConnection() {
        try {
            $conn = $this->getConnection();
            return $conn !== null;
        } catch(Exception $e) {
            return false;
        }
    }
}

// Security Functions
function sanitize_input($data) {
    if (is_array($data)) {
        return array_map('sanitize_input', $data);
    }
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token']) || empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
        return false;
    }
    
    // Token expires after 1 hour
    if (time() - $_SESSION['csrf_token_time'] > 3600) {
        unset($_SESSION['csrf_token']);
        unset($_SESSION['csrf_token_time']);
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => HASH_COST]);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function generate_password_reset_token() {
    return bin2hex(random_bytes(32));
}

function validate_password_reset_token($token, $timestamp) {
    // Token expires after 1 hour
    return (time() - $timestamp) < 3600;
}

// Utility Functions
function redirect($url, $statusCode = 302) {
    if (headers_sent()) {
        echo "<script>window.location.href='$url';</script>";
        exit();
    }
    header("Location: $url", true, $statusCode);
    exit();
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function get_user_id() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

function get_user_role() {
    return isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;
}

function get_user_name() {
    return isset($_SESSION['user_name']) ? $_SESSION['user_name'] : null;
}

function require_login() {
    if (!is_logged_in()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        redirect('login.php');
    }
}

function require_role($required_role) {
    require_login();
    if (get_user_role() !== $required_role) {
        redirect('dashboard.php');
    }
}

// File Upload Functions
function upload_file($file, $destination, $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'pdf']) {
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'message' => 'No file uploaded or file upload error'];
    }

    $file_info = pathinfo($file['name']);
    $file_extension = strtolower($file_info['extension']);
    
    if (!in_array($file_extension, $allowed_types)) {
        return ['success' => false, 'message' => 'File type not allowed'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size too large'];
    }

    $new_filename = uniqid() . '.' . $file_extension;
    $upload_path = UPLOAD_PATH . $new_filename;

    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }

    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return ['success' => true, 'filename' => $new_filename, 'path' => $upload_path];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file'];
    }
}

// Email Functions
function send_email($to, $subject, $message, $headers = []) {
    $default_headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . APP_NAME . ' <noreply@volunteerconnect.com>'
    ];
    
    $headers = array_merge($default_headers, $headers);
    $header_string = implode("\r\n", $headers);
    
    return mail($to, $subject, $message, $header_string);
}

// Date/Time Functions
function format_date($date, $format = 'F j, Y') {
    return date($format, strtotime($date));
}

function time_ago($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;

    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        return round($diff / 60) . ' minutes ago';
    } elseif ($diff < 86400) {
        return round($diff / 3600) . ' hours ago';
    } elseif ($diff < 2592000) {
        return round($diff / 86400) . ' days ago';
    } else {
        return format_date($datetime);
    }
}

// Error Handling
function custom_error_handler($errno, $errstr, $errfile, $errline) {
    error_log("Error: [$errno] $errstr in $errfile on line $errline");
    if (ini_get('display_errors')) {
        echo "<div class='alert alert-danger'>Error: $errstr</div>";
    }
}

set_error_handler('custom_error_handler');

// Initialize CSRF token
generate_csrf_token();

// Set error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

?>