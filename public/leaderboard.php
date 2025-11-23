<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

// Fetch top 3 volunteers by average rating
$stmt = $pdo->query('
    SELECT u.id, u.name, u.photo, AVG(r.rating) as avg_rating, COUNT(r.id) as total_ratings
    FROM users u
    LEFT JOIN ratings r ON u.id = r.volunteer_id
    WHERE u.user_type="volunteer"
    GROUP BY u.id
    ORDER BY avg_rating DESC
    LIMIT 3
');
$top_rated = $stmt->fetchAll();

// Fetch top 3 volunteers by activity (number of applications)
$stmt = $pdo->query('
    SELECT u.id, u.name, u.photo, COUNT(a.id) as total_apps
    FROM users u
    LEFT JOIN applications a ON u.id = a.volunteer_id
    WHERE u.user_type="volunteer"
    GROUP BY u.id
    ORDER BY total_apps DESC
    LIMIT 3
');
$most_active = $stmt->fetchAll();

require_once __DIR__ . '/../includes/header.php';
?>

<div class="vc-container" style="padding-top:40px; max-width:1000px;">
    <h2>Leaderboard</h2>

    <h3>Top Rated Volunteers</h3>
    <div class="leaderboard-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:40px;">
        <?php
        $medals = ['#FFD700', '#C0C0C0', '#CD7F32']; // Gold, Silver, Bronze
        $positions = ['1st', '2nd', '3rd'];
        foreach ($top_rated as $index => $v): 
        ?>
            <div class="leader-card" style="background:var(--card);border-radius:12px;box-shadow:var(--shadow);padding:20px;text-align:center;position:relative;">
                <div style="position:absolute;top:-10px;right:-10px;font-weight:bold;font-size:18px;color:<?php echo $medals[$index]; ?>;">
                    <?php echo $positions[$index]; ?>
                </div>
                <img src="uploads/avatars/<?php echo e($v['photo'] ?? 'avatar1.png'); ?>" 
                     alt="avatar" style="width:80px;height:80px;border-radius:50%;margin-bottom:12px;">
                <h3 style="margin:0 0 6px;"><?php echo e($v['name']); ?></h3>
                <p style="margin:0 0 6px;">
                    <strong>Average Rating:</strong> <?php echo round($v['avg_rating'] ?? 0, 1); ?> ★
                </p>
                <p style="margin:0 0 6px;color:var(--muted);">
                    <strong>Total Ratings:</strong> <?php echo $v['total_ratings']; ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>

    <h3>Most Active Volunteers</h3>
    <div class="leaderboard-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;">
        <?php
        foreach ($most_active as $index => $v): 
        ?>
            <div class="leader-card" style="background:var(--card);border-radius:12px;box-shadow:var(--shadow);padding:20px;text-align:center;position:relative;">
                <div style="position:absolute;top:-10px;right:-10px;font-weight:bold;font-size:18px;color:<?php echo $medals[$index]; ?>;">
                    <?php echo $positions[$index]; ?>
                </div>
                <img src="uploads/avatars/<?php echo e($v['photo'] ?? 'avatar1.png'); ?>" 
                     alt="avatar" style="width:80px;height:80px;border-radius:50%;margin-bottom:12px;">
                <h3 style="margin:0 0 6px;"><?php echo e($v['name']); ?></h3>
                <p style="margin:0 0 6px;color:var(--muted);">
                    <strong>Applications Submitted:</strong> <?php echo $v['total_apps']; ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.leader-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(13,42,80,0.15);
    transition: transform 0.2s, box-shadow 0.2s;
}

@media(max-width:600px) {
    .leaderboard-grid {
        grid-template-columns:1fr;
    }
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


