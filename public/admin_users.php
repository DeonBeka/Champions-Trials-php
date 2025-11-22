<?php
require_once "../includes/admin_check.php";
require_once "../config/db.php";
require_once "../includes/header.php";
require_once "../includes/navbar.php";

// Delete user if requested
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $conn->query("DELETE FROM users WHERE id=$delete_id");
    header("Location: admin_users.php");
    exit;
}

$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<div class="container mt-4">
    <h3>All Users</h3>

    <table class="table table-striped mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($user = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= $user['role'] ?></td>
                    <td><?= $user['created_at'] ?></td>
                    <td>
                        <a href="?delete=<?= $user['id'] ?>" class="btn btn-danger btn-sm"
                           onclick="return confirm('Delete this user?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
