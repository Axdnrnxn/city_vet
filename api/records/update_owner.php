<?php
// File: api/records/update_owner.php
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['owner_id']) || empty($data['fname']) || empty($data['lname'])) {
    echo json_encode(["status" => "error", "message" => "Name fields are required"]);
    exit();
}

// Update Owner Table
$stmt = $conn->prepare("UPDATE owners SET First_name = ?, Last_name = ?, Contact_Number = ?, Address = ? WHERE Owner_ID = ?");
$stmt->bind_param("ssssi", $data['fname'], $data['lname'], $data['phone'], $data['address'], $data['owner_id']);

if ($stmt->execute()) {
    // Optional: Update Email in Users table if needed (requires join update logic)
    echo json_encode(["status" => "success", "message" => "Owner details updated."]);
} else {
    echo json_encode(["status" => "error", "message" => "Update failed."]);
}
?>