<?php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$session_user_id = $_SESSION['user_id'];

// NEW: Get the REAL Owner_ID linked to this User_ID
$stmt = $conn->prepare("SELECT Owner_ID FROM owners WHERE User_ID = ?");
$stmt->bind_param("i", $session_user_id);
$stmt->execute();
$result = $stmt->get_result();
$owner_data = $result->fetch_assoc();

if (!$owner_data) {
    echo json_encode(["status" => "error", "message" => "Owner profile not found"]);
    exit();
}

$user_id = $owner_data['Owner_ID'];

$response = [
    'stats' => [],
    'schedule' => [],
    'registrations' => []
];


// 1. CARD STATS (Filtered for the specific Owner)
// New: Shows all appointments scheduled for today OR in the future
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE Owner_ID = ? AND Appointment_Date >= CURDATE() AND Status = 'Confirmed'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$response['stats']['today_appointments'] = $stmt->get_result()->fetch_assoc()['count'];

// Pending Requests for this owner
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM appointments WHERE Owner_ID = ? AND Status = 'Pending'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$response['stats']['pending'] = $stmt->get_result()->fetch_assoc()['count'];

// Total Pets owned by this user
$stmt = $conn->prepare("SELECT COUNT(*) as count FROM pets WHERE Owner_ID = ? AND Status = 'active'");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$response['stats']['total_patients'] = $stmt->get_result()->fetch_assoc()['count'];

// Staff Active (Global stat is fine)
$sql = "SELECT COUNT(*) as count FROM staff WHERE Status = 'active'";
$response['stats']['staff_active'] = $conn->query($sql)->fetch_assoc()['count'];

// 2. UPCOMING SCHEDULE (Changed from CURDATE() to >= CURDATE())
$sql = "SELECT 
            DATE_FORMAT(a.Appointment_Date, '%M %d, %Y') as Date,
            TIME_FORMAT(a.Appointment_Date, '%h:%i %p') as Time,
            p.Name as Pet,
            sp.Species_Name as Species,
            s.Service_Name,
            a.Status
        FROM appointments a
        JOIN pets p ON a.Pet_ID = p.Pet_ID
        LEFT JOIN species sp ON p.Species_ID = sp.Species_ID
        LEFT JOIN services s ON a.Service_ID = s.Service_ID
        WHERE a.Owner_ID = ? 
        AND a.Appointment_Date >= CURDATE() 
        AND a.Status != 'Cancelled'
        ORDER BY a.Appointment_Date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()) {
    $response['schedule'][] = $row;
}

// 3. RECENT REGISTRATIONS (Usually not needed for Owner view, but keeping structure)
$sql = "SELECT First_name, Last_name, Registration_Date 
        FROM owners 
        ORDER BY Registration_Date DESC LIMIT 4";
$result = $conn->query($sql);
while($row = $result->fetch_assoc()) {
    $response['registrations'][] = $row;
}

echo json_encode($response);
?>