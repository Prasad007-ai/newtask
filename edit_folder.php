<?php
session_start();

// Define root directory for file management
$rootDir = 'uploads/';
define('SECRET_KEY', 'your_secret_key_123'); // Ensure this matches your encryption key
define('ENCRYPTED_PASSWORD', 'MV4o/rFFlHsWMLjRi8y+cXDIDM4tD+ur+nwmtBrGIcE='); // The encrypted password you generated

// Function to encrypt a password
function encryptPassword($password)
{
    return openssl_encrypt($password, 'AES-128-ECB', SECRET_KEY);
}

// Function to decrypt a password (could be used for password recovery)
function decryptPassword($encryptedPassword)
{
    return openssl_decrypt($encryptedPassword, 'AES-128-ECB', SECRET_KEY);
}

// Check if the user is authenticated
if (!isset($_SESSION['authenticated'])) {
    header('Location: xrpclx.php'); // Redirect to login page if not authenticated
    exit();
}

// Initialize folderPath to null
$folderPath = null;
$folderName = null;

// Ensure the folder parameter is present in the URL
if (isset($_GET['folder'])) {
    $folderName = basename($_GET['folder']); // Sanitize folder name
    $folderPath = $rootDir . $folderName;

    // Ensure folder exists
    if (!is_dir($folderPath)) {
        echo "Folder not found.";
        exit();
    }
} else {
    echo "No folder specified.";
    exit();
}

// Handle file uploads to the folder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileName = basename($_FILES['file']['name']);
    $filePath = $folderPath . DIRECTORY_SEPARATOR . preg_replace("/[^a-zA-Z0-9\._-]/", "", $fileName);

    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        echo '<div class="alert alert-success">File uploaded successfully!</div>';
        header("Refresh: 2"); // Refresh the page after upload
    } else {
        echo '<div class="alert alert-danger">Failed to upload file.</div>';
    }
}

// Handle file deletion
if (isset($_GET['delete'])) {
    $fileToDelete = $folderPath . DIRECTORY_SEPARATOR . basename($_GET['delete']);

    if (file_exists($fileToDelete)) {
        unlink($fileToDelete);
        echo '<div class="alert alert-success">File deleted successfully!</div>';
        header("Refresh: 2"); // Refresh the page after deletion
    } else {
        echo '<div class="alert alert-danger">File not found.</div>';
    }
}

// Handle file download
if (isset($_GET['download'])) {
    $fileToDownload = $folderPath . DIRECTORY_SEPARATOR . basename($_GET['download']);  // Ensure folderPath is correctly set

    if (file_exists($fileToDownload)) {
        // Force download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($fileToDownload) . '"');
        readfile($fileToDownload);
        exit();
    } else {
        echo '<div class="alert alert-danger">File not found.</div>';
    }
}

// Ensure $folderPath is set before calling scandir()
if ($folderPath) {
    // Get all files and directories in the folder
    $filesAndDirs = scandir($folderPath);
} else {
    echo "No folder found to display files.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Folder - File Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <h2>Edit Folder: <?php echo htmlspecialchars($folderName); ?></h2>

    <!-- File upload form -->
    <form method="POST" enctype="multipart/form-data" class="mb-4">
        <div class="input-group">
            <input type="file" name="file" class="form-control" required>
            <button type="submit" class="btn btn-primary">Upload File</button>
        </div>
    </form>

    <!-- List all files and allow downloading, deleting, and editing -->
    <div class="list-group">
        <?php
        foreach ($filesAndDirs as $item) {
            if ($item != '.' && $item != '..') {
                $filePath = $folderPath . DIRECTORY_SEPARATOR . $item;
                $downloadUrl = 'edit_folder.php?folder=' . urlencode($folderName) . '&download=' . urlencode($item);
                $editUrl = 'edit_file.php?file=' . urlencode($filePath); // URL for editing the file
                echo '<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <a href="' . $downloadUrl . '">' . $item . '</a>
                    <a href="' . $editUrl . '" class="btn btn-warning btn-sm">Edit</a>
                </div>';
            }
        }
        ?>
    </div>

</div>

</body>
</html>