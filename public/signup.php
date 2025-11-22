<?php
session_start();
require_once "../config/db.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'student')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        $success = "Account created successfully! You may login.";
    } else {
        $error = "Email or username already exists.";
    }
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">

            <div class="card p-4 shadow">
                <h3 class="text-center mb-3">Create Account</h3>

                <?php if ($success): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input class="form-control mb-3" name="username" placeholder="Username" required>
                    <input class="form-control mb-3" type="email" name="email" placeholder="Email" required>
                    <input class="form-control mb-3" type="password" name="password" placeholder="Password" required>
                    
                    <button class="btn btn-success w-100">Sign Up</button>
                </form>

                <p class="text-center mt-3">
                    Already registered? <a href="index.php">Login</a>
                </p>
            </div>

        </div>
    </div>
</div>

</body>
</html>
