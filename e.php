<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION['authenticated'])) {
    header('Location: xrpclx.php'); // Redirect to login page if not authenticated
    exit();
}

// Define the root directory for file management
$rootDir = 'C:/xampp/htdocs/project/uploads'; // Use absolute path to uploads
if (!file_exists($rootDir)) {
    die("Error: Root directory does not exist.");
}

// Get the directory and file to edit
$currentDir = isset($_GET['dir']) ? $_GET['dir'] : $rootDir;
$editFileName = isset($_GET['edit']) ? $_GET['edit'] : '';

$currentDir = rtrim($currentDir, '/') . '/';
$editFilePath = $currentDir . $editFileName;

// Debugging output
echo "<pre>";
echo "Root Directory: " . $rootDir . "\n";
echo "Current Directory (Input): " . $currentDir . "\n";
echo "Edit File Name: " . $editFileName . "\n";
echo "Constructed Edit File Path: " . $editFilePath . "\n";
echo "Resolved Edit File Path (realpath): " . realpath($editFilePath) . "\n";
echo "Resolved Root Directory (realpath): " . realpath($rootDir) . "\n";
echo "</pre>";

// Sanitize file access
$resolvedEditFilePath = realpath($editFilePath);
$resolvedRootDir = realpath($rootDir);

if (!$resolvedEditFilePath) {
    die("Error: Resolved file path is invalid.");
}

if (!str_starts_with($resolvedEditFilePath, $resolvedRootDir)) {
    die("Error: File is outside the root directory.");
}

if (!is_file($resolvedEditFilePath)) {
    die("Error: File does not exist or is not a valid file.");
}

// File content handling
$fileContent = file_get_contents($resolvedEditFilePath);

// Save changes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['file_content'])) {
    $newContent = $_POST['file_content'];
    file_put_contents($resolvedEditFilePath, $newContent);
    echo "<script>alert('File saved successfully!'); window.location.href='xrpclx.php?dir=" . urlencode($currentDir) . "';</script>";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit File</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        textarea {
            width: 100%;
            height: 400px;
            padding: 10px;
            font-family: monospace;
        }
        input[type="submit"] {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<h2>Edit File: <?= htmlspecialchars($editFileName) ?></h2>

<form method="POST">
    <textarea name="file_content"><?= htmlspecialchars($fileContent) ?></textarea>
    <br>
    <input type="submit" value="Save Changes">
</form>

</body>
</html>
