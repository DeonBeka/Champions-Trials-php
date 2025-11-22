<?php
require_once "../includes/auth.php";
require_once "../config/db.php";
require_once "../includes/header.php";
require_once "../includes/navbar.php";

// Enable errors
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$user_id = $_SESSION["user_id"];

// Fetch uploads for this user
$stmt = $conn->prepare("SELECT * FROM uploads WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="container mt-4">
    <h3>My Uploads</h3>

    <?php if ($result->num_rows === 0): ?>
        <div class="alert alert-info">You have no uploads yet.</div>
    <?php else: ?>
        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-6 mb-4">
                    <div class="card p-3 shadow">
                        <?php
                        $filepath = "../uploads/" . $row["filename"];
                        if (file_exists($filepath)):
                        ?>
                            <img src="<?= $filepath ?>" class="img-fluid mb-3">
                        <?php else: ?>
                            <div class="alert alert-warning">Image file missing.</div>
                        <?php endif; ?>

                        <h5>Notes:</h5>
                        <pre><?= htmlspecialchars($row["ai_notes"]) ?></pre>

                        <h5>Quiz:</h5>
                        <pre><?= htmlspecialchars($row["ai_quiz"]) ?></pre>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
