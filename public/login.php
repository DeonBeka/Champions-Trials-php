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
<h2>Login</h2>
<?php if ($error) echo '<div class="errors">'.e($error).'</div>'; ?>
<form method="post" class="form">
  <label>Email<input name="email" type="email" required></label>
  <label>Password<input name="password" type="password" required></label>
  <button type="submit">Login</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
