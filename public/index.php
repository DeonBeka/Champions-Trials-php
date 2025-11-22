<?php
session_start();
require_once "../config/db.php";

if (isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $res = $stmt->get_result();
    if ($res->num_rows === 1) {
        $user = $res->fetch_assoc();

        if (password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["email"]   = $user["email"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["role"] = $user["role"];

            header("Location: dashboard.php");
            exit;
        }
    }

    $error = "Invalid login information.";
}
?>

<?php include "../includes/header.php"; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-4">

            <div class="card p-4 shadow">
                <h3 class="text-center mb-3">Login</h3>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input class="form-control mb-3" type="email" name="email" placeholder="Email" required>
                    <input class="form-control mb-3" type="password" name="password" placeholder="Password" required>

                    <button class="btn btn-primary w-100">Login</button>
                </form>

                <p class="mt-3 text-center">
                    No account? <a href="signup.php">Sign up</a>
                </p>
            </div>

        </div>
    </div>
</div>

</body>
</html>
