<?php
// File: api/records/update_owner.php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

function writeAuditLog($conn, $userId, $action, $tableAffected, $recordId = 0) {
    if (!$userId) return;

    $stmt = $conn->prepare("INSERT INTO audit_logs (User_ID, Action, Table_Affected, Record_ID) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issi", $userId, $action, $tableAffected, $recordId);
        $stmt->execute();
        $stmt->close();
    }
}

$data = json_decode(file_get_contents("php://input"), true);
$actorId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if (empty($data['owner_id']) || empty($data['fname']) || empty($data['lname'])) {
    echo json_encode(["status" => "error", "message" => "Name fields are required"]);
    exit();
}

// Update Owner Table
$stmt = $conn->prepare("UPDATE owners SET First_name = ?, Last_name = ?, Contact_Number = ?, Address = ? WHERE Owner_ID = ?");
$stmt->bind_param("ssssi", $data['fname'], $data['lname'], $data['phone'], $data['address'], $data['owner_id']);

if ($stmt->execute()) {
    writeAuditLog($conn, $actorId, "Update Owner Record", "owners", (int)$data['owner_id']);
    echo json_encode(["status" => "success", "message" => "Owner details updated."]);
} else {
    echo json_encode(["status" => "error", "message" => "Update failed."]);
}
?>
