<?php
// File: api/auth/logout.php
session_start();

require_once '../../config/db_connection.php';
require_once '../system/audit_helper.php';

if (isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    $presence = $conn->prepare("UPDATE users SET Is_Online = 0, Last_Activity = NULL WHERE User_ID = ?");
    $presence->bind_param("i", $userId);
    $presence->execute();
    $presence->close();
    $action = "Logout";
    $tableAffected = "sessions";
    $recordId = 0;

    auditLog($conn, $userId, $action, $tableAffected, $recordId, ['event_type' => 'security']);
}

session_destroy();

$expectsJson = isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;

if ($expectsJson) {
    header("Content-Type: application/json");
    echo json_encode(["status" => "success", "message" => "Logged out successfully."]);
} else {
    header("Location: ../../login.html");
}
exit();
?>
