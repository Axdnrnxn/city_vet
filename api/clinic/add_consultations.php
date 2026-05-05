<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Map UI fields to your specific table columns
    $owner_id = $_POST['owner_id'];
    $pet_id = $_POST['pet_id'];
    $vet_id = $_POST['vet_id'] ?? 1; // Default to first vet if not specified
    $subject = $_POST['treatment']; // Mapping "Treatment" from UI to "Subject"
    $description = $_POST['symptoms']; // Mapping "Symptoms" from UI to "Concern_Description"
    $status = 'Completed'; 
    $date = date('Y-m-d H:i:s');

    $sql = "INSERT INTO consultations (Owner_ID, Pet_ID, Vet_ID, Subject, Concern_Description, Status, Consultation_Date) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissss", $owner_id, $pet_id, $vet_id, $subject, $description, $status, $date);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Consultation recorded!"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
}
?>