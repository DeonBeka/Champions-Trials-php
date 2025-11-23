<?php
require_once __DIR__ . '/../includes/init.php';
if (is_logged_in()) header('Location: dashboard.php');

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && password_verify($password, $u['password'])) {
        $_SESSION['user_id'] = $u['id'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    body {
        background: #f5f7fb;
    }
    .login-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 80vh;
    }
    .login-card {
        background: #fff;
        padding: 40px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
        width: 100%;
        max-width: 400px;
        text-align: center;
    }
    .login-card h2 {
        margin-bottom: 24px;
        font-weight: 600;
        color: #1f2937;
    }
    .login-card .errors {
        background: #ffe5e5;
        color: #a40000;
        padding: 10px 12px;
        border-radius: 6px;
        margin-bottom: 15px;
    }
    .login-card .form label {
        display: block;
        text-align: left;
        margin-bottom: 10px;
        font-weight: 500;
        color: #374151;
    }
    .login-card .form input {
        width: 100%;
        padding: 10px 12px;
        margin-top: 4px;
        border-radius: 8px;
        border: 1px solid #d1d5db;
        outline: none;
        font-size: 15px;
        transition: border-color 0.2s;
    }
    .login-card .form input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 2px rgba(59,130,246,0.2);
    }
    .login-card button {
        margin-top: 20px;
        width: 100%;
        padding: 12px;
        background: #3b82f6;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    .login-card button:hover {
        background: #2563eb;
    }
    .login-card .signup-link {
        margin-top: 16px;
        font-size: 14px;
        color: #6b7280;
    }
    .login-card .signup-link a {
        color: #3b82f6;
        text-decoration: none;
    }
    .login-card .signup-link a:hover {
        text-decoration: underline;
    }
</style>

<div class="login-wrapper">
    <div class="login-card">
        <h2>Login</h2>
        <?php if ($error): ?>
            <div class="errors"><?php echo e($error); ?></div>
        <?php endif; ?>
        <form method="post" class="form">
            <label>Email
                <input name="email" type="email" placeholder="you@example.com" required>
            </label>
            <label>Password
                <input name="password" type="password" placeholder="••••••••" required>
            </label>
            <button type="submit">Login</button>
        </form>
        <div class="signup-link">
            Don't have an account? <a href="register.php">Sign up</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

