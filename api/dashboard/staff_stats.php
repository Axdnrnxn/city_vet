<?php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 4) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$response = ['stats' => [], 'schedule' => []];

$count_sql = "SELECT COUNT(*) as total FROM appointments WHERE DATE(Appointment_Date) = CURDATE() AND Status != 'Cancelled'";
$response['stats']['today_appointments'] = $conn->query($count_sql)->fetch_assoc()['total'];

$sql = "SELECT COUNT(*) as count FROM pets WHERE Status = 'active'";
$response['stats']['total_patients'] = $conn->query($sql)->fetch_assoc()['count'];

$sql = "SELECT COUNT(*) AS count FROM users
        WHERE Status = 'active' AND Role_ID IN (1, 2, 4) AND Is_Online = 1
          AND Last_Activity >= DATE_SUB(NOW(), INTERVAL 3 MINUTE)";
$response['stats']['active_staff'] = $conn->query($sql)->fetch_assoc()['count'];

$sql = "
    SELECT 
        a.Appointment_ID,
        p.Pet_ID,
        a.Notes,
        o.Contact_number,
        DATE_FORMAT(a.Appointment_Date, '%l:%i %p') as Time,
        p.Name as Pet,
        o.First_name as Owner_F,
        o.Last_name as Owner_L,
        CASE
            WHEN a.Event_ID IS NOT NULL AND (LOWER(ce.Title) LIKE '%spay%' OR LOWER(ce.Title) LIKE '%neuter%') THEN 'Spay/Neuter'
            WHEN s.Service_Name IS NOT NULL THEN s.Service_Name
            ELSE 'General Consultation'
        END AS Service,
        sp.Species_Name as Species,
        a.Status as Status,
        a.Appointment_Date as SortDate
    FROM appointments a
    JOIN pets p ON a.Pet_ID = p.Pet_ID
    JOIN owners o ON p.Owner_ID = o.Owner_ID
    LEFT JOIN species sp ON p.Species_ID = sp.Species_ID
    LEFT JOIN services s ON a.Service_ID = s.Service_ID
    LEFT JOIN calendar_events ce ON ce.Event_ID = a.Event_ID
    WHERE DATE(a.Appointment_Date) = CURDATE()
      AND a.Status != 'Cancelled'
    ORDER BY SortDate ASC";

$result = $conn->query($sql);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $response['schedule'][] = $row;
    }
} else {
    $response['error'] = $conn->error;
}

echo json_encode($response);
?>
