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

function writeAuditLog($conn, $userId, $action, $tableAffected, $recordId = 0) {
    if (!$userId) return;

    $stmt = $conn->prepare("INSERT INTO audit_logs (User_ID, Action, Table_Affected, Record_ID) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issi", $userId, $action, $tableAffected, $recordId);
        $stmt->execute();
        $stmt->close();
    }
}

if (isset($_POST['book_surgery'])) {
    $user_id = (int)$_SESSION['user_id'];
    
    // Get the actual Owner_ID linked to this logged-in User
    $owner_stmt = $conn->prepare("SELECT Owner_ID FROM owners WHERE User_ID = ? AND Status = 'active'");
    $owner_stmt->bind_param("i", $user_id);
    $owner_stmt->execute();
    $owner_q = $owner_stmt->get_result();
    $owner = $owner_q->fetch_assoc();

    if (!$owner) {
        echo json_encode(["status" => "error", "message" => "Owner profile not found."]);
        exit();
    }

    $owner_id = $owner['Owner_ID'];
    
    $pet_id = (int)($_POST['pet_id'] ?? 0);
    $service_id = (int)($_POST['service_id'] ?? 0);
    $date = $_POST['appointment_date'] ?? '';

    if (!$pet_id || !$service_id || empty($date)) {
        echo json_encode(["status" => "error", "message" => "Please complete all appointment fields."]);
        exit();
    }

    $appointmentTime = strtotime($date);
    if (!$appointmentTime || $appointmentTime < strtotime(date('Y-m-d'))) {
        echo json_encode(["status" => "error", "message" => "Appointment date cannot be in the past."]);
        exit();
    }

    $pet_stmt = $conn->prepare("SELECT Pet_ID FROM pets WHERE Pet_ID = ? AND Owner_ID = ? AND Status = 'active'");
    $pet_stmt->bind_param("ii", $pet_id, $owner_id);
    $pet_stmt->execute();
    if ($pet_stmt->get_result()->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Selected pet is not available."]);
        exit();
    }

    $service_stmt = $conn->prepare("SELECT Service_ID FROM services WHERE Service_ID = ? AND Status = 'active'");
    $service_stmt->bind_param("i", $service_id);
    $service_stmt->execute();
    if ($service_stmt->get_result()->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Selected service is not available."]);
        exit();
    }

    $duplicate_stmt = $conn->prepare("
        SELECT Appointment_ID
        FROM appointments
        WHERE Pet_ID = ?
          AND Appointment_Date = ?
          AND Status IN ('Pending', 'Confirmed')
        LIMIT 1
    ");
    $duplicate_stmt->bind_param("is", $pet_id, $date);
    $duplicate_stmt->execute();
    if ($duplicate_stmt->get_result()->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "This pet already has an appointment request for that date and time."]);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO appointments (Owner_ID, Pet_ID, Service_ID, Appointment_Date, Status) VALUES (?, ?, ?, ?, 'Pending')");
    $stmt->bind_param("iiis", $owner_id, $pet_id, $service_id, $date);
    
    if ($stmt->execute()) {
        $appointment_id = $conn->insert_id;
        writeAuditLog($conn, $user_id, "Request Appointment", "appointments", $appointment_id);
        echo json_encode(["status" => "success", "message" => "Appointment requested successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
    }
    exit();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request"]);
}
?>
