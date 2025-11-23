<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/header.php';

$q = trim($_GET['q'] ?? '');
$location = trim($_GET['location'] ?? '');

$params = [];
$where = [];
if ($q !== '') {
    $where[] = '(interests LIKE ? OR skills LIKE ? OR name LIKE ?)';
    $params[] = "%$q%"; $params[] = "%$q%"; $params[] = "%$q%";
}
if ($location !== '') {
    $where[] = 'location LIKE ?';
    $params[] = "%$location%";
}

$sql = 'SELECT id,name,user_type,interests,skills,location,photo FROM users';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY created_at DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();
?>

<div class="vc-container">
  <h2 style="margin-bottom:20px;">Search Results</h2>

  <?php if (!$results): ?>
      <p>No results found.</p>
  <?php else: ?>
      <div class="results-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:18px;">
      <?php foreach ($results as $r):
            // Fetch average rating and count for this volunteer
            $stmt2 = $pdo->prepare('SELECT AVG(rating) as avg_rating, COUNT(*) as total FROM ratings WHERE volunteer_id=?');
            $stmt2->execute([$r['id']]);
            $rating_data = $stmt2->fetch();
            $avg_rating = round($rating_data['avg_rating'],1);
            $total_ratings = $rating_data['total'];
      ?>
          <div class="volunteer-card" style="background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:20px;display:flex;flex-direction:column;align-items:center;text-align:center;transition:transform 0.2s;">
            <img src="uploads/avatars/<?php echo e($r['photo'] ?? 'avatar1.png'); ?>" alt="avatar" style="width:80px;height:80px;border-radius:50%;margin-bottom:12px;">
            <h3 style="margin:0 0 6px;"><?php echo e($r['name'] ?: 'User #' . e($r['id'])); ?></h3>
            <small style="color:var(--muted);margin-bottom:10px;"><?php echo e($r['user_type']); ?></small>
            <p style="margin:0 0 6px;"><strong>Interests:</strong> <?php echo e($r['interests']); ?></p>
            <p style="margin:0 0 6px;"><strong>Skills:</strong> <?php echo e($r['skills']); ?></p>
            <p style="margin:0 0 6px;color:var(--muted);">
                <strong>Rating:</strong>
                <?php if ($total_ratings > 0): ?>
                    <?php echo $avg_rating; ?> ★ (<?php echo $total_ratings; ?>)
                <?php else: ?>
                    No ratings yet
                <?php endif; ?>
            </p>
            <p style="margin:0 0 10px;color:var(--muted);"><?php echo e($r['location']); ?></p>
            <a href="profile_view.php?id=<?php echo e($r['id']); ?>" class="btn btn-ghost" style="margin-top:auto;">View Profile</a>
          </div>
      <?php endforeach; ?>
      </div>
  <?php endif; ?>
</div>

<style>
  .volunteer-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 28px rgba(13,42,80,0.15);
  }

  @media (max-width:600px){
      .results-grid{
          grid-template-columns:1fr;
      }
  }
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>



