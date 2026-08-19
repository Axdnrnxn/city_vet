<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db_connection.php';
require_once '../system/audit_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id']) || (int)($_SESSION['role_id'] ?? 0) !== 1) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Administrator access required.']);
    exit();
}

$petId = (int)($_POST['pet_id'] ?? 0);
if (!$petId) {
    echo json_encode(['status' => 'error', 'message' => 'Pet ID is required.']);
    exit();
}

$stmt = $conn->prepare("UPDATE pets SET Status = 'active' WHERE Pet_ID = ? AND Status = 'archived'");
$stmt->bind_param('i', $petId);
$stmt->execute();

if ($stmt->affected_rows !== 1) {
    echo json_encode(['status' => 'error', 'message' => 'Archived pet not found.']);
    exit();
}

auditLog($conn, (int)$_SESSION['user_id'], 'Restore Archived Pet', 'pets', $petId);
echo json_encode(['status' => 'success', 'message' => 'Pet restored to active records.']);
?>
