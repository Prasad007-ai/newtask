<?php
session_start();

// Define root directory for file management
$rootDir = 'uploads/';
$folder = isset($_GET['folder']) ? $_GET['folder'] : '';

if (empty($folder)) {
    echo "No folder specified.";
    exit();
}

$folderPath = $rootDir . $folder;

// Ensure the folder exists
if (!is_dir($folderPath)) {
    echo "Folder does not exist.";
    exit();
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileName = basename($_FILES['file']['name']);
    $filePath = $folderPath . DIRECTORY_SEPARATOR . preg_replace("/[^a-zA-Z0-9\._-]/", "", $fileName);

    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        $message = '<div class="alert alert-success">File uploaded successfully!</div>';
    } else {
        $message = '<div class="alert alert-danger">Failed to upload file.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload to Folder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <h1>Upload File to Folder: <?= htmlspecialchars($folder) ?></h1>

        <?php if (isset($message)) { echo $message; } ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <input type="file" name="file" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success">Upload</button>
        </form>

        <a href="xrpclx.php" class="btn btn-primary mt-3">Back to File Manager</a>
    </div>
</body>
</html>
