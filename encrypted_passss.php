<?php
// Define your secret key (must be 16 characters for AES-128)
define('SECRET_KEY', 'your_secret_key_123'); // Replace with a strong, secure key

// Take a plaintext password as input
$password = 'your_secret_key_123'; // Replace with the password you want to encrypt

// Encrypt the password using AES-128-ECB
$encryptedPassword = openssl_encrypt($password, 'AES-128-ECB', SECRET_KEY);

// Output the encrypted password
echo "Encrypted password: " . $encryptedPassword;
?>

<form method="POST">
    <input type="password" name="password" placeholder="Enter password" required>
    <button type="submit">Login</button>
</form>
