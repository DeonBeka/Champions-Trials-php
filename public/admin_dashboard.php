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
                    <a href="edit_user.php?id=<?php echo $u['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                    <a href="delete_user.php?id=<?php echo $u['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
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
                    <a href="edit_place.php?id=<?php echo $p['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
                    <a href="delete_place.php?id=<?php echo $p['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this place?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<style>
/* Table Styling */
.admin-table { 
    width:100%; 
    border-collapse:collapse; 
    margin-bottom:40px; 
    font-family: 'Inter', sans-serif;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border-radius: 8px;
    overflow: hidden;
}
.admin-table th, .admin-table td { 
    padding:12px 15px; 
    border-bottom:1px solid #eee; 
    text-align:left; 
}
.admin-table th { 
    background:#f7f7f7; 
    font-weight:600;
}
.admin-table tbody tr:hover { 
    background:#f9f9f9; 
    transition:0.2s;
}

/* Buttons */
.btn {
    display:inline-block;
    padding:6px 12px;
    border-radius:6px;
    font-size:14px;
    font-weight:500;
    text-decoration:none;
    transition:0.2s;
    cursor:pointer;
}
.btn-sm { padding:4px 10px; font-size:13px; }

.btn-primary { 
    background:#1565D8; 
    color:#fff; 
    border:none; 
}
.btn-primary:hover { background:#0D47A1; }

.btn-danger { 
    background:#D32F2F; 
    color:#fff; 
    border:none; 
}
.btn-danger:hover { background:#9A0007; }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>




