<?php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$pet_id = isset($_POST['pet_id']) ? (int)$_POST['pet_id'] : 0;
$note_text = trim($_POST['note_text'] ?? '');

function writeAuditLog($conn, $userId, $action, $tableAffected, $recordId = 0) {
    if (!$userId) return;

    $stmt = $conn->prepare("INSERT INTO audit_logs (User_ID, Action, Table_Affected, Record_ID) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issi", $userId, $action, $tableAffected, $recordId);
        $stmt->execute();
        $stmt->close();
    }
}

if (!$pet_id || $note_text === '') {
    echo json_encode(["status" => "error", "message" => "Pet and note text are required."]);
    exit();
}

$owner_stmt = $conn->prepare("
    SELECT p.Pet_ID
    FROM pets p
    JOIN owners o ON p.Owner_ID = o.Owner_ID
    WHERE p.Pet_ID = ? AND o.User_ID = ? AND p.Status != 'archived'
    LIMIT 1
");
$owner_stmt->bind_param("ii", $pet_id, $user_id);
$owner_stmt->execute();
$owner_result = $owner_stmt->get_result();

if ($owner_result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Pet not found or access denied."]);
    exit();
}

$stmt = $conn->prepare("INSERT INTO pet_notes (Pet_ID, Note_Text) VALUES (?, ?)");
$stmt->bind_param("is", $pet_id, $note_text);

if ($stmt->execute()) {
    $note_id = $conn->insert_id;
    writeAuditLog($conn, $user_id, "Add Pet Note", "pet_notes", $note_id);
    echo json_encode(["status" => "success", "message" => "Note added successfully."]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to add note."]);
}
?>
