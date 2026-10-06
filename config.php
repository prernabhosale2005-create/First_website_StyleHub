<?php
// Style Hub - MySQL connection and persistent login bootstrap.
// Keep your existing XAMPP credentials here.
$host = "localhost";
$user = "root";
$password = "";
$dbname = "style_hub";

// Keep the PHP session available across browser refreshes/restarts.
ini_set('session.gc_maxlifetime', '2592000'); // 30 days
session_set_cookie_params([
    'lifetime' => 2592000,
    'path' => '/',
    'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
    'httponly' => true,
    'samesite' => 'Lax'
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Restore a remembered login when the PHP session is new.
if (!isset($_SESSION['user_id']) && !empty($_COOKIE['style_hub_remember'])) {
    $rawToken = $_COOKIE['style_hub_remember'];
    $tokenHash = hash('sha256', $rawToken);

    $stmt = $conn->prepare(
        "SELECT u.id, u.name, u.email
         FROM remember_tokens rt
         INNER JOIN users u ON u.id = rt.user_id
         WHERE rt.token_hash = ? AND rt.expires_at > NOW()
         LIMIT 1"
    );
    if ($stmt) {
        $stmt->bind_param('s', $tokenHash);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($userRow = $result->fetch_assoc()) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$userRow['id'];
            $_SESSION['user_name'] = $userRow['name'];
            $_SESSION['user_email'] = $userRow['email'];
        } else {
            setcookie('style_hub_remember', '', [
                'expires' => time() - 3600, 'path' => '/',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true, 'samesite' => 'Lax'
            ]);
        }
        $stmt->close();
    }
}

// Helper used by protected pages.
function require_login(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: home.php?login=required');
        exit;
    }
}

function create_remember_token(mysqli $conn, int $userId): void {
    $rawToken = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $rawToken);
    $expires = date('Y-m-d H:i:s', time() + 60 * 60 * 24 * 30);

    $stmt = $conn->prepare("DELETE FROM remember_tokens WHERE user_id = ? OR expires_at <= NOW()");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("INSERT INTO remember_tokens (user_id, token_hash, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param('iss', $userId, $tokenHash, $expires);
    $stmt->execute();
    $stmt->close();

    setcookie('style_hub_remember', $rawToken, [
        'expires' => time() + 60 * 60 * 24 * 30,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}
?>
