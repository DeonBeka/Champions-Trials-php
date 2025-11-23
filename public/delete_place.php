<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$user = current_user($pdo);
if (!$user['is_admin']) die('Access denied. Admins only.');

$id = (int)($_GET['id'] ?? 0);
if (!$id) die('Place not found.');

$stmt = $pdo->prepare('DELETE FROM places WHERE id=?');
$stmt->execute([$id]);

header('Location: admin_dashboard.php');
exit;
