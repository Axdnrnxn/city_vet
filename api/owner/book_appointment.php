<?php
// File: api/owner/book_appointment.php
session_start();
ini_set('display_errors', 0);
header("Content-Type: application/json");

// Database Path Check
$paths = ['../../config/db_connection.php', '../../db_connection.php'];
foreach ($paths as $path) {
    if (file_exists($path)) { require_once $path; break; }
}

if (isset($_POST['book_surgery'])) {
    $owner_id = $_SESSION['owner_id'] ?? 1; // Mock ID
    $pet_id = $_POST['pet_id'];
    $service_id = $_POST['service_id']; 
    $date = $_POST['appointment_date'];

    $stmt = $conn->prepare("INSERT INTO appointments (Owner_ID, Pet_ID, Service_ID, Appointment_Date, Status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("iiis", $owner_id, $pet_id, $service_id, $date);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Appointment requested successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
    }
    exit();
}
?>