document.getElementById("uploadForm").addEventListener("submit", function(e) {
    e.preventDefault();

    const statusDiv = document.getElementById("uploadStatus");
    statusDiv.innerHTML = "<div class='alert alert-info'>Uploading & analyzing... please wait.</div>";

    const formData = new FormData();
    formData.append("lesson_image", document.getElementById("lesson_image").files[0]);

    fetch("process_ai.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.text())
    .then(response => {
        statusDiv.innerHTML = "<div class='alert alert-success'>AI analysis complete!</div>";
        window.location.href = "my_uploads.php";
    })
    .catch(err => {
        statusDiv.innerHTML = "<div class='alert alert-danger'>Error processing image.</div>";
    });
});
