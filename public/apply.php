<?php
require_once __DIR__ . '/../includes/init.php';
require_login();
$user = current_user($pdo);
if ($user['user_type'] !== 'volunteer') {
    die('Only volunteers can apply.');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $place_id = (int)($_POST['place_id'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    // basic check that place exists
    $stmt = $pdo->prepare('SELECT id FROM places WHERE id = ?');
    $stmt->execute([$place_id]);
    if (!$stmt->fetch()) {
        die('Place not found.');
    }
    // insert
    $stmt = $pdo->prepare('INSERT INTO applications (place_id, volunteer_id, message) VALUES (?,?,?)');
    $stmt->execute([$place_id, $user['id'], $message]);
    header('Location: places.php?id=' . $place_id);
    exit;
}
header('Location: places.php');
exit;
