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
$sql = 'SELECT id,name,user_type,interests,skills,location FROM users';
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= ' ORDER BY created_at DESC LIMIT 200';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$results = $stmt->fetchAll();

echo '<h2>Search results</h2>';
if (!$results) echo '<p>No results</p>';
foreach ($results as $r) {
    echo '<div class="result">';
    echo '<h3>' . e($r['name'] ?: 'User #' . e($r['id'])) . ' <small>(' . e($r['user_type']) . ')</small></h3>';
    echo '<p>' . e($r['interests']) . ' | ' . e($r['skills']) . '</p>';
    echo '<p>' . e($r['location']) . '</p>';
    echo '</div>';
}

require_once __DIR__ . '/../includes/footer.php';
