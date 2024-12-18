<?php
session_start();

// Define root directory for file management
$rootDir = 'uploads/';
define('SECRET_KEY', 'your_secret_key_123'); // Make sure this is the same key you used for encryption
define('ENCRYPTED_PASSWORD', 'MV4o/rFFlHsWMLjRi8y+cXDIDM4tD+ur+nwmtBrGIcE='); // The encrypted password you generated

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
        // Encrypt the input password before comparing 
        $inputPassword = encryptPassword($_POST['password']);

        // Check if the encrypted password matches the stored encrypted password
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

// Handle file uploads to the root directory
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $fileName = basename($_FILES['file']['name']);
    $filePath = $rootDir . preg_replace("/[^a-zA-Z0-9\._-]/", "", $fileName);

    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        $message = '<div id="uploadSuccess" class="alert alert-success">File uploaded successfully!</div>';
    } else {
        $message = '<div id="uploadError" class="alert alert-danger">Failed to upload file.</div>';
    }
}

// Handle folder creation
if (isset($_POST['create_folder']) && isset($_POST['folder_name'])) {
    $newFolder = $rootDir . preg_replace("/[^a-zA-Z0-9\._-]/", "", $_POST['folder_name']);
    if (!file_exists($newFolder)) {
        mkdir($newFolder, 0777, true);
        $message = '<div id="folderSuccess" class="alert alert-success">Folder created successfully!</div>';
    } else {
        $message = '<div id="folderError" class="alert alert-danger">Folder already exists.</div>';
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

// Handle file uploads to a specific folder
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_in_folder']) && isset($_POST['folder'])) {
    $folderName = preg_replace("/[^a-zA-Z0-9\._-]/", "", $_POST['folder']); // Sanitize folder name
    $folderPath = $rootDir . $folderName . DIRECTORY_SEPARATOR;

    // Ensure folder exists
    if (!file_exists($folderPath)) {
        mkdir($folderPath, 0777, true);
    }

    // Sanitize the file name
    $fileName = basename($_FILES['file_in_folder']['name']);
    $filePath = $folderPath . preg_replace("/[^a-zA-Z0-9\._-]/", "", $fileName);

    // Move the uploaded file to the folder
    if (move_uploaded_file($_FILES['file_in_folder']['tmp_name'], $filePath)) {
        $message = '<div id="uploadSuccess" class="alert alert-success">File uploaded successfully!</div>';
    } else {
        $message = '<div id="uploadError" class="alert alert-danger">Failed to upload file.</div>';
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
    <script>
        // Function to hide the alert messages after 3 seconds
        function hideAlertMessage(id) {
            setTimeout(function() {
                var element = document.getElementById(id);
                if (element) {
                    element.style.display = 'none';
                }
            }, 3000); // 3 seconds
        }

        // Hide the messages after 3 seconds
        <?php if (isset($message)) { ?>
            <?php if (strpos($message, 'uploadSuccess') !== false) { ?>
                hideAlertMessage('uploadSuccess');
            <?php } elseif (strpos($message, 'uploadError') !== false) { ?>
                hideAlertMessage('uploadError');
            <?php } elseif (strpos($message, 'folderSuccess') !== false) { ?>
                hideAlertMessage('folderSuccess');
            <?php } elseif (strpos($message, 'folderError') !== false) { ?>
                hideAlertMessage('folderError');
            <?php } ?>
        <?php } ?>
    </script>
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
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
            </div>
        </div>

        <!-- Folder Creation Form -->
        <div class="card my-3">
            <div class="card-header bg-success text-white">Create a Folder</div>
            <div class="card-body">
                <form method="POST">
                    <div class="mb-3">
                        <input type="text" name="folder_name" class="form-control" required placeholder="Enter folder name">
                    </div>
                    <button type="submit" name="create_folder" class="btn btn-success">Create Folder</button>
                </form>
            </div>
        </div>

        <!-- Display Upload/Folder Messages -->
        <?php if (isset($message)) {
            echo $message;
        } ?>

        <!-- Files and Folders Table -->
        <div class="card">
            <div class="card-header">Files and Folders</div>
            <div class="card-body">
                <table class="table table-bordered">
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
                                    <td>
                                        <?php
                                        $filePath = $rootDir . $item;
                                        if (is_file($filePath)) {
                                            echo '<a href="' . htmlspecialchars($filePath) . '" target="_blank" download>' . htmlspecialchars($item) . '</a>';
                                        } else {
                                            echo htmlspecialchars($item);
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <!-- Delete Button -->
                                        <a href="?delete=<?= urlencode($item) ?>" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this?')">Delete</a>

                                        <!-- Edit Button for Folder -->
                                        <?php if (is_dir($filePath)): ?>
                                            <a href="edit_folder.php?folder=<?= urlencode($item) ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <?php else: ?>
                                            <!-- Edit Button for File -->
                                            <a href="edit_file.php?file=<?= urlencode($item) ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <?php endif; ?>

                                        <!-- Upload Form for Folders if Empty -->
                                        <?php if (is_dir($filePath)): ?>
                                            <?php
                                            $folderContents = array_diff(scandir($filePath), ['.', '..']);
                                            if (empty($folderContents)):
                                            ?>
                                                <form method="POST" enctype="multipart/form-data" style="display:inline-block;">
                                                    <input type="hidden" name="folder" value="<?= htmlspecialchars($item) ?>">
                                                    <input type="file" name="file_in_folder" required>
                                                    <button type="submit" class="btn btn-sm btn-warning">Upload</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</body>

</html>