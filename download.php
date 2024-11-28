<?php
session_start();

// Define root directory for file management
$rootDir = 'uploads/';
// Define the secret key for encryption/decryption
define('SECRET_KEY', 'your-secure-secret-key'); // Correct constant name

// Function to decrypt content with AES-128-ECB
function decryptContent($encryptedContent)
{
    return openssl_decrypt($encryptedContent, 'AES-128-ECB', SECRET_KEY); // Use correct constant
}

// Check if the user has clicked to download a file
if (isset($_GET['file'])) {
    $file = $rootDir . basename($_GET['file']);
    
    if (file_exists($file)) {
        // Debugging: Check if file exists
        echo "File exists: " . htmlspecialchars($file) . "<br>";

        // Prompt for password
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
            $inputPassword = $_POST['password'];

            // Debugging: Print entered password
            echo "Entered Password: " . htmlspecialchars($inputPassword) . "<br>";

            // Check if password matches SECRET_KEY
            if ($inputPassword === SECRET_KEY) { // Match against the correct password (SECRET_KEY)
                $fileContent = file_get_contents($file);
                $decryptedContent = decryptContent($fileContent);

                // Debugging: Check if decryption is successful
                if ($decryptedContent === false) {
                    echo "Decryption failed!<br>";
                } else {
                    echo "Decryption succeeded!<br>";

                    // Send the decrypted content as a download
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
                    echo $decryptedContent;
                    exit();
                }
            } else {
                echo "Incorrect password!<br>";
            }
        }

        // Password form
        echo '<form method="POST">
                <label for="password">Enter Password to Download:</label>
                <input type="password" name="password" required>
                <button type="submit">Download</button>
              </form>';
    } else {
        echo "File not found.<br>";
    }
} else {
    echo "No file specified.<br>";
}
?>
