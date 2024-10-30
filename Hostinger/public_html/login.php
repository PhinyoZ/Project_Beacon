<?php
session_start();

$CLIENT_ID = '2005980091';
$REDIRECT_URL = 'https://mediumaquamarine-porcupine-998187.hostingersite.com/callback.php';
$AUTH_URL = 'https://access.line.me/oauth2/v2.1/authorize';

$_SESSION['state'] = bin2hex(random_bytes(8)); // สร้าง state สำหรับการตรวจสอบ
$_SESSION['nonce'] = bin2hex(random_bytes(8)); // สร้าง nonce สำหรับการตรวจสอบ

$login_url = $AUTH_URL . "?response_type=code&client_id=" . $CLIENT_ID . "&redirect_uri=" . urlencode($REDIRECT_URL) . "&state=" . $_SESSION['state'] . "&scope=profile%20openid%20email&nonce=" . $_SESSION['nonce'];

header("Location: " . $login_url);
exit();
?>
