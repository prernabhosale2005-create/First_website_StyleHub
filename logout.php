<?php
require_once "config.php";
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ?");
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
}
setcookie('style_hub_remember', '', [
    'expires' => time() - 3600, 'path' => '/',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true, 'samesite' => 'Lax'
]);
session_unset();
session_destroy();
header("Location: home.php");
exit;
?>
