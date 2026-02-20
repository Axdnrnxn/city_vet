<?php
// File: api/dashboard/stats.php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

// Security Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$response = [
    'stats' => [],
    'schedule' => [],
    'registrations' => []
];

// 1. CARD STATS
// Today's Appointments
$sql = "SELECT COUNT(*) as count FROM appointments WHERE DATE(Appointment_Date) = CURDATE()";
$result = $conn->query($sql);
$response['stats']['today_appointments'] = $result->fetch_assoc()['count'];

// Pending Requests (Appointments marked as Pending)
$sql = "SELECT COUNT(*) as count FROM appointments WHERE Status = 'Pending'";
$result = $conn->query($sql);
$response['stats']['pending'] = $result->fetch_assoc()['count'];

// Total Patients (Active Pets)
$sql = "SELECT COUNT(*) as count FROM pets WHERE Status = 'active'";
$result = $conn->query($sql);
$response['stats']['total_patients'] = $result->fetch_assoc()['count'];

// Staff Active
$sql = "SELECT COUNT(*) as count FROM staff WHERE Status = 'active'";
$result = $conn->query($sql);
$response['stats']['staff_active'] = $result->fetch_assoc()['count'];

// 2. TODAY'S SCHEDULE TABLE
$sql = "SELECT 
            TIME_FORMAT(a.Appointment_Date, '%h:%i %p') as Time,
            CONCAT(o.First_name, ' ', o.Last_name) as Owner,
            p.Name as Pet,
            sp.Species_Name as Species,
            s.Service_Name,
            a.Status
        FROM appointments a
        JOIN owners o ON a.Owner_ID = o.Owner_ID
        JOIN pets p ON a.Pet_ID = p.Pet_ID
        LEFT JOIN species sp ON p.Species_ID = sp.Species_ID
        LEFT JOIN services s ON a.Service_ID = s.Service_ID
        WHERE DATE(a.Appointment_Date) = CURDATE()
        ORDER BY a.Appointment_Date ASC";
$result = $conn->query($sql);
while($row = $result->fetch_assoc()) {
    $response['schedule'][] = $row;
}

// 3. RECENT REGISTRATIONS
$sql = "SELECT First_name, Last_name, Registration_Date 
        FROM owners 
        ORDER BY Registration_Date DESC LIMIT 4";
$result = $conn->query($sql);
while($row = $result->fetch_assoc()) {
    $response['registrations'][] = $row;
}

echo json_encode($response);
?>