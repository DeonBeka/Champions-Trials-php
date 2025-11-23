<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$user = current_user($pdo);
$avatars = ['avatar1.png','avatar2.png','avatar3.png','avatar4.png'];

$availability_options = [
    'Weekdays',
    'Weekends',
    'Evenings',
    'Mornings',
    'Flexible'
];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $interests = trim($_POST['interests'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $availability = $_POST['availability'] ?? $user['availability'];
    $photo = $_POST['photo'] ?? $user['photo'];

    // Optional: Validate that availability is valid
    if (!in_array($availability, $availability_options)) {
        $availability = null; // or handle error
    }

    $stmt = $pdo->prepare('UPDATE users SET name=?, interests=?, skills=?, bio=?, location=?, photo=?, availability=? WHERE id=?');
    $stmt->execute([$name, $interests, $skills, $bio, $location, $photo, $availability, $user['id']]);

    $user = current_user($pdo);
}

// Get volunteer rating
$stmt = $pdo->prepare('SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM ratings WHERE volunteer_id=?');
$stmt->execute([$user['id']]);
$rating_data = $stmt->fetch();
$avg_rating = round($rating_data['avg_rating'], 1);
$total_ratings = $rating_data['total'];

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-wrapper">
  <div class="form-container">
    <h2>Edit Profile</h2>
    <form method="post" class="form">

      <label>Name
        <input type="text" name="name" value="<?php echo e($user['name'] ?? ''); ?>">
      </label>

      <label>Interests
        <input type="text" name="interests" value="<?php echo e($user['interests'] ?? ''); ?>">
        <small>Separate interests with commas</small>
      </label>

      <label>Skills
        <input type="text" name="skills" value="<?php echo e($user['skills'] ?? ''); ?>">
        <small>Separate skills with commas</small>
      </label>

      <label>Location
        <input type="text" name="location" value="<?php echo e($user['location'] ?? ''); ?>">
      </label>

      <label>Bio
        <textarea name="bio"><?php echo e($user['bio'] ?? ''); ?></textarea>
      </label>

      <!-- Availability Dropdown -->
      <label>Availability
        <select name="availability" required>
          <option value="">-- Select Availability --</option>
          <?php foreach($availability_options as $option): ?>
            <option value="<?php echo $option; ?>" <?php echo ($user['availability'] === $option) ? 'selected' : ''; ?>>
              <?php echo $option; ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>Choose Avatar</label>
      <div class="avatar-wrapper">
        <?php foreach ($avatars as $avatar): ?>
          <label class="avatar-label">
            <input type="radio" name="photo" value="<?php echo $avatar; ?>" <?php echo ($user['photo'] === $avatar) ? 'checked' : ''; ?> style="display:none;">
            <img src="uploads/avatars/<?php echo $avatar; ?>" class="avatar-img <?php echo ($user['photo'] === $avatar) ? 'selected' : ''; ?>">
          </label>
        <?php endforeach; ?>
      </div>

      <button type="submit">Save Profile</button>
    </form>
  </div>
</div>

<section class="profile-preview">
  <h3>Your Public Profile Preview</h3>
  <div class="profile-card">
    <img src="uploads/avatars/<?php echo e($user['photo'] ?? 'avatar1.png'); ?>" class="avatar">
    <h4><?php echo e($user['name'] ?: $user['email']); ?></h4>
    <p><strong>Type:</strong> <?php echo e($user['user_type']); ?></p>
    <p><strong>Interests:</strong> <?php echo e($user['interests']); ?></p>
    <p><strong>Skills:</strong> <?php echo e($user['skills']); ?></p>
    <p><strong>Location:</strong> <?php echo e($user['location']); ?></p>
    <p><strong>Availability:</strong> <?php echo e($user['availability'] ?? 'Not set'); ?></p>
    <p><?php echo nl2br(e($user['bio'])); ?></p>
    <p><strong>Rating:</strong> <?php echo $avg_rating ?: 0; ?> ★ (<?php echo $total_ratings; ?> ratings)</p>
  </div>
</section>

<style>
.avatar-wrapper { display:flex; flex-wrap:wrap; gap:12px; margin-bottom:20px; }
.avatar-label { cursor:pointer; text-align:center; }
.avatar-img { width:70px; height:70px; border-radius:50%; border:3px solid #ccc; transition:all 0.2s; }
.avatar-img.selected { border-color: var(--primary); }
.avatar-img:hover { filter:brightness(0.9); }

.profile-preview { margin:40px auto; max-width:500px; text-align:center; }
.profile-card { background:#fff; padding:20px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.08); }
.profile-card .avatar { width:100px; height:100px; border-radius:50%; margin-bottom:12px; }

@media(max-width:600px) { 
  .avatar-img { width:60px; height:60px; } 
}
</style>

<script>
// Avatar selection
document.querySelectorAll('.avatar-label').forEach(label => {
    label.addEventListener('click', function() {
        document.querySelectorAll('.avatar-img').forEach(img => img.classList.remove('selected'));
        this.querySelector('img').classList.add('selected');
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>



