<nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
    <a class="navbar-brand" href="dashboard.php">AI Student App</a>

    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">

            <?php if (isLoggedIn()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="my_uploads.php">My Uploads</a>
                </li>

                <?php if (isAdmin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_users.php">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_uploads.php">All Uploads</a>
                    </li>
                <?php endif; ?>

                <li class="nav-item">
                    <a class="nav-link text-danger" href="logout.php">Logout</a>
                </li>

            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Login</a>
                </li>
            <?php endif; ?>

        </ul>
    </div>
</nav>
