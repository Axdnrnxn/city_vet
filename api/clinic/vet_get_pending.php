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

// Updated SQL with JOINS to get full Pet, Owner, and Service details for the new Modal
$sql = "SELECT 
            a.Appointment_ID, 
            a.Appointment_Date, 
            a.Status,
            a.Notes,
            p.Pet_ID,
            p.Name AS Pet_Name,
            p.Gender,
            p.Weight,
            o.First_name AS Owner_First,
            o.Last_name AS Owner_Last,
            o.Contact_number,
            o.Address,
            s.Species_Name,
            b.Breed_Name,
            srv.Service_Name
        FROM appointments a
        INNER JOIN pets p ON a.Pet_ID = p.Pet_ID
        INNER JOIN owners o ON a.Owner_ID = o.Owner_ID
        LEFT JOIN species s ON p.Species_ID = s.Species_ID
        LEFT JOIN breeds b ON p.Breed_ID = b.Breed_ID
        LEFT JOIN services srv ON a.Service_ID = srv.Service_ID
        WHERE a.Status = 'Pending' 
        ORDER BY a.Appointment_Date ASC"; 
        // Changed to ASC so the nearest appointments show up first in the queue

$result = $conn->query($sql);

$appointments = [];
if ($result) {
    while($row = $result->fetch_assoc()) {
        // Optional: Make the date look nicer before sending it to the frontend
        if (!empty($row['Appointment_Date'])) {
            $phpdate = strtotime($row['Appointment_Date']);
            $row['Appointment_Date'] = date('M d, Y - h:i A', $phpdate);
        }
        $appointments[] = $row;
    }
} else {
    // If there is an SQL error, this helps you debug it in the Network tab
    echo json_encode(["error" => "SQL Error: " . $conn->error]);
    exit();
}

echo json_encode($appointments);
?>