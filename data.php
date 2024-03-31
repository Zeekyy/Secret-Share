<?php
$host = 'db';
$dbname = 'enter db name';
$user = 'enter db username';
$pass = 'enter username password for db';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['secret'])) {
        $secret = $_POST['secret'];
        $token = bin2hex(random_bytes(32));
        $expire_at = date('Y-m-d H:i:s', strtotime('+1 day'));

        $encryption_key = bin2hex(openssl_random_pseudo_bytes(32));
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encryptedSecret = openssl_encrypt($secret, 'aes-256-cbc', hex2bin($encryption_key), 0, $iv);

        $stmt = $pdo->prepare("INSERT INTO secrets (secret, token, expire_at, iv) VALUES (:secret, :token, :expire_at, :iv)");
        $stmt->execute([':secret' => $encryptedSecret, ':token' => $token, ':expire_at' => $expire_at, ':iv' => base64_encode($iv)]);

        $key_filename = "decryption_keys/{$token}_key.txt";
        file_put_contents($key_filename, $encryption_key);

        $response = [
            "link" => "https://websitelink/get.php?token=$token",
            "decryption_key_link" => "https://websitelink/download_key.php?token=$token"
        ];
        echo json_encode($response);
    }
} catch (PDOException $e) {
    echo json_encode(["error" => "Unable to connect to database: " . $e->getMessage()]);
}
?>
