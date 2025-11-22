<?php
require_once "../includes/admin_check.php";
require_once "../config/db.php";
require_once "../includes/header.php";
require_once "../includes/navbar.php";

// Delete upload if requested
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    // Delete file from uploads folder
    $file = $conn->query("SELECT filename FROM uploads WHERE id=$delete_id")->fetch_assoc()['filename'];
    if (file_exists("../uploads/".$file)) unlink("../uploads/".$file);
    $conn->query("DELETE FROM uploads WHERE id=$delete_id");
    header("Location: admin_uploads.php");
    exit;
}

$result = $conn->query("SELECT uploads.*, users.username FROM uploads LEFT JOIN users ON uploads.user_id = users.id ORDER BY uploads.created_at DESC");
?>

<div class="container mt-4">
    <h3>All Uploads</h3>

    <div class="row">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="col-md-6 mb-4">
                <div class="card p-3 shadow">
                    <p><strong>User:</strong> <?= htmlspecialchars($row['username']) ?></p>
                    <img src="../uploads/<?= htmlspecialchars($row['filename']) ?>" class="img-fluid mb-3">
                    <h5>Notes:</h5>
                    <pre><?= htmlspecialchars($row["ai_notes"]) ?></pre>
                    <h5>Quiz:</h5>
                    <pre><?= htmlspecialchars($row["ai_quiz"]) ?></pre>
                    <a href="?delete=<?= $row['id'] ?>" class="btn btn-danger btn-sm mt-2"
                       onclick="return confirm('Delete this upload?');">Delete Upload</a>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

</body>
</html>
