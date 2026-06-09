<?php
// File: api/auth/session_status.php
session_start();
header("Content-Type: application/json");

$timeoutSeconds = 3 * 60;
$shouldTouch = ($_GET['touch'] ?? '1') !== '0';

if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeoutSeconds) {
        session_unset();
        session_destroy();
        echo json_encode(["status" => "timeout", "message" => "Session expired due to inactivity."]);
        exit();
    }

    if ($shouldTouch) {
        $_SESSION['last_activity'] = time();
    }

    echo json_encode([
        "status" => "logged_in",
        "user_id" => $_SESSION['user_id'],
        "role_id" => $_SESSION['role_id'],
        "username" => $_SESSION['username'],
        "timeout_minutes" => (int)($timeoutSeconds / 60)
    ]);
} else {
    echo json_encode(["status" => "logged_out"]);
}
?>
