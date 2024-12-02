<?php
session_start();

// Define root directory for file management
$rootDir = 'uploads/';
define('SECRET_KEY', 'your_secret_key_123'); // Make sure this is the same key you used for encryption

// Check if the user is authenticated
if (!isset($_SESSION['authenticated'])) {
    header('Location: xrpclx.php'); // Redirect to login page if not authenticated
    exit();
}

// Ensure root directory exists
if (!file_exists($rootDir)) {
    mkdir($rootDir, 0777, true); // Create root directory if it doesn't exist
}

// Get the file to edit from the URL parameter
$fileToEdit = isset($_GET['file']) ? basename($_GET['file']) : null;
$filePath = $rootDir . $fileToEdit;

if ($fileToEdit && file_exists($filePath)) {
    // Attempt to read the file content
    $fileContent = @file_get_contents($filePath); // Using @ to suppress errors

    if ($fileContent === false) {
        $message = '<div class="alert alert-danger">Failed to open the file. Please check the file permissions.</div>';
    } else {
        // Handle form submission for saving the file
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Get the updated content from the form
            $updatedContent = $_POST['content'];

            // Attempt to save the updated content to the file
            if (file_put_contents($filePath, $updatedContent) !== false) {
                $message = '<div class="alert alert-success">File updated successfully!</div>';
            } else {
                $message = '<div class="alert alert-danger">Failed to update the file. Please check the file permissions.</div>';
            }
        }
    }
} else {
    // Redirect if the file doesn't exist or invalid file parameter
    $message = '<div class="alert alert-danger">File not found or invalid file name.</div>';
    echo $message;
    exit();
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
        <div class="d-flex justify-content-between align-items-center">
            <h1>Edit File: <?= htmlspecialchars($fileToEdit) ?></h1>
            <a href="xrpclx.php" class="btn btn-secondary">Back to File Manager</a>
        </div>

        <!-- Display message if any -->
        <?php if (isset($message)) echo $message; ?>

        <!-- File Edit Form -->
        <div class="card my-3">
            <div class="card-header bg-primary text-white">Edit File</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <label for="content" class="form-label">File Content</label>
                        <textarea name="content" id="content" class="form-control" rows="10" required><?= htmlspecialchars($fileContent) ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-success">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>