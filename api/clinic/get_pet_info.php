<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

$pet_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($pet_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Invalid Pet ID"]);
    exit;
}

try {
    // 1. Get Pet Details + Owner Info + Species/Breed
    $pet_sql = "SELECT p.*, o.First_name, o.Last_name, o.Contact_number, o.Address, 
                       s.Species_Name, b.Breed_Name
                FROM pets p
                JOIN owners o ON p.Owner_ID = o.Owner_ID
                LEFT JOIN species s ON p.Species_ID = s.Species_ID
                LEFT JOIN breeds b ON p.Breed_ID = b.Breed_ID
                WHERE p.Pet_ID = ?";
    
    $stmt = $conn->prepare($pet_sql);
    $stmt->bind_param("i", $pet_id);
    $stmt->execute();
    $pet_details = $stmt->get_result()->fetch_assoc();

    if (!$pet_details) {
        echo json_encode(["status" => "error", "message" => "Pet not found"]);
        exit;
    }

    // 2. Get Medical History (Records + Consultations merged via UNION)
    $history_sql = "
        (SELECT Visit_Date as Date, Treatment, Notes, 'Medical Record' as Type 
         FROM medical_records WHERE Pet_ID = ? AND is_deleted = 0)
        UNION ALL
        (SELECT Consultation_Date as Date, Subject as Treatment, Concern_Description as Notes, 'Consultation' as Type 
         FROM consultations WHERE Pet_ID = ? AND Status = 'Completed')
        ORDER BY Date DESC";

    $hist_stmt = $conn->prepare($history_sql);
    $hist_stmt->bind_param("ii", $pet_id, $pet_id);
    $hist_stmt->execute();
    $history = $hist_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode([
        "status" => "success",
        "details" => $pet_details,
        "history" => $history
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}