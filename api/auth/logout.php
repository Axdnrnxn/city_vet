<?php
// File: api/auth/logout.php
session_start();

require_once '../../config/db_connection.php';

if (isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    $action = "Logout";
    $tableAffected = "sessions";
    $recordId = 0;

    $stmt = $conn->prepare("INSERT INTO audit_logs (User_ID, Action, Table_Affected, Record_ID) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issi", $userId, $action, $tableAffected, $recordId);
        $stmt->execute();
        $stmt->close();
    }
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
