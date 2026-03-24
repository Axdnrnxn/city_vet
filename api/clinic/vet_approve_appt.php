<?php
// File: api/clinic/vet_approve_appt.php
session_start();
header("Content-Type: application/json");

$paths = ['../../config/db_connection.php', '../../db_connection.php'];
foreach ($paths as $path) {
    if (file_exists($path)) { require_once $path; break; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $id = $data['id'] ?? null;

    if ($id && isset($conn)) {
        // Update status to 'Confirmed'
        $stmt = $conn->prepare("UPDATE appointments SET Status = 'Confirmed' WHERE Appointment_ID = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            echo json_encode(["success" => true]);
        } else {
            echo json_encode(["success" => false, "error" => $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(["success" => false, "error" => "Invalid ID or Connection"]);
    }
}
?>