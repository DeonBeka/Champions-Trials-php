<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
$user = current_user($pdo);

// Only seekers can add places
if ($user['user_type'] !== 'seeker') {
    die('Only seekers may add places.');
}

// Define upload directory if not already defined
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', __DIR__ . '/../uploads/');
}

$errors = [];
$preview_img = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $img = null;

    if (!$title) $errors[] = 'Title required.';

    if (!empty($_FILES['image']['name'])) {
        $fn = basename($_FILES['image']['name']);
        $safe = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/', '_', $fn);
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
        $target = UPLOAD_DIR . $safe;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $img = $safe;
            $preview_img = $safe;
        } else {
            $errors[] = 'Image upload failed.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO places (seeker_id, title, description, requirements, location, tags, image) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$user['id'], $title, $description, $requirements, $location, $tags, $img]);
        header('Location: dashboard.php');
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="vc-container" style="padding-top:40px; max-width:900px;">
    <h2 style="margin-bottom:20px;">Add a Volunteer Place</h2>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <?php foreach ($errors as $e) echo '<p>'.e($e).'</p>'; ?>
        </div>
    <?php endif; ?>

    <div style="display:flex;gap:30px;flex-wrap:wrap;">
        <!-- Form column -->
        <div style="flex:1; min-width:300px;">
            <form method="post" enctype="multipart/form-data" class="form">
                <label>Title
                    <input name="title" required value="<?php echo e($_POST['title'] ?? ''); ?>">
                </label>

                <label>Description
                    <textarea name="description"><?php echo e($_POST['description'] ?? ''); ?></textarea>
                </label>

                <label>Requirements
                    <textarea name="requirements"><?php echo e($_POST['requirements'] ?? ''); ?></textarea>
                </label>

                <label>Location
                    <input name="location" value="<?php echo e($_POST['location'] ?? ''); ?>">
                </label>

                <label>Tags (comma separated)
                    <input name="tags" value="<?php echo e($_POST['tags'] ?? ''); ?>">
                </label>

                <label>Image
                    <input name="image" type="file" onchange="previewImage(event)">
                </label>

                <button type="submit" class="btn btn-primary">Add Place</button>
            </form>
        </div>

        <!-- Preview column -->
        <div style="flex:1; min-width:250px;">
            <h3>Image Preview</h3>
            <div style="border:1px solid #ddd; border-radius:12px; padding:10px; text-align:center; min-height:200px;">
                <img id="imagePreview" src="<?php echo $preview_img ? 'uploads/' . e($preview_img) : ''; ?>" 
                     alt="Preview" style="max-width:100%; max-height:200px; border-radius:10px;">
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(event) {
    const output = document.getElementById('imagePreview');
    output.src = URL.createObjectURL(event.target.files[0]);
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

