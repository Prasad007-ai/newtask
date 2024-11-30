<?php
session_start();

// Define root directory for file management
$rootDir = 'uploads/'; // Root directory for file manager
define('SECRET_KEY', 'test'); // Replace with a strong, random key

// The password should be encrypted and stored securely
define('ENCRYPTED_PASSWORD', openssl_encrypt('test', 'AES-128-ECB', SECRET_KEY)); // Replace with your encrypted password

// Function to encrypt a password
function encryptPassword($password)
{
    return openssl_encrypt($password, 'AES-128-ECB', SECRET_KEY);
}

// Function to decrypt a password (not necessary for login, but could be used for password recovery)
function decryptPassword($encryptedPassword)
{
    return openssl_decrypt($encryptedPassword, 'AES-128-ECB', SECRET_KEY);
}

// Check if the user is authenticated
if (!isset($_SESSION['authenticated'])) {
    header('Location: xrpclx.php'); // Redirect to login page
    exit();
}

// Ensure root directory exists
if (!file_exists($rootDir)) {
    mkdir($rootDir, 0777, true);
}

// Initialize the filesInFolder variable
$filesInFolder = [];

// Handle file uploads for specific folders
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $folder = $_POST['folder'];
    $fileName = basename($_FILES['file']['name']);
    $filePath = $folder . DIRECTORY_SEPARATOR . preg_replace("/[^a-zA-Z0-9\._-]/", "", $fileName);

    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        $message = '<div id="uploadSuccess" class="alert alert-success">File uploaded successfully!</div>';
    } else {
        $message = '<div id="uploadError" class="alert alert-danger">Failed to upload file.</div>';
    }
}

// Check if folder exists and is empty
if (isset($_GET['edit'])) {
    $folderToEdit = $rootDir . basename($_GET['edit']);

    if (is_dir($folderToEdit)) {
        $filesInFolder = array_diff(scandir($folderToEdit), array('.', '..'));
        $isEmpty = count($filesInFolder) === 0;
    } else {
        $isEmpty = false;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Folder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <h1>Edit Folder: <?= htmlspecialchars(basename($folderToEdit)) ?></h1>

        <?php if ($isEmpty): ?>
            <div class="alert alert-info">
                This folder is empty. You can upload files to it.
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="folder" value="<?= htmlspecialchars($folderToEdit) ?>">
                <div class="mb-3">
                    <input type="file" name="file" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Upload File</button>
            </form>
        <?php else: ?>
            <p>The folder contains the following files. You can edit or download them:</p>
            <ul class="list-group">
                <?php foreach ($filesInFolder as $file): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <?= htmlspecialchars($file) ?>
                        <span class="btn-group">
                            <!-- Download Link -->
                            <a href="<?= $folderToEdit . DIRECTORY_SEPARATOR . urlencode($file) ?>" class="btn btn-sm btn-info" download>Download</a>
                            <!-- Edit Button with correct path -->
                            <a href="edit_file.php?dir=<?= urlencode($folderToEdit) ?>&edit=<?= urlencode($file) ?>" class="btn btn-sm btn-primary">Edit</a>

                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <br>
        <a href="xrpclx.php" class="btn btn-primary">Back to File Manager</a>
    </div>
</body>

</html>