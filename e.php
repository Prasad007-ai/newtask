<?php
session_start();

// Ensure the user is authenticated
if (!isset($_SESSION['authenticated'])) {
    header('Location: xrpclx.php');
    exit();
}

// Define root directory for file management
$rootDir = './uploads'; // Set the root directory where your files are stored

// Get the directory and file to edit from the URL
$currentDir = isset($_GET['dir']) ? $_GET['dir'] : $rootDir; // Default to the root directory if not specified
$editFileName = isset($_GET['edit']) ? $_GET['edit'] : '';

// Debugging: Print current directory and file name
echo "Current Directory: " . htmlspecialchars($currentDir) . "<br>";
echo "File to Edit: " . htmlspecialchars($editFileName) . "<br>";

// Ensure that the file is valid and inside the root directory
$currentDir = rtrim($currentDir, '/') . '/';
$editFilePath = $currentDir . $editFileName;

// Debugging: Print full path to the file
echo "Full File Path: " . realpath($editFilePath) . "<br>";

// Sanitize the file access to prevent directory traversal
if (strpos(realpath($editFilePath), realpath($rootDir)) !== 0 || !is_file($editFilePath)) {
    die("Error: Invalid file path.");
}

// Read the content of the file
$fileContent = '';
if (file_exists($editFilePath) && is_file($editFilePath)) {
    $fileContent = file_get_contents($editFilePath);
} else {
    die("Error: File does not exist.");
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
