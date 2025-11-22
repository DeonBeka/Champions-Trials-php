<?php
require_once __DIR__ . '/../includes/init.php';
require_once __DIR__ . '/../includes/header.php';

// single place view
if (!empty($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare('SELECT p.*, u.name AS seeker_name, u.email AS seeker_email FROM places p JOIN users u ON p.seeker_id = u.id WHERE p.id = ?');
    $stmt->execute([$id]);
    $p = $stmt->fetch();
    if (!$p) {
        echo '<p>Not found</p>';
    } else {
        echo '<h2>' . e($p['title']) . '</h2>';
        if ($p['image']) echo '<img src="../uploads/'.e($p['image']).'" class="place-image" alt="">';
        echo '<p>'.nl2br(e($p['description'])).'</p>';
        echo '<p><strong>Location:</strong> ' . e($p['location']). '</p>';
        echo '<p><strong>Posted by:</strong> ' . e($p['seeker_name']) . ' (' . e($p['seeker_email']) . ')</p>';

        // show apply button/form if user is volunteer
        if (is_logged_in()) {
            $user = current_user($pdo);
            if ($user['user_type'] === 'volunteer') {
                echo '<hr>';
                echo '<h3>Apply for this place</h3>';
                echo '<form method="post" action="apply.php">';
                echo '<input type="hidden" name="place_id" value="'.e($p['id']).'">';
                echo '<label>Message<textarea name="message"></textarea></label>';
                echo '<button type="submit">Apply</button>';
                echo '</form>';
            }
        } else {
            echo '<p><a href="login.php">Log in</a> to apply.</p>';
        }
    }
    require_once __DIR__ . '/../includes/footer.php';
    exit;
}

// list all
$stmt = $pdo->query('SELECT p.id,p.title,p.description,p.location,p.tags,u.name FROM places p JOIN users u ON p.seeker_id = u.id ORDER BY p.created_at DESC');
$places = $stmt->fetchAll();

echo '<h2>Volunteer Opportunities</h2>';
foreach ($places as $p) {
    echo '<div class="place">';
    echo '<h3>' . e($p['title']) . '</h3>';
    echo '<p>' . e(substr($p['description'],0,200)) . '...</p>';
    echo '<p><em>' . e($p['location']) . '</em></p>';
    echo '<p><a href="places.php?id=' . e($p['id']) . '">View</a></p>';
    echo '</div>';
}

require_once __DIR__ . '/../includes/footer.php';
