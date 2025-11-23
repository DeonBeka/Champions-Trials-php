<?php
require_once __DIR__ . '/../includes/init.php'; // adjust path if needed
require_login();

$user = current_user($pdo);

// Only allow admins
if (!$user['is_admin'] || $user['is_admin'] != 1) {
    die('Access denied. Admins only.');
}

// Fetch all users for admin management
$stmt = $pdo->query('SELECT * FROM users ORDER BY created_at DESC');
$users = $stmt->fetchAll();

// Fetch all places
$stmt = $pdo->query('SELECT p.*, u.name AS seeker_name FROM places p JOIN users u ON p.seeker_id = u.id ORDER BY p.created_at DESC');
$places = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="vc-container" style="padding-top:40px; max-width:1000px;">
    <h2>Admin Dashboard</h2>

    <h3>Users</h3>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th><th>Name</th><th>Email</th><th>Type</th><th>Admin</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($users as $u): ?>
            <tr>
                <td><?php echo e($u['id']); ?></td>
                <td><?php echo e($u['name']); ?></td>
                <td><?php echo e($u['email']); ?></td>
                <td><?php echo e($u['user_type']); ?></td>
                <td><?php echo $u['is_admin'] ? 'Yes' : 'No'; ?></td>
                <td>
                    <a href="edit_user.php?id=<?php echo $u['id']; ?>">Edit</a> |
                    <a href="delete_user.php?id=<?php echo $u['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3>Places</h3>
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th><th>Title</th><th>Seeker</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($places as $p): ?>
            <tr>
                <td><?php echo e($p['id']); ?></td>
                <td><?php echo e($p['title']); ?></td>
                <td><?php echo e($p['seeker_name']); ?></td>
                <td>
                    <a href="edit_place.php?id=<?php echo $p['id']; ?>">Edit</a> |
                    <a href="delete_place.php?id=<?php echo $p['id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
.admin-table { width:100%; border-collapse:collapse; margin-bottom:30px; }
.admin-table th, .admin-table td { padding:10px; border:1px solid #ddd; text-align:left; }
.admin-table th { background:#f2f2f2; }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>



