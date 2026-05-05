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
// Total activities today (Scheduled Appointments + Walk-in Medical Records)
$count_sql = "SELECT 
    (SELECT COUNT(*) FROM appointments WHERE DATE(Appointment_Date) = CURDATE() AND Status != 'Cancelled') + 
    (SELECT COUNT(*) FROM medical_records WHERE DATE(Visit_Date) = CURDATE() AND is_deleted = 0) as total";
$response['stats']['today_appointments'] = $conn->query($count_sql)->fetch_assoc()['total'];

// Total registered active pets in clinic
$sql = "SELECT COUNT(*) as count FROM pets WHERE Status = 'active'";
$response['stats']['total_patients'] = $conn->query($sql)->fetch_assoc()['count'];

// Active staff members
$sql = "SELECT COUNT(*) as count FROM staff WHERE Status = 'active'";
$response['stats']['staff_active'] = $conn->query($sql)->fetch_assoc()['count'];

// 3. Today's Full Queue (UNION ALL: Appointments + Walk-ins)
// ✨ We are now selecting Pet_ID, Notes, Contact_number, and Species to feed the new Modal ✨
$sql = "
    (SELECT 
        a.Appointment_ID,
        p.Pet_ID,
        a.Notes,
        o.Contact_number,
        TIME_FORMAT(a.Appointment_Date, '%h:%i %p') as Time,
        p.Name as Pet,
        o.First_name as Owner_F, o.Last_name as Owner_L,
        s.Service_Name as Service,
        sp.Species_Name as Species,
        a.Status as Status,
        a.Appointment_Date as SortDate
    FROM appointments a
    JOIN pets p ON a.Pet_ID = p.Pet_ID
    JOIN owners o ON p.Owner_ID = o.Owner_ID
    LEFT JOIN species sp ON p.Species_ID = sp.Species_ID
    LEFT JOIN services s ON a.Service_ID = s.Service_ID
    WHERE DATE(a.Appointment_Date) = CURDATE() AND a.Status != 'Cancelled')

    UNION ALL

    (SELECT 
        m.Record_ID as Appointment_ID,
        p.Pet_ID,
        m.Notes,
        o.Contact_number,
        TIME_FORMAT(m.Visit_Date, '%h:%i %p') as Time,
        p.Name as Pet,
        o.First_name as Owner_F, o.Last_name as Owner_L,
        CONCAT('[Walk-in] ', COALESCE(m.Treatment, 'General Checkup')) as Service,
        sp.Species_Name as Species,
        'Checked In' as Status,
        m.Visit_Date as SortDate
    FROM medical_records m
    JOIN pets p ON m.Pet_ID = p.Pet_ID
    JOIN owners o ON p.Owner_ID = o.Owner_ID
    LEFT JOIN species sp ON p.Species_ID = sp.Species_ID
    WHERE DATE(m.Visit_Date) = CURDATE() AND m.is_deleted = 0)

    ORDER BY SortDate ASC";

$result = $conn->query($sql);

if ($result) {
    while($row = $result->fetch_assoc()) {
        $response['schedule'][] = $row;
    }
} else {
    // Helpful for debugging if there is an SQL syntax issue
    $response['error'] = $conn->error; 
}

echo json_encode($response);
?>