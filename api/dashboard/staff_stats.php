<?php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

// 1. Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 4) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$response = ['stats' => [], 'schedule' => []];

// 2. Global Clinic Stats
// Total appointments today (Any owner)
$sql = "SELECT COUNT(*) as count FROM appointments WHERE DATE(Appointment_Date) = CURDATE() AND Status != 'Cancelled'";
$response['stats']['today_appointments'] = $conn->query($sql)->fetch_assoc()['count'];

// Total registered active pets in clinic
$sql = "SELECT COUNT(*) as count FROM pets WHERE Status = 'active'";
$response['stats']['total_patients'] = $conn->query($sql)->fetch_assoc()['count'];

// Active staff members
$sql = "SELECT COUNT(*) as count FROM staff WHERE Status = 'active'";
$response['stats']['staff_active'] = $conn->query($sql)->fetch_assoc()['count'];

// 3. Today's Full Queue (All Owners)
$sql = "SELECT 
            TIME_FORMAT(a.Appointment_Date, '%h:%i %p') as Time,
            p.Name as Pet,
            sp.Species_Name as Species,
            o.First_name as Owner_F, o.Last_name as Owner_L,
            s.Service_Name,
            a.Status
        FROM appointments a
        JOIN pets p ON a.Pet_ID = p.Pet_ID
        JOIN owners o ON p.Owner_ID = o.Owner_ID
        LEFT JOIN species sp ON p.Species_ID = sp.Species_ID
        LEFT JOIN services s ON a.Service_ID = s.Service_ID
        WHERE DATE(a.Appointment_Date) = CURDATE()
        AND a.Status != 'Cancelled'
        ORDER BY a.Appointment_Date ASC";

$result = $conn->query($sql);
while($row = $result->fetch_assoc()) {
    $response['schedule'][] = $row;
}

echo json_encode($response);