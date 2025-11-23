<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
$user = current_user($pdo);

$place_id = (int)($_GET['id'] ?? 0);
if (!$place_id) {
    die('Place not found.');
}

// Fetch place info
$stmt = $pdo->prepare('SELECT p.*, u.name AS seeker_name FROM places p JOIN users u ON p.seeker_id = u.id WHERE p.id = ?');
$stmt->execute([$place_id]);
$place = $stmt->fetch();
if (!$place) die('Place not found.');

$errors = [];
$success = '';

// Handle volunteer application
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user['user_type'] === 'volunteer') {
    $message = trim($_POST['message'] ?? '');
    // Check if already applied
    $stmt = $pdo->prepare('SELECT * FROM applications WHERE place_id = ? AND volunteer_id = ?');
    $stmt->execute([$place_id, $user['id']]);
    if ($stmt->fetch()) {
        $errors[] = 'You have already applied to this place.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO applications (place_id, volunteer_id, message) VALUES (?,?,?)');
        $stmt->execute([$place_id, $user['id'], $message]);
        $success = 'Application submitted successfully!';
    }
}

// Handle seeker approving/rejecting volunteer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user['user_type'] === 'seeker') {
    $app_id = (int)($_POST['app_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    // Ensure the application belongs to this place
    $stmt = $pdo->prepare('SELECT * FROM applications WHERE id = ? AND place_id = ?');
    $stmt->execute([$app_id, $place_id]);
    $application = $stmt->fetch();
    if ($application) {
        if ($action === 'accept' || $action === 'reject') {
            $stmt = $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?');
            $stmt->execute([$action, $app_id]);
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="vc-container" style="padding-top:40px; max-width:800px;">
  <h2><?php echo e($place['title']); ?></h2>
  <p><strong>Posted by:</strong> <?php echo e($place['seeker_name']); ?></p>
  <p><strong>Location:</strong> <?php echo e($place['location']); ?></p>
  <?php if ($place['tags']): ?>
    <p><strong>Tags:</strong> <?php echo e($place['tags']); ?></p>
  <?php endif; ?>
  <?php if ($place['image'] && file_exists(__DIR__ . '/../uploads/' . $place['image'])): ?>
    <img src="../uploads/<?php echo e($place['image']); ?>" alt="" style="width:100%;max-height:300px;object-fit:cover;border-radius:12px;margin-bottom:20px;">
  <?php endif; ?>

  <h3>Description</h3>
  <p><?php echo nl2br(e($place['description'])); ?></p>

  <?php if ($place['requirements']): ?>
    <h3>Requirements</h3>
    <p><?php echo nl2br(e($place['requirements'])); ?></p>
  <?php endif; ?>

  <?php if ($user['user_type'] === 'volunteer'): ?>
      <?php if (!empty($success)) echo '<div class="btn btn-cta" style="margin-bottom:15px;">' . e($success) . '</div>'; ?>
      <?php if (!empty($errors)) foreach($errors as $e) echo '<div class="errors"><p>' . e($e) . '</p></div>'; ?>

      <h3>Apply to this place</h3>
      <form method="post" class="form">
        <label>Message<textarea name="message" required placeholder="Introduce yourself or explain why you want to volunteer"></textarea></label>
        <button type="submit">Apply</button>
      </form>
  <?php else: // seeker view ?>
      <h3>Applicants</h3>
      <?php
      $stmt = $pdo->prepare('SELECT a.*, u.name, u.photo FROM applications a JOIN users u ON a.volunteer_id = u.id WHERE a.place_id = ? ORDER BY a.created_at DESC');
      $stmt->execute([$place_id]);
      $applications = $stmt->fetchAll();
      ?>
      <?php if (empty($applications)): ?>
        <p>No volunteers have applied yet.</p>
      <?php else: ?>
        <?php foreach ($applications as $app): ?>
          <div style="display:flex;align-items:center;background:var(--card);padding:12px;border-radius:12px;margin-bottom:12px;box-shadow:var(--shadow);">
            <?php if ($app['photo'] && file_exists(__DIR__ . '/../uploads/' . $app['photo'])): ?>
              <img src="../uploads/<?php echo e($app['photo']); ?>" alt="" style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin-right:12px;">
            <?php endif; ?>
            <div style="flex:1;">
              <strong><?php echo e($app['name']); ?></strong>
              <p><?php echo nl2br(e($app['message'])); ?></p>
              <p>Status: <strong><?php echo e($app['status']); ?></strong></p>
            </div>
            <form method="post" style="display:flex;gap:6px;flex-direction:column;">
              <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
              <?php if($app['status'] === 'applied'): ?>
                <button type="submit" name="action" value="accept" class="btn btn-primary">Accept</button>
                <button type="submit" name="action" value="reject" class="btn btn-ghost" style="color:#a40000;border:1px solid rgba(15,23,42,0.06);">Reject</button>
              <?php endif; ?>
            </form>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
