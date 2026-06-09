<?php
// File: api/clinic/vet_approve_appt.php
session_start();
header("Content-Type: application/json");

$paths = ['../../config/db_connection.php', '../../db_connection.php'];
foreach ($paths as $path) {
    if (file_exists($path)) { require_once $path; break; }
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? null;

    if ($id && isset($conn)) {
        // Update status to 'Confirmed'
        $stmt = $conn->prepare("UPDATE appointments SET Status = 'Confirmed' WHERE Appointment_ID = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $actorId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;
            writeAuditLog($conn, $actorId, "Approve Appointment", "appointments", (int)$id);
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Invalid ID or Connection"]);
    }
}
?>
