<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db_connection.php';
require_once '../system/audit_helper.php';

if (!isset($_SESSION['user_id']) || !in_array((int)$_SESSION['role_id'], [1, 2, 4])) {
    echo json_encode(["status" => "error", "message" => "Access denied."]);
    exit();
}

$action = $_GET['action'] ?? '';

// CREATE (Add New Record)
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = $_POST['pet_id'];
    $treatment = $_POST['treatment'];
    $notes = $_POST['symptoms']; // Symptoms field from UI maps to 'Notes' in DB
    
    $sql = "INSERT INTO medical_records (Pet_ID, Treatment, Notes, Visit_Date) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $pet_id, $treatment, $notes);
    
    if ($stmt->execute()) {
        auditLog($conn, (int)$_SESSION['user_id'], 'Create Medical Record', 'medical_records', (int)$conn->insert_id, ['metadata' => ['pet_id' => (int)$pet_id]]);
        echo json_encode(["status"=>"success", "message"=>"Record saved"]);
    } else echo json_encode(["status"=>"error"]);
}

// UPDATE
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $treatment = $_POST['treatment'];
    $notes = $_POST['symptoms'];

    $sql = "UPDATE medical_records SET Treatment=?, Notes=? WHERE Record_ID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $treatment, $notes, $id);
    
    if ($stmt->execute()) {
        auditLog($conn, (int)$_SESSION['user_id'], 'Update Medical Record', 'medical_records', (int)$id);
        echo json_encode(["status"=>"success", "message"=>"Updated"]);
    } else echo json_encode(["status"=>"error"]);
}

// SOFT DELETE
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    // Assumes you added the 'is_deleted' column as discussed
    $sql = "UPDATE medical_records SET is_deleted = 1 WHERE Record_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        auditLog($conn, (int)$_SESSION['user_id'], 'Archive Medical Record', 'medical_records', (int)$id);
        echo json_encode(["status"=>"success"]);
    } else echo json_encode(["status"=>"error"]);
}

// GET SINGLE (For Edit Modal)
if ($action === 'get_single' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM medical_records WHERE Record_ID = $id");
    echo json_encode($result->fetch_assoc());
}
?>
