<?php
header('Content-Type: application/json');
require_once '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = $_POST['pet_id'];
    $weight = $_POST['weight'];
    $symptoms = $_POST['symptoms'];
    $diagnosis = $_POST['diagnosis'];
    $treatment = $_POST['treatment'];
    $follow_up = !empty($_POST['follow_up']) ? $_POST['follow_up'] : NULL;

    $sql = "INSERT INTO consultations (Pet_ID, Weight, Symptoms, Diagnosis, Treatment, Follow_up_Date, Visit_Date) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("idssss", $pet_id, $weight, $symptoms, $diagnosis, $treatment, $follow_up);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Consultation recorded!"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
}
?>