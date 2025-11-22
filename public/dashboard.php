<?php
require_once "../includes/auth.php";
require_once "../includes/header.php";
require_once "../includes/navbar.php";
?>

<div class="container mt-4">
    <h2>Welcome, <?= htmlspecialchars($_SESSION["username"]); ?>!</h2>
    <p class="text-muted">Use the menu above to navigate.</p>

    <div class="row mt-4">
        <div class="col-md-6 mb-3">
            <a href="upload.php" class="btn btn-primary w-100 p-3">Upload Lesson Image</a>
        </div>

        <div class="col-md-6 mb-3">
            <a href="my_uploads.php" class="btn btn-secondary w-100 p-3">My Uploads</a>
        </div>

        <?php if (isAdmin()): ?>
            <div class="col-md-6 mb-3">
                <a href="admin_users.php" class="btn btn-warning w-100 p-3">Manage Users</a>
            </div>

            <div class="col-md-6 mb-3">
                <a href="admin_uploads.php" class="btn btn-danger w-100 p-3">View All Uploads</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
