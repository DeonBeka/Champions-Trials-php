<?php
require_once __DIR__ . '/../includes/init.php';
if (is_logged_in()) header('Location: dashboard.php');

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $user_type = ($_POST['user_type'] === 'seeker') ? 'seeker' : 'volunteer';
    $interests = trim($_POST['interests'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $location = trim($_POST['location'] ?? '');

    if (!$email) $errors[] = 'Valid email required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (email,password,user_type,name,interests,skills,bio,location)
                                   VALUES (?,?,?,?,?,?,?,?)');
            $stmt->execute([$email, $hash, $user_type, $name, $interests, $skills, $bio, $location]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header('Location: profile.php');
            exit;
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<div class="page-wrapper">
    <div class="form-container">
        <h2>Create Your Account</h2>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach($errors as $e) echo '<p>'.e($e).'</p>'; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="form">
            <label>Email
                <input name="email" type="email" required>
            </label>

            <label>Password
                <input name="password" type="password" required>
            </label>

            <label>Name
                <input name="name" type="text">
            </label>

            <label>Role
                <select name="user_type">
                    <option value="volunteer">Volunteer (I want to help)</option>
                    <option value="seeker">Seeker (I need volunteers)</option>
                </select>
            </label>

            <label>Interests (comma separated)
                <input name="interests" type="text">
            </label>

            <label>Skills (comma separated)
                <input name="skills" type="text">
            </label>

            <label>Location
                <input name="location" type="text">
            </label>

            <label>Short bio
                <textarea name="bio"></textarea>
            </label>

            <button type="submit">Create account</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
