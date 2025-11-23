<?php
require_once __DIR__ . '/../includes/init.php';
require_login();

$user = current_user($pdo);

// Only seekers can rate
if ($user['user_type'] !== 'seeker') die('Only seekers can rate volunteers.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $volunteer_id = $_POST['volunteer_id'] ?? null;
    $place_id = $_POST['place_id'] ?? null;
    $rating = (int) ($_POST['rating'] ?? 0);

    if (!$volunteer_id || !$place_id || $rating < 1 || $rating > 5) {
        die('Invalid input.');
    }

    // Make sure seeker owns this place
    $stmt = $pdo->prepare('SELECT * FROM places WHERE id=? AND seeker_id=?');
    $stmt->execute([$place_id, $user['id']]);
    if (!$stmt->fetch()) die('You do not own this place.');

    // Check if already rated
    $stmt = $pdo->prepare('SELECT id FROM ratings WHERE volunteer_id=? AND place_id=?');
    $stmt->execute([$volunteer_id, $place_id]);
    if ($stmt->fetch()) die('Already rated.');

    // Insert rating
    $stmt = $pdo->prepare('INSERT INTO ratings (volunteer_id,seeker_id,place_id,rating) VALUES (?,?,?,?)');
    $stmt->execute([$volunteer_id,$user['id'],$place_id,$rating]);

    header('Location: view_applications.php?place_id=' . $place_id);
    exit;
}
