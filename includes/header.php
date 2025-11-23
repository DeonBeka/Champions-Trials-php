<?php
require_once __DIR__ . '/init.php';
$user = current_user($pdo);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Volunteer Connect</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="stylesheet" href="css/admin.css"> <!-- NEW -->
</head>

<body>
<header class="vc-header">
  <div class="vc-container header-inner">
    <div class="brand">
      <a href="index.php" class="logo">
        <svg width="34" height="26" viewBox="0 0 24 24" fill="none">
          <path d="M2 12h7" stroke="#1565D8" stroke-width="2" stroke-linecap="round"/>
          <path d="M9 6l5 6-5 6" stroke="#1565D8" stroke-width="2" stroke-linecap="round"/>
        </svg>
        <span class="brand-text">Volunteer Connect</span>
      </a>
    </div>

    <nav class="nav">
      <a href="index.php">Home</a>
      <a href="places.php">Opportunities</a>
      <a href="search.php?q=&location=">Volunteers</a>
      <a href="messages.php">Messages</a>
      <a href="dashboard.php">Dashboard</a>

      <?php if ($user && !empty($user['is_admin']) && $user['is_admin'] == 1): ?>
      <a href="admin_dashboard.php" class="admin-link">Admin Dashboard</a>
  <?php endif; ?>
    </nav>

    <div class="auth">
      <?php if ($user): ?>
        <a class="btn btn-ghost" href="profile.php"><?= e($user['name'] ?: $user['email']); ?></a>
        <a class="btn btn-primary" href="logout.php">Logout</a>
      <?php else: ?>
        <a class="btn btn-ghost" href="login.php">Login</a>
        <a class="btn btn-primary" href="register.php">Sign Up</a>
      <?php endif; ?>
    </div>

    <button class="nav-toggle">&#9776;</button>
  </div>
</header>

<main>


