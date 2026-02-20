<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

$action = $_GET['action'] ?? '';

// CREATE (Add New Record)
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = $_POST['pet_id'];
    $treatment = $_POST['treatment'];
    $notes = $_POST['symptoms']; // Symptoms field from UI maps to 'Notes' in DB
    
    $sql = "INSERT INTO medical_records (Pet_ID, Treatment, Notes, Visit_Date) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iss", $pet_id, $treatment, $notes);
    
    echo json_encode($stmt->execute() ? ["status"=>"success", "message"=>"Record saved"] : ["status"=>"error"]);
}

// UPDATE
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $treatment = $_POST['treatment'];
    $notes = $_POST['symptoms'];

    $sql = "UPDATE medical_records SET Treatment=?, Notes=? WHERE Record_ID=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $treatment, $notes, $id);
    
    echo json_encode($stmt->execute() ? ["status"=>"success", "message"=>"Updated"] : ["status"=>"error"]);
}

// SOFT DELETE
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    // Assumes you added the 'is_deleted' column as discussed
    $sql = "UPDATE medical_records SET is_deleted = 1 WHERE Record_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    echo json_encode($stmt->execute() ? ["status"=>"success"] : ["status"=>"error"]);
}

// GET SINGLE (For Edit Modal)
if ($action === 'get_single' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM medical_records WHERE Record_ID = $id");
    echo json_encode($result->fetch_assoc());
}
?>