<?php
// includes/header.php
require_once __DIR__ . '/init.php';
$user = current_user($pdo);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Volunteer Connect</title>
  <!-- Optional: swap or remove Google Fonts if offline -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<header class="vc-header">
  <div class="vc-container header-inner">
    <div class="brand">
      <a href="index.php" class="logo">
        <svg width="34" height="26" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
          <path d="M2 12h7" stroke="#1565D8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M9 6l5 6-5 6" stroke="#1565D8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
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
    </nav>

    <div class="auth">
      <?php if ($user): ?>
        <a class="btn btn-ghost" href="profile.php"><?php echo e($user['name'] ?: $user['email']); ?></a>
        <a class="btn btn-primary" href="logout.php">Logout</a>
      <?php else: ?>
        <a class="btn btn-ghost" href="login.php">Login</a>
        <a class="btn btn-primary" href="register.php">Sign Up</a>
      <?php endif; ?>
    </div>

    <button class="nav-toggle" aria-label="Toggle menu">&#9776;</button>
  </div>
</header>

<main>
  

