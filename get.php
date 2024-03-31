<?php
$host = 'db';
$dbname = 'DB name';
$user = 'DB username';
$pass = 'DB user password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['token'])) {
        $token = $_GET['token'];

        $stmt = $pdo->prepare("SELECT secret, iv FROM secrets WHERE token = :token AND expire_at > NOW()");
        $stmt->execute([':token' => $token]);
        $secretData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($secretData && isset($_GET['decryption_key'])) {
            $decryption_key = $_GET['decryption_key'];
            $iv = base64_decode($secretData['iv']);

            $decryptedSecret = openssl_decrypt($secretData['secret'], 'aes-256-cbc', hex2bin($decryption_key), 0, $iv);

            if ($decryptedSecret !== false) {
                echo "Your secret: " . htmlspecialchars($decryptedSecret);

                $key_filename = "decryption_keys/{$token}_key.txt";
                if (file_exists($key_filename)) {
                    unlink($key_filename);
                }

                $stmt = $pdo->prepare("DELETE FROM secrets WHERE token = :token");
                $stmt->execute([':token' => $token]);
            } else {
                echo "Incorrect decryption key or secret has been tampered with.";
            }
        } elseif ($secretData) {
            echo "<form method='GET'>
                    <input type='hidden' name='token' value='" . htmlspecialchars($token) . "'>
                    <label for='decryption_key'>Enter decryption key:</label>
                    <input type='text' id='decryption_key' name='decryption_key'>
                    <input type='submit' value='Decrypt'>
                  </form>";
        } else {
            echo "Secret not found or has expired.";
        }
    } else {
        echo "Token not provided.";
    }
} catch (PDOException $e) {
    die("Database connection error: " . $e->getMessage());
}
?>
