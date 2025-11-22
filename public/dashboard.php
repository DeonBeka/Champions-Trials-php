<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
$user = current_user($pdo);
require_once __DIR__ . '/../includes/header.php';
?>
<h2>Dashboard</h2>

<?php if ($user['user_type'] === 'seeker'): ?>
  <p>You are a <strong>seeker</strong>. <a href="add_place.php">Add a volunteer place</a></p>
  <h3>Your places</h3>
  <?php
    $stmt = $pdo->prepare('SELECT * FROM places WHERE seeker_id = ? ORDER BY created_at DESC');
    $stmt->execute([$user['id']]);
    $places = $stmt->fetchAll();
  ?>
  <?php if (empty($places)): ?><p>No places yet.</p><?php endif; ?>
  <?php foreach ($places as $p): ?>
    <div class="place">
      <h4><?php echo e($p['title']); ?></h4>
      <p><?php echo e($p['description']); ?></p>
      <p><a href="places.php?id=<?php echo $p['id']; ?>">View</a></p>
    </div>
  <?php endforeach; ?>

<?php else: ?>
  <p>You are a <strong>volunteer</strong>. Browse <a href="places.php">places</a> or search for people to volunteer with.</p>
  <h3>Suggested places</h3>
  <?php
    $stmt = $pdo->query('SELECT * FROM places ORDER BY created_at DESC LIMIT 6');
    $places = $stmt->fetchAll();
  ?>
  <?php if (empty($places)): ?><p>No places yet.</p><?php endif; ?>
  <?php foreach ($places as $p): ?>
    <div class="place">
      <h4><?php echo e($p['title']); ?></h4>
      <p><?php echo e(substr($p['description'],0,200)); ?>...</p>
      <p><a href="places.php?id=<?php echo $p['id']; ?>">Apply / View</a></p>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
