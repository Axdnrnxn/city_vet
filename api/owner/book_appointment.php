<?php
// File: api/owner/book_appointment.php
// API endpoint to book an appointment for a pet owner
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

if (!isset($_SESSION['user_id'])) { 
    echo json_encode(["status" => "error", "message" => "Unauthorized"]); 
    exit(); 
}

if (isset($_POST['book_surgery'])) {
    $user_id = $_SESSION['user_id'];
    
    // Get the actual Owner_ID linked to this logged-in User
    $owner_q = $conn->query("SELECT Owner_ID FROM owners WHERE User_ID = $user_id");
    $owner = $owner_q->fetch_assoc();
    $owner_id = $owner['Owner_ID'];
    
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
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request"]);
}
?>