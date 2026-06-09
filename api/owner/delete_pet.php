<?php
session_start();
require_once('../../config/db_connection.php');
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

function writeAuditLog($conn, $userId, $action, $tableAffected, $recordId = 0) {
    if (!$userId) return;

    $stmt = $conn->prepare("INSERT INTO audit_logs (User_ID, Action, Table_Affected, Record_ID) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issi", $userId, $action, $tableAffected, $recordId);
        $stmt->execute();
        $stmt->close();
    }
}

if (isset($_GET['id'])) {
    $pet_id = (int)$_GET['id'];
    $user_id = (int)$_SESSION['user_id'];
    $role_id = isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : 0;

    if ($role_id === 1 || $role_id === 4) {
        $check_stmt = $conn->prepare("SELECT Pet_ID FROM pets WHERE Pet_ID = ? AND Status != 'archived'");
        $check_stmt->bind_param("i", $pet_id);
    } else {
        $check_stmt = $conn->prepare("
            SELECT p.Pet_ID
            FROM pets p
            JOIN owners o ON p.Owner_ID = o.Owner_ID
            WHERE p.Pet_ID = ? AND o.User_ID = ? AND p.Status != 'archived'
        ");
        $check_stmt->bind_param("ii", $pet_id, $user_id);
    }

    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Pet not found or already archived."]);
        exit();
    }

    // Soft delete by setting status to 'archived'
    $stmt = $conn->prepare("UPDATE pets SET Status = 'archived' WHERE Pet_ID = ?");
    $stmt->bind_param("i", $pet_id);

    if ($stmt->execute()) {
        $actorId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
        writeAuditLog($conn, $actorId, "Archive Pet Record", "pets", (int)$pet_id);
        echo json_encode(["status" => "success", "message" => "Pet has been archived."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No pet ID provided"]);
}
?>
