<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
$user = current_user($pdo);
if ($user['user_type'] !== 'seeker') {
    die('Only seekers may add places.');
}
$errors = [];
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
        $safe = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/','_', $fn);
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
        $target = UPLOAD_DIR . $safe;
        if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            $img = $safe;
        } else {
            $errors[] = 'Image upload failed.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO places (seeker_id,title,description,requirements,location,tags,image) VALUES (?,?,?,?,?,?,?)');
        $stmt->execute([$user['id'],$title,$description,$requirements,$location,$tags,$img]);
        header('Location: dashboard.php');
        exit;
    }
}
require_once __DIR__ . '/../includes/header.php';
?>
<h2>Add place</h2>
<?php if (!empty($errors)): ?><div class="errors"><?php foreach($errors as $e) echo '<p>'.e($e).'</p>'; ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form">
  <label>Title<input name="title" required></label>
  <label>Description<textarea name="description"></textarea></label>
  <label>Requirements<textarea name="requirements"></textarea></label>
  <label>Location<input name="location"></label>
  <label>Tags (comma separated)<input name="tags"></label>
  <label>Image<input name="image" type="file"></label>
  <button type="submit">Add place</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
