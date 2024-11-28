<?php
session_start();

$rootDir = 'uploads/'; // Root directory for file manager
define('SECRET_KEY', 'your-secure-secret-key'); // Replace with a strong, random key

// The password should be encrypted and stored securely
define('ENCRYPTED_PASSWORD', openssl_encrypt('your_secure_password', 'AES-128-ECB', SECRET_KEY)); // Replace with your encrypted password

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
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        // Encrypt the input password
        $inputPassword = encryptPassword($_POST['password']);

        // Check if the input password matches the stored encrypted password
        if ($inputPassword === ENCRYPTED_PASSWORD) {
            $_SESSION['authenticated'] = true;
            header('Location: xrpclx.php'); // Redirect to the main file manager
            exit();
        } else {
            $error = "Incorrect password. Please try again.";
        }
    }

    // Display login form if not authenticated
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login - File Manager</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container d-flex justify-content-center align-items-center" style="height: 100vh;">
            <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
                <h2 class="text-center">Login</h2>
                ' . (isset($error) ? '<div class="alert alert-danger">' . $error . '</div>' : '') . '
                <form method="POST">
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </body>
    </html>';
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: xrpclx.php'); // Redirect to login page
    exit();
}

// Ensure root directory exists
if (!file_exists($rootDir)) {
    mkdir($rootDir, 0777, true);
}

// Handle file uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileName = basename($_FILES['file']['name']);
    $filePath = $rootDir . preg_replace("/[^a-zA-Z0-9\._-]/", "", $fileName);

    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        echo '<div class="alert alert-success">File uploaded successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Failed to upload file.</div>';
    }
}

// Handle folder creation
if (isset($_POST['create_folder']) && isset($_POST['folder_name'])) {
    $newFolder = $rootDir . preg_replace("/[^a-zA-Z0-9\._-]/", "", $_POST['folder_name']);
    if (!file_exists($newFolder)) {
        mkdir($newFolder, 0777, true);
        echo '<div class="alert alert-success">Folder created successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Folder already exists.</div>';
    }
}

// Get all files and directories
$filesAndDirs = scandir($rootDir);

// Delete function to handle file/folder deletion
function deleteFolder($folderPath)
{
    foreach (scandir($folderPath) as $item) {
        if ($item !== '.' && $item !== '..') {
            $itemPath = $folderPath . DIRECTORY_SEPARATOR . $item;
            if (is_dir($itemPath)) {
                deleteFolder($itemPath); // Recursively delete contents of the directory
            } else {
                unlink($itemPath); // Delete the file
            }
        }
    }
    rmdir($folderPath); // Delete the folder after its contents are deleted
}

if (isset($_GET['delete'])) {
    $itemToDelete = $rootDir . basename($_GET['delete']); // Sanitize the path

    if (realpath($itemToDelete) !== false && strpos(realpath($itemToDelete), realpath($rootDir)) === 0) {
        if (is_file($itemToDelete)) {
            if (unlink($itemToDelete)) {
                echo "<script>alert('File deleted successfully!'); window.location.href='xrpclx.php';</script>";
            } else {
                echo "<script>alert('Failed to delete file.'); window.location.href='xrpclx.php';</script>";
            }
        } elseif (is_dir($itemToDelete)) {
            deleteFolder($itemToDelete); // Recursively delete contents and then the folder itself
            echo "<script>alert('Folder deleted successfully!'); window.location.href='xrpclx.php';</script>";
        } else {
            echo "<script>alert('Item not found.'); window.location.href='xrpclx.php';</script>";
        }
    } else {
        echo "<script>alert('Invalid path.'); window.location.href='xrpclx.php';</script>";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center">
            <h1>File Manager</h1>
            <a href="?logout=true" class="btn btn-danger">Logout</a>
        </div>

        <!-- File Upload Form -->
        <div class="card my-3">
            <div class="card-header bg-primary text-white">Upload a File</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <input type="file" name="file" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success">Upload</button>
                </form>
            </div>
        </div>

        <!-- Create Folder Form -->
        <div class="card my-3">
            <div class="card-header bg-primary text-white">Create a New Folder</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <input type="text" name="folder_name" class="form-control" placeholder="Enter folder name" required>
                    </div>
                    <button type="submit" name="create_folder" class="btn btn-warning">Create Folder</button>
                </form>
            </div>
        </div>

        <!-- File and Folder List -->
        <div class="card">
            <div class="card-header bg-dark text-white">Files and Folders</div>
            <div class="card-body">
                <?php if (count($filesAndDirs) > 2): ?>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filesAndDirs as $item): ?>
                                <?php if ($item !== '.' && $item !== '..'): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($item) ?></td>
                                        <td>
                                            <!-- Delete Button -->
                                            <a href="?delete=<?= urlencode($item) ?>" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Are you sure you want to delete this?')">Delete</a>
                                            <!-- Edit Button with correct path -->
                                            <a href="e.php?dir=<?= urlencode($rootDir) ?>&edit=<?= urlencode($item) ?>" class="btn btn-sm btn-primary">Edit</a>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No files or folders found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>