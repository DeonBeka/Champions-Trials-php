<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$user = current_user($pdo);
if (!$user['is_admin']) die('Access denied. Admins only.');

$id = (int)($_GET['id'] ?? 0);
if (!$id) die('User not found.');

// Fetch user
$stmt = $pdo->prepare('SELECT * FROM users WHERE id=?');
$stmt->execute([$id]);
$editUser = $stmt->fetch();
if (!$editUser) die('User not found.');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $user_type = $_POST['user_type'] ?? 'volunteer';
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    if (!$name || !$email) $errors[] = 'Name and Email are required.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE users SET name=?, email=?, user_type=?, is_admin=? WHERE id=?');
        $stmt->execute([$name, $email, $user_type, $is_admin, $id]);
        header('Location: admin_dashboard.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="vc-container" style="max-width:500px; padding-top:40px;">
    <h2>Edit User</h2>
    <?php foreach($errors as $e) echo '<div class="errors">'.e($e).'</div>'; ?>
    <form method="post" class="form">
        <label>Name<input type="text" name="name" value="<?php echo e($editUser['name']); ?>" required></label>
        <label>Email<input type="email" name="email" value="<?php echo e($editUser['email']); ?>" required></label>
        <label>User Type
            <select name="user_type">
                <option value="volunteer" <?php echo $editUser['user_type']=='volunteer'?'selected':''; ?>>Volunteer</option>
                <option value="seeker" <?php echo $editUser['user_type']=='seeker'?'selected':''; ?>>Seeker</option>
            </select>
        </label>
        <label><input type="checkbox" name="is_admin" <?php echo $editUser['is_admin']?'checked':''; ?>> Admin</label>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="admin_dashboard.php" class="btn btn-ghost">Cancel</a>
    </form>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
