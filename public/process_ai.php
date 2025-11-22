<?php
require_once "../includes/auth.php";
require_once "../config/db.php";
require_once "../services/AiService.php";

// Enable error reporting for debugging (disable in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["lesson_image"])) {
    $user_id = $_SESSION["user_id"];

    // Ensure uploads directory exists
    $uploadDir = "../uploads/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    // Sanitize and create unique filename
    $filename = time() . "_" . basename($_FILES["lesson_image"]["name"]);
    $targetFile = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES["lesson_image"]["tmp_name"], $targetFile)) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "Failed to upload image."]);
        exit;
    }

    try {
        $aiService = new AiService();

        // Call AI service with base64 inline image
        $aiResponse = $aiService->analyzeImage($targetFile);

        // Extract AI text output
        $aiText = $aiResponse["choices"][0]["message"]["content"] ?? "";

        // Parse NOTES and QUIZ from AI response (expect format NOTES: ... QUIZ: ...)
        $notes = "";
        $quiz = "";

        if (preg_match('/NOTES:(.*?)QUIZ:/s', $aiText, $matches)) {
            $notes = trim($matches[1]);
            // Extract quiz part after "QUIZ:"
            $quiz = trim(substr($aiText, strpos($aiText, "QUIZ:") + strlen("QUIZ:")));
        } else {
            // Fallback - save entire response as notes and placeholder for quiz
            $notes = $aiText ?: "AI did not return any notes.";
            $quiz = "No quiz generated.";
        }

        // Save upload info and AI output in database
        $stmt = $conn->prepare("INSERT INTO uploads (user_id, filename, ai_notes, ai_quiz) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $filename, $notes, $quiz);

        if ($stmt->execute()) {
            echo json_encode([
                "status" => "success",
                "message" => "Upload processed successfully.",
                "notes" => $notes,
                "quiz" => $quiz,
                "filename" => $filename
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to save upload info to database."]);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["status" => "error", "message" => "AI service error: " . $e->getMessage()]);
    }

} else {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No image uploaded or invalid request."]);
}
