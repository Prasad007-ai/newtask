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

// Ensure that the directory and file path are safe
$currentDir = rtrim($currentDir, '/') . '/'; // Remove trailing slashes
$editFilePath = $currentDir . $editFileName;

// Debugging: Print current directory and file name (this is only for debugging purposes, remove for production)
echo "Current Directory: " . htmlspecialchars($currentDir) . "<br>";
echo "File to Edit: " . htmlspecialchars($editFileName) . "<br>";

// Sanitize and validate the file access to prevent directory traversal
if (strpos(realpath($editFilePath), realpath($rootDir)) !== 0 || !is_file($editFilePath)) {
    die("Error: Invalid file path. Please ensure you are editing a valid file.");
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
    // Sanitize the content to prevent code injection
    $newContent = htmlspecialchars($_POST['file_content'], ENT_QUOTES, 'UTF-8');

    if (file_put_contents($editFilePath, $newContent) !== false) {
        echo "<script>alert('File saved successfully!'); window.location.href='xrpclx.php?dir=" . urlencode($currentDir) . "';</script>";
    } else {
        echo "<script>alert('Error saving the file. Please try again.');</script>";
    }
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
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }
        h2 {
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            height: 400px;
            padding: 10px;
            font-family: monospace;
            border: 1px solid #ccc;
            border-radius: 5px;
            resize: vertical;
        }
        input[type="submit"] {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            margin-top: 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>

<h2>Edit File: <?= htmlspecialchars($editFileName) ?></h2>

<!-- Form to edit file content -->
<form method="POST">
    <textarea name="file_content"><?= htmlspecialchars($fileContent) ?></textarea>
    <br>
    <input type="submit" value="Save Changes">
</form>

</body>
</html>
