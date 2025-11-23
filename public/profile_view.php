<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$profile_id = (int)($_GET['id'] ?? 0);
if (!$profile_id) die('User not found.');

// Fetch user info
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$profile_id]);
$user_profile = $stmt->fetch();
if (!$user_profile) die('User not found.');

// Fetch all ratings & feedback for this user
$stmt = $pdo->prepare('
    SELECT r.rating, r.message AS feedback, r.created_at, u.name AS seeker_name
    FROM ratings r
    JOIN users u ON r.seeker_id = u.id
    WHERE r.volunteer_id = ?
    ORDER BY r.created_at DESC
');
$stmt->execute([$profile_id]);
$ratings = $stmt->fetchAll();

// Compute average rating
$total_ratings = count($ratings);
$avg_rating = $total_ratings > 0 ? round(array_sum(array_column($ratings,'rating'))/$total_ratings,1) : 0;

require_once __DIR__ . '/../includes/header.php';
?>

<div class="vc-container" style="max-width:700px;padding-top:40px;">
  <div style="text-align:center;margin-bottom:30px;">
    <img src="uploads/avatars/<?php echo e($user_profile['photo'] ?? 'avatar1.png'); ?>" style="width:100px;height:100px;border-radius:50%;margin-bottom:12px;">
    <h2><?php echo e($user_profile['name'] ?: 'User #' . e($user_profile['id'])); ?></h2>
    <p><strong>Type:</strong> <?php echo e($user_profile['user_type']); ?></p>
    <p><strong>Location:</strong> <?php echo e($user_profile['location']); ?></p>
    <p><strong>Interests:</strong> <?php echo e($user_profile['interests']); ?></p>
    <p><strong>Skills:</strong> <?php echo e($user_profile['skills']); ?></p>
    <p><strong>Average Rating:</strong> <?php echo $avg_rating ?: 'No ratings yet'; ?> <?php if($total_ratings) echo "($total_ratings)"; ?></p>
  </div>

  <h3>Feedback from Seekers</h3>
  <?php if(empty($ratings)): ?>
      <p>No feedback yet.</p>
  <?php else: ?>
      <?php foreach($ratings as $r): ?>
          <div style="background:var(--card);padding:12px;border-radius:12px;margin-bottom:12px;box-shadow:var(--shadow);">
              <p><strong><?php echo e($r['seeker_name']); ?></strong> gave <strong><?php echo e($r['rating']); ?> ★</strong> on <?php echo date('Y-m-d', strtotime($r['created_at'])); ?></p>
              <?php if($r['feedback']): ?>
                  <p><em>"<?php echo nl2br(e($r['feedback'])); ?>"</em></p>
              <?php endif; ?>
          </div>
      <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

