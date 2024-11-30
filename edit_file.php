<?php
session_start();

$rootDir = 'uploads/';

if (!isset($_SESSION['authenticated'])) {
    header('Location: login.php');
    exit();
}

// Check if the file parameter is set
if (!isset($_GET['file'])) {
    die("No file specified.");
}

// Get the file path
$fileName = basename($_GET['file']);
$filePath = $rootDir . $fileName;

// Validate the file path
if (!file_exists($filePath) || !is_file($filePath)) {
    die("File not found.");
}

// Read file content
$fileContents = file_get_contents($filePath);

// Handle file editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['file_contents'])) {
    file_put_contents($filePath, $_POST['file_contents']);
    $fileContents = $_POST['file_contents'];
    $message = "File updated successfully!";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit File</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h1>Edit File: <?= htmlspecialchars($fileName) ?></h1>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <textarea name="file_contents" class="form-control" rows="10"><?= htmlspecialchars($fileContents) ?></textarea>
        </div>
        <button type="submit" class="btn btn-success">Save Changes</button>
        <a href="xrpclx.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</body>
</html>
