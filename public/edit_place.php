<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$user = current_user($pdo);

// Only admins can access
if (!$user['is_admin'] || $user['is_admin'] != 1) {
    die('Access denied. Admins only.');
}

$place_id = (int)($_GET['id'] ?? 0);
if (!$place_id) {
    die('Place not found.');
}

// Fetch place
$stmt = $pdo->prepare('SELECT * FROM places WHERE id = ?');
$stmt->execute([$place_id]);
$place = $stmt->fetch();
if (!$place) die('Place not found.');

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $location = trim($_POST['location'] ?? '');
    
    // Handle image upload
    if (!empty($_FILES['image']['name'])) {
        $target_dir = __DIR__ . '/../uploads/';
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $target_file = $target_dir . $image_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Simple validation
        $allowed = ['jpg','jpeg','png','gif'];
        if (!in_array($imageFileType, $allowed)) {
            $errors[] = "Only JPG, PNG, GIF files are allowed for the image.";
        } else {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                // Delete old image
                if ($place['image'] && file_exists($target_dir . $place['image'])) {
                    unlink($target_dir . $place['image']);
                }
                $place['image'] = $image_name;
            } else {
                $errors[] = "Failed to upload the image.";
            }
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE places SET title=?, description=?, requirements=?, tags=?, location=?, image=? WHERE id=?');
        $stmt->execute([$title, $description, $requirements, $tags, $location, $place['image'], $place_id]);
        $success = 'Place updated successfully!';
        // Refresh place info
        $stmt = $pdo->prepare('SELECT * FROM places WHERE id = ?');
        $stmt->execute([$place_id]);
        $place = $stmt->fetch();
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="vc-container" style="padding-top:40px; max-width:700px;">
    <h2>Edit Place</h2>
    <?php if ($success) echo '<div class="btn btn-cta" style="margin-bottom:15px;">' . e($success) . '</div>'; ?>
    <?php if ($errors) foreach($errors as $e) echo '<div class="errors"><p>' . e($e) . '</p></div>'; ?>

    <form method="post" enctype="multipart/form-data" class="form">
        <label>Title
            <input type="text" name="title" required value="<?php echo e($place['title']); ?>">
        </label>

        <label>Description
            <textarea name="description" required><?php echo e($place['description']); ?></textarea>
        </label>

        <label>Requirements
            <textarea name="requirements"><?php echo e($place['requirements']); ?></textarea>
        </label>

        <label>Tags (comma separated)
            <input type="text" name="tags" value="<?php echo e($place['tags']); ?>">
        </label>

        <label>Location
            <input type="text" name="location" value="<?php echo e($place['location']); ?>">
        </label>

        <label>Image
            <input type="file" name="image">
        </label>
        <?php if ($place['image'] && file_exists(__DIR__ . '/../uploads/' . $place['image'])): ?>
            <img src="../uploads/<?php echo e($place['image']); ?>" alt="" style="width:200px;height:auto;margin:10px 0;border-radius:8px;">
        <?php endif; ?>

        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="admin_dashboard.php" class="btn btn-ghost">Cancel</a>
    </form>
</div>

<style>
    .form label { display:block; margin-bottom:12px; }
    .form input[type=text], .form textarea { width:100%; padding:8px; border-radius:6px; border:1px solid #ccc; }
    .form textarea { min-height:80px; }
    .btn { padding:8px 14px; border-radius:6px; border:none; cursor:pointer; text-decoration:none; display:inline-block; }
    .btn-primary { background:#1565D8; color:#fff; }
    .btn-ghost { background:#f5f7fb; color:#333; border:1px solid #ccc; }
    .btn-cta { background:#4caf50; color:#fff; padding:10px 18px; border-radius:6px; margin-bottom:12px; }
    .errors { background:#ffe6e6; color:#a40000; padding:8px 12px; border-radius:6px; margin-bottom:8px; }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

