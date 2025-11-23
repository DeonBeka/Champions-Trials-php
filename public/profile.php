<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
$user = current_user($pdo);
$errors = [];

// Make sure these avatars exist in public/uploads/avatars/
$avatars = ['avatar1.png', 'avatar2.png', 'avatar3.png', 'avatar4.png'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $interests = trim($_POST['interests'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $photo_path = $_POST['photo'] ?? $user['photo'] ?? null;

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE users SET name=?, interests=?, skills=?, bio=?, location=?, photo=? WHERE id=?');
        $stmt->execute([$name, $interests, $skills, $bio, $location, $photo_path, $user['id']]);
        header('Location: profile.php');
        exit;
    }
}
require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-wrapper">
  <div class="form-container">
    <h2>Edit Profile</h2>

    <?php if (!empty($errors)): ?>
      <div class="errors">
        <?php foreach($errors as $e) echo '<p>'.e($e).'</p>'; ?>
      </div>
    <?php endif; ?>

    <form method="post" class="form">
      <label>Name
        <input name="name" type="text" value="<?php echo e($user['name'] ?? ''); ?>">
      </label>

      <label>Interests
        <input name="interests" type="text" value="<?php echo e($user['interests'] ?? ''); ?>">
      </label>

      <label>Skills
        <input name="skills" type="text" value="<?php echo e($user['skills'] ?? ''); ?>">
      </label>

      <label>Location
        <input name="location" type="text" value="<?php echo e($user['location'] ?? ''); ?>">
      </label>

      <label>Bio
        <textarea name="bio"><?php echo e($user['bio'] ?? ''); ?></textarea>
      </label>

      <label>Choose Avatar</label>
      <div style="display:flex;gap:12px;margin-bottom:20px;">
        <?php foreach ($avatars as $avatar): ?>
          <label style="cursor:pointer; text-align:center; display:block;">
            <input type="radio" name="photo" value="<?php echo $avatar; ?>" 
              <?php echo ($user['photo'] === $avatar) ? 'checked' : ''; ?> 
              style="display:none;">
            <img src="uploads/avatars/<?php echo $avatar; ?>" alt="" 
              style="width:70px;height:70px;border-radius:50%;border:3px solid <?php echo ($user['photo'] === $avatar) ? '#2a7de1' : '#ccc'; ?>;">
          </label>
        <?php endforeach; ?>
      </div>

      <button type="submit">Save Profile</button>
    </form>
  </div>
</div>

<section style="margin:40px auto; max-width:500px; text-align:center;">
  <h3>Your Public Profile Preview</h3>
  <div class="profile-card" style="background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08);">
    <img src="uploads/avatars/<?php echo e($user['photo'] ?? 'avatar1.png'); ?>" alt="photo" class="avatar" style="width:100px; height:100px; border-radius:50%; margin-bottom:12px;">
    <h4><?php echo e($user['name'] ?: $user['email']); ?></h4>
    <p><strong>Type:</strong> <?php echo e($user['user_type']); ?></p>
    <p><strong>Interests:</strong> <?php echo e($user['interests']); ?></p>
    <p><strong>Skills:</strong> <?php echo e($user['skills']); ?></p>
    <p><?php echo nl2br(e($user['bio'])); ?></p>
  </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
