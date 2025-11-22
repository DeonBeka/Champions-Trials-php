<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
$user = current_user($pdo);
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $interests = trim($_POST['interests'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $location = trim($_POST['location'] ?? '');

    // handle photo upload
    $photo_path = $user['photo'] ?? null;
    if (!empty($_FILES['photo']['name'])) {
        $fn = basename($_FILES['photo']['name']);
        $safe = time() . '_' . preg_replace('/[^A-Za-z0-9_.-]/','_', $fn);
        if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
        $target = UPLOAD_DIR . $safe;
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target)) {
            $photo_path = $safe;
        } else {
            $errors[] = 'Failed to upload photo.';
        }
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE users SET name=?, interests=?, skills=?, bio=?, location=?, photo=? WHERE id=?');
        $stmt->execute([$name, $interests, $skills, $bio, $location, $photo_path, $user['id']]);
        header('Location: profile.php');
        exit;
    }
}
require_once __DIR__ . '/../includes/header.php';
?>
<h2>Edit Profile</h2>
<?php if (!empty($errors)): ?>
  <div class="errors"><?php foreach($errors as $e) echo '<p>'.e($e).'</p>'; ?></div>
<?php endif; ?>
<form method="post" enctype="multipart/form-data" class="form">
  <label>Name<input name="name" type="text" value="<?php echo e($user['name'] ?? ''); ?>"></label>
  <label>Interests<input name="interests" type="text" value="<?php echo e($user['interests'] ?? ''); ?>"></label>
  <label>Skills<input name="skills" type="text" value="<?php echo e($user['skills'] ?? ''); ?>"></label>
  <label>Location<input name="location" type="text" value="<?php echo e($user['location'] ?? ''); ?>"></label>
  <label>Bio<textarea name="bio"><?php echo e($user['bio'] ?? ''); ?></textarea></label>
  <label>Photo<input name="photo" type="file"></label>
  <button type="submit">Save profile</button>
</form>

<section>
  <h3>Your public profile preview</h3>
  <div class="profile-card">
    <?php if ($user['photo']): ?><img src="../uploads/<?php echo e($user['photo']); ?>" alt="photo" class="avatar"><?php endif; ?>
    <h4><?php echo e($user['name'] ?: $user['email']); ?></h4>
    <p><strong>Type:</strong> <?php echo e($user['user_type']); ?></p>
    <p><strong>Interests:</strong> <?php echo e($user['interests']); ?></p>
    <p><strong>Skills:</strong> <?php echo e($user['skills']); ?></p>
    <p><?php echo nl2br(e($user['bio'])); ?></p>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
