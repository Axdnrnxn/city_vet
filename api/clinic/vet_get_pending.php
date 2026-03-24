<?php
// File: api/clinic/vet_get_pending.php
session_start();
ini_set('display_errors', 0);
header("Content-Type: application/json");

// Database Path Check
$paths = ['../../config/db_connection.php', '../../db_connection.php', '../db_connection.php'];
foreach ($paths as $path) {
    if (file_exists($path)) { require_once $path; break; }
}

if (!isset($conn)) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Updated SQL with JOINS to get Pet and Owner details
$sql = "SELECT 
            a.Appointment_ID, 
            a.Appointment_Date, 
            a.Status,
            p.Name AS Pet_Name,
            o.First_Name AS Owner_First,
            o.Last_Name AS Owner_Last,
            s.Species_Name,
            b.Breed_Name
        FROM appointments a
        INNER JOIN pets p ON a.Pet_ID = p.Pet_ID
        INNER JOIN owners o ON p.Owner_ID = o.Owner_ID
        LEFT JOIN breeds b ON p.Breed_ID = b.Breed_ID
        LEFT JOIN species s ON b.Species_ID = s.Species_ID
        WHERE a.Status = 'Pending' 
        ORDER BY a.Appointment_Date DESC";

$result = $conn->query($sql);

$appointments = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
}

echo json_encode($appointments);
?>