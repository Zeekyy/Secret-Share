<?php
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    $key_filename = "decryption_keys/{$token}_key.txt";
    
    if (file_exists($key_filename)) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($key_filename) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($key_filename));
        readfile($key_filename);

    } else {
        echo "Key file does not exist.";
    }
} else {
    echo "Token not provided.";
}
?>
