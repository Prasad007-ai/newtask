<?php
session_start();

// Set the root directory for file manager (uploads folder)
$rootDir = __DIR__.'/uploads/'; // Absolute path

define('SECRET_KEY', 'your-secret-key'); // Set your AES key
define('ENCRYPTED_PASSWORD', openssl_encrypt('test', 'AES-128-ECB', SECRET_KEY)); // Encrypted password
// Check if the uploads folder exists
if ($rootDir === false || !file_exists($rootDir)) {
    die("The 'uploads' folder does not exist or the path is invalid.");
}

// Ensure the uploads folder exists
if (!file_exists($rootDir)) {
    if (!mkdir($rootDir, 0777, true)) {
        die("Failed to create the uploads directory.");
    }
}



// Check if user is authenticated
if (!isset($_SESSION['authenticated'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        $inputPassword = openssl_encrypt($_POST['password'], 'AES-128-ECB', SECRET_KEY);
        if ($inputPassword === ENCRYPTED_PASSWORD) {
            $_SESSION['authenticated'] = true;
            header('Location: xrpclx.php');
            exit();
        } else {
            $error = "Incorrect password. Please try again.";
        }
    }

    // Display login form if not authenticated
    echo '<h2>Login to Access File Manager</h2>';
    if (isset($error)) {
        echo "<p style='color: red;'>$error</p>";
    }
    echo '<form method="POST">
            <label for="password">Password: </label>
            <input type="password" name="password" required>
            <input type="submit" value="Login">
          </form>';
    exit();
}

// Logout functionality
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: xrpclx.php');
    exit();
}

// Handle file uploads
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $fileName = basename($_FILES['file']['name']);
    $filePath = $rootDir . DIRECTORY_SEPARATOR . $fileName;

    // Sanitize file name to allow only safe characters
    $fileName = preg_replace("/[^a-zA-Z0-9\._-]/", "", $fileName);

    // Move uploaded file to the correct directory
    if (move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
        echo "File uploaded successfully!";
    } else {
        echo "Failed to upload file.";
    }
}

// Handle folder creation
if (isset($_POST['create_folder']) && isset($_POST['folder_name'])) {
    $newFolder = $rootDir . DIRECTORY_SEPARATOR . $_POST['folder_name'];
    $newFolder = preg_replace("/[^a-zA-Z0-9\._-]/", "", $newFolder);

    if (!file_exists($newFolder)) {
        if (mkdir($newFolder, 0777, true)) {
            echo "Folder created successfully!";
        } else {
            echo "Failed to create folder.";
        }
    } else {
        echo "Folder already exists.";
    }
}

// Get all files and directories in the current directory
$filesAndDirs = scandir($rootDir);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container">

<h2 class="mt-4">File Manager</h2>

<!-- Logout button -->
<a href="?logout=true" class="btn btn-danger mt-3">Logout</a>

<!-- Upload file form -->
<h3 class="mt-4">Upload a file</h3>
<form class="form-inline" action="xrpclx.php" method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <input type="file" name="file" required class="form-control">
        <input type="submit" value="Upload" class="btn btn-primary">
    </div>
</form>

<!-- Create a new folder -->
<h3 class="mt-4">Create a new folder</h3>
<form class="form-inline" action="xrpclx.php" method="POST">
    <div class="mb-3">
        <input type="text" name="folder_name" placeholder="Folder Name" required class="form-control">
        <input type="submit" name="create_folder" value="Create Folder" class="btn btn-success">
    </div>
</form>

<!-- File/Folder list -->
<h3 class="mt-4">Files and Folders</h3>
<table class="table table-bordered">
    <thead>
        <tr>
            <th>Filename</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($filesAndDirs as $fileOrDir) {
            if ($fileOrDir !== '.' && $fileOrDir !== '..') {
                echo "<tr>
                        <td>$fileOrDir</td>
                        <td>
                            <a href=\"?delete=$fileOrDir\" class=\"btn btn-danger btn-sm\" onclick=\"return confirm('Are you sure?')\">Delete</a>
                        </td>
                      </tr>";
            }
        }
        ?>
    </tbody>
</table>

</body>
</html>
