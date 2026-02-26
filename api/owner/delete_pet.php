<?php
require_once('../../config/db_connection.php');
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $pet_id = $_GET['id'];

    // Soft delete by setting status to 'archived'
    $stmt = $conn->prepare("UPDATE pets SET Status = 'archived' WHERE Pet_ID = ?");
    $stmt->bind_param("i", $pet_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Pet has been archived."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No pet ID provided"]);
}
?>