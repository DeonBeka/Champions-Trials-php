<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
$user = current_user($pdo);

$place_id = (int)($_GET['id'] ?? 0);
if (!$place_id) die('Place not found.');

// Fetch place info
$stmt = $pdo->prepare('SELECT p.*, u.name AS seeker_name FROM places p JOIN users u ON p.seeker_id = u.id WHERE p.id = ?');
$stmt->execute([$place_id]);
$place = $stmt->fetch();
if (!$place) die('Place not found.');

$errors = [];
$success = '';

// Handle volunteer application (volunteer side)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user['user_type'] === 'volunteer') {
    $message = trim($_POST['message'] ?? '');
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

// Handle seeker actions (accept/reject + rate + message)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $user['user_type'] === 'seeker') {
    $app_id = (int)($_POST['app_id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $rating = (int)($_POST['rating'] ?? 0);
    $rating_message = trim($_POST['rating_message'] ?? '');

    $stmt = $pdo->prepare('SELECT * FROM applications WHERE id = ? AND place_id = ?');
    $stmt->execute([$app_id, $place_id]);
    $application = $stmt->fetch();

    if ($application) {
        if ($action === 'accept') {
            $stmt = $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?');
            $stmt->execute(['accepted', $app_id]);
        } elseif ($action === 'reject') {
            $stmt = $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?');
            $stmt->execute(['rejected', $app_id]);
        } elseif ($action === 'rate' && $rating >=1 && $rating <=5) {
            $stmt = $pdo->prepare('SELECT * FROM ratings WHERE volunteer_id=? AND place_id=?');
            $stmt->execute([$application['volunteer_id'], $place_id]);
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare('INSERT INTO ratings (volunteer_id,seeker_id,place_id,rating,message) VALUES (?,?,?,?,?)');
                $stmt->execute([$application['volunteer_id'], $user['id'], $place_id, $rating, $rating_message]);
            }
        }
    }

    header('Location: place_detail.php?id=' . $place_id);
    exit;
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

<?php else: ?>
    <h3>Applicants</h3>
    <?php
    $stmt = $pdo->prepare('SELECT a.*, u.id AS volunteer_id, u.name, u.photo FROM applications a JOIN users u ON a.volunteer_id = u.id WHERE a.place_id = ? ORDER BY a.created_at DESC');
    $stmt->execute([$place_id]);
    $applications = $stmt->fetchAll();
    ?>
    <?php if (empty($applications)): ?>
        <p>No volunteers have applied yet.</p>
    <?php else: ?>
        <?php foreach ($applications as $app): 
            $stmt = $pdo->prepare('SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM ratings WHERE volunteer_id=?');
            $stmt->execute([$app['volunteer_id']]);
            $rating_data = $stmt->fetch();
            $avg_rating = round($rating_data['avg_rating'],1);
            $total_ratings = $rating_data['total'];
        ?>
        <div style="display:flex;align-items:center;background:var(--card);padding:12px;border-radius:12px;margin-bottom:12px;box-shadow:var(--shadow);">
            <?php if ($app['photo'] && file_exists(__DIR__ . '/../uploads/' . $app['photo'])): ?>
              <img src="../uploads/<?php echo e($app['photo']); ?>" alt="" style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin-right:12px;">
            <?php endif; ?>
            <div style="flex:1;">
              <strong><?php echo e($app['name']); ?></strong>
              <p><?php echo nl2br(e($app['message'])); ?></p>
              <p>Status: <strong><?php echo e($app['status']); ?></strong></p>
              <p>Average Rating: <?php echo $avg_rating ?: 0; ?> ★ (<?php echo $total_ratings; ?>)</p>
            </div>

            <div style="display:flex;flex-direction:column;gap:6px;">
              <?php if ($app['status'] === 'applied'): ?>
                  <form method="post" style="display:flex;gap:6px;align-items:center;">
                      <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                      <button type="submit" name="action" value="accept" class="btn btn-primary">Accept</button>
                      <button type="submit" name="action" value="reject" class="btn btn-ghost" style="color:#a40000;border:1px solid rgba(15,23,42,0.06);">Reject</button>
                  </form>
              <?php elseif ($app['status'] === 'accepted'): 
                  $stmt = $pdo->prepare('SELECT rating, message FROM ratings WHERE volunteer_id=? AND place_id=?');
                  $stmt->execute([$app['volunteer_id'], $place_id]);
                  $existing_rating = $stmt->fetch();
                  if (!$existing_rating): ?>
                      <form method="post" class="star-rating-form" style="display:flex;flex-direction:column;gap:6px;">
                          <input type="hidden" name="app_id" value="<?php echo $app['id']; ?>">
                          <div class="stars" style="display:flex;gap:4px;cursor:pointer;">
                              <?php for($i=1;$i<=5;$i++): ?>
                                  <span class="star" data-value="<?php echo $i; ?>" style="font-size:20px; color:#ccc;">★</span>
                              <?php endfor; ?>
                          </div>
                          <input type="hidden" name="rating" value="">
                          <textarea name="rating_message" placeholder="Leave feedback for this volunteer" style="margin-top:6px;"></textarea>
                          <button type="submit" name="action" value="rate" class="btn btn-primary">Submit Rating</button>
                      </form>
                  <?php else: ?>
                      <p>Rating given: <?php echo $existing_rating['rating']; ?> ★</p>
                      <?php if($existing_rating['message']): ?>
                        <p><em>Feedback: <?php echo nl2br(e($existing_rating['message'])); ?></em></p>
                      <?php endif; ?>
                  <?php endif; ?>
              <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>
</div>

<script>
const forms = document.querySelectorAll('.star-rating-form');
forms.forEach(form => {
  const stars = form.querySelectorAll('.star');
  const input = form.querySelector('input[name="rating"]');
  stars.forEach(star => {
    star.addEventListener('click', () => {
      input.value = star.dataset.value;
      stars.forEach(s => s.classList.remove('selected'));
      for(let i=0;i<star.dataset.value;i++){
        stars[i].classList.add('selected');
      }
    });
    star.addEventListener('mouseover', () => {
      stars.forEach(s => s.style.color = '#ccc');
      for(let i=0;i<star.dataset.value;i++){
        stars[i].style.color = '#ffb400';
      }
    });
    star.addEventListener('mouseout', () => {
      stars.forEach(s => s.classList.remove('selected'));
      const val = input.value;
      for(let i=0;i<val;i++){
        stars[i].classList.add('selected');
      }
    });
  });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

