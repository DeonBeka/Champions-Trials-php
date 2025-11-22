<?php
require_once "../includes/auth.php";
require_once "../includes/header.php";
require_once "../includes/navbar.php";
?>

<div class="container mt-4">
    <h3>Upload Lesson Image</h3>
    <p class="text-muted">The AI will analyze your image and generate notes + a quiz.</p>

    <div class="card p-4 shadow">
        <form id="uploadForm" enctype="multipart/form-data">
            <input type="file" name="lesson_image" id="lesson_image" class="form-control mb-3" accept="image/*" required>
            <button class="btn btn-primary w-100" type="submit">Analyze with AI</button>
        </form>

        <div id="uploadStatus" class="mt-3"></div>
    </div>
</div>

<script src="../assets/js/upload.js"></script>

</body>
</html>
