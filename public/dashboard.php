<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
$user = current_user($pdo);
require_once __DIR__ . '/../includes/header.php';
?>

<div class="vc-container" style="padding-top:40px;">
  <h2 style="margin-bottom:20px;">Dashboard</h2>

  <?php if ($user['user_type'] === 'seeker'): ?>
      <p>You are a <strong>seeker</strong>. <a href="add_place.php" class="btn btn-primary">Add a Volunteer Place</a></p>

      <h3 style="margin-top:30px;">Your Places</h3>
      <?php
        $stmt = $pdo->prepare('SELECT p.*, COUNT(a.id) AS applicants_count 
                               FROM places p 
                               LEFT JOIN applications a ON a.place_id = p.id 
                               WHERE p.seeker_id = ? 
                               GROUP BY p.id 
                               ORDER BY p.created_at DESC');
        $stmt->execute([$user['id']]);
        $places = $stmt->fetchAll();
      ?>
      <?php if (empty($places)): ?>
        <p>No places uploaded yet.</p>
      <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;">
          <?php foreach ($places as $p): ?>
            <div class="place-card" style="background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:20px;display:flex;flex-direction:column;">
              <?php if ($p['image']): ?>
                <img src="uploads/places/<?php echo e($p['image']); ?>" alt="" style="width:100%;border-radius:10px;margin-bottom:12px;height:150px;object-fit:cover;">
              <?php endif; ?>
              <h4 style="margin:0 0 6px;"><?php echo e($p['title']); ?></h4>
              <p style="margin:0 0 6px;"><?php echo e(substr($p['description'],0,100)); ?>...</p>
              <p style="margin:0 0 6px;"><strong>Applicants:</strong> <?php echo $p['applicants_count']; ?></p>
              <a href="place_detail.php?id=<?php echo $p['id']; ?>" class="btn btn-ghost" style="margin-top:auto;">Manage</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

  <?php else: ?>
      <p>You are a <strong>volunteer</strong>. Browse volunteer places below:</p>

      <h3 style="margin-top:30px;">Available Places</h3>
      <?php
        $stmt = $pdo->query('SELECT * FROM places ORDER BY created_at DESC LIMIT 12');
        $places = $stmt->fetchAll();
      ?>
      <?php if (empty($places)): ?>
        <p>No places available at the moment.</p>
      <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;">
          <?php foreach ($places as $p): ?>
            <div class="place-card" style="background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:20px;display:flex;flex-direction:column;">
              <?php if ($p['image']): ?>
                <img src="uploads/places/<?php echo e($p['image']); ?>" alt="" style="width:100%;border-radius:10px;margin-bottom:12px;height:150px;object-fit:cover;">
              <?php endif; ?>
              <h4 style="margin:0 0 6px;"><?php echo e($p['title']); ?></h4>
              <p style="margin:0 0 6px;"><?php echo e(substr($p['description'],0,100)); ?>...</p>
              <p style="margin:0 0 6px;color:var(--muted);"><strong>Location:</strong> <?php echo e($p['location']); ?></p>
              <a href="place_detail.php?id=<?php echo $p['id']; ?>" class="btn btn-primary" style="margin-top:auto;">View / Apply</a>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
  <?php endif; ?>
</div>

<style>
  .place-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 28px rgba(13,42,80,0.15);
      transition: all 0.2s ease;
  }

  @media (max-width:600px){
      .place-card {
          margin-bottom:15px;
      }
  }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

