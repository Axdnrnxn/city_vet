<?php
// File: api/clinic/vet_get_pending.php
session_start();
ini_set('display_errors', 0);
header("Content-Type: application/json");

// Database Path Check
$paths = ['../../config/db_connection.php', '../../db_connection.php'];
foreach ($paths as $path) {
    if (file_exists($path)) { require_once $path; break; }
}

if (!isset($conn)) {
    echo json_encode([]); // Return empty list on failure
    exit();
}

$sql = "SELECT * FROM appointments WHERE Status = 'Pending' ORDER BY Appointment_Date DESC";
$result = $conn->query($sql);

$appointments = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

echo json_encode($appointments);
?>