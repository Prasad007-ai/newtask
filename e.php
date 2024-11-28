<?php
session_start();

// Check if user is authenticated
if (!isset($_SESSION['authenticated'])) {
    header('Location: xrpclx.php'); // Redirect to login page if not authenticated
    exit();
}

// Define the root directory for file management
$rootDir = './uploads';  // Set the root directory where your files are stored

// Get the directory and file to edit
$currentDir = isset($_GET['dir']) ? $_GET['dir'] : $rootDir;
$editFileName = isset($_GET['edit']) ? $_GET['edit'] : '';

// Ensure the directory is not empty or invalid
$currentDir = rtrim($currentDir, '/') . '/';
$editFilePath = $currentDir . $editFileName;

// Sanitize the file access and prevent directory traversal
// Make sure the file is within the root directory and is a valid file
if (strpos(realpath($editFilePath), realpath($rootDir)) !== 0 || !is_file($editFilePath)) {
    die("Error: Invalid file path.");
}

$fileContent = '';
if (file_exists($editFilePath) && is_file($editFilePath)) {
    $fileContent = file_get_contents($editFilePath);
}

// Save the content when the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['file_content'])) {
    $newContent = $_POST['file_content'];
    file_put_contents($editFilePath, $newContent);
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
