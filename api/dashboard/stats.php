<?php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

$session_user_id = (int)$_SESSION['user_id'];
$role_id = (int)($_SESSION['role_id'] ?? 0);

$response = [
    'stats' => [],
    'schedule' => [],
    'registrations' => []
];


// Presence expires with the same three-minute timeout used by session_status.php.
$onlineStaffSql = "SELECT COUNT(*) AS count FROM users
    WHERE Status = 'active' AND Role_ID IN (1, 2, 4) AND Is_Online = 1
      AND Last_Activity >= DATE_SUB(NOW(), INTERVAL 3 MINUTE)";
$response['stats']['active_staff'] = (int)($conn->query($onlineStaffSql)->fetch_assoc()['count'] ?? 0);

$onlineUsersSql = "SELECT COUNT(*) AS count FROM users
    WHERE Status = 'active' AND Is_Online = 1
      AND Last_Activity >= DATE_SUB(NOW(), INTERVAL 3 MINUTE)";
$response['stats']['active_users'] = (int)($conn->query($onlineUsersSql)->fetch_assoc()['count'] ?? 0);

if ($role_id === 1) {
    $response['stats']['today_appointments'] = (int)$conn->query("SELECT COUNT(*) AS count FROM appointments WHERE DATE(Appointment_Date) = CURDATE() AND Status != 'Cancelled'")->fetch_assoc()['count'];
    $response['stats']['pending'] = (int)$conn->query("SELECT COUNT(*) AS count FROM appointments WHERE Status = 'Pending'")->fetch_assoc()['count'];
    $response['stats']['total_patients'] = (int)$conn->query("SELECT COUNT(*) AS count FROM pets WHERE Status = 'active'")->fetch_assoc()['count'];

    $sql = "SELECT DATE_FORMAT(a.Appointment_Date, '%l:%i %p') AS Time, CONCAT(o.First_name, ' ', o.Last_name) AS Owner,
                   p.Name AS Pet, sp.Species_Name AS Species, COALESCE(s.Service_Name, 'General Consultation') AS Service_Name, a.Status
            FROM appointments a JOIN owners o ON o.Owner_ID = a.Owner_ID JOIN pets p ON p.Pet_ID = a.Pet_ID
            LEFT JOIN species sp ON sp.Species_ID = p.Species_ID LEFT JOIN services s ON s.Service_ID = a.Service_ID
            WHERE DATE(a.Appointment_Date) = CURDATE() AND a.Status != 'Cancelled' ORDER BY a.Appointment_Date";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) $response['schedule'][] = $row;

    $result = $conn->query("SELECT First_name, Last_name, Registration_Date, TIMESTAMPDIFF(SECOND, Registration_Date, NOW()) AS Registration_Seconds_Ago FROM owners WHERE Status = 'active' ORDER BY Registration_Date DESC LIMIT 4");
    while ($row = $result->fetch_assoc()) $response['registrations'][] = $row;
    echo json_encode($response);
    exit();
}

// Owner dashboard data.
$stmt = $conn->prepare("SELECT Owner_ID FROM owners WHERE User_ID = ?");
$stmt->bind_param("i", $session_user_id);
$stmt->execute();
$owner_data = $stmt->get_result()->fetch_assoc();
if (!$owner_data) { echo json_encode(["status" => "error", "message" => "Owner profile not found"]); exit(); }
$user_id = (int)$owner_data['Owner_ID'];

$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM appointments WHERE Owner_ID = ? AND Appointment_Date >= CURDATE() AND Status = 'Confirmed'");
$stmt->bind_param("i", $user_id); $stmt->execute(); $response['stats']['today_appointments'] = $stmt->get_result()->fetch_assoc()['count'];
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM appointments WHERE Owner_ID = ? AND Status = 'Pending'");
$stmt->bind_param("i", $user_id); $stmt->execute(); $response['stats']['pending'] = $stmt->get_result()->fetch_assoc()['count'];
$stmt = $conn->prepare("SELECT COUNT(*) AS count FROM pets WHERE Owner_ID = ? AND Status = 'active'");
$stmt->bind_param("i", $user_id); $stmt->execute(); $response['stats']['total_patients'] = $stmt->get_result()->fetch_assoc()['count'];

// 2. UPCOMING SCHEDULE (Changed from CURDATE() to >= CURDATE())
$sql = "SELECT
            DATE_FORMAT(COALESCE(ce.Event_Date, DATE(a.Appointment_Date)), '%M %d, %Y') AS Date,
            CASE WHEN ce.Event_Type = 'SpayNeuter' THEN 'Time to be announced'
                 ELSE DATE_FORMAT(a.Appointment_Date, '%l:%i %p') END AS Time,
            COALESCE(GROUP_CONCAT(DISTINCT event_pet.Name ORDER BY event_pet.Name SEPARATOR ', '), p.Name) AS Pet,
            COALESCE(GROUP_CONCAT(DISTINCT event_species.Species_Name SEPARATOR ', '), sp.Species_Name) AS Species,
            CASE
                WHEN ce.Event_Type = 'SpayNeuter' THEN CONCAT('Spay/Neuter', CASE WHEN ce.Title IS NOT NULL AND ce.Title != '' THEN CONCAT(' — ', ce.Title) ELSE '' END)
                WHEN s.Service_Name IS NOT NULL THEN s.Service_Name
                ELSE 'General Consultation'
            END AS Service_Name,
            a.Status
        FROM appointments a
        JOIN pets p ON a.Pet_ID = p.Pet_ID
        LEFT JOIN species sp ON p.Species_ID = sp.Species_ID
        LEFT JOIN services s ON a.Service_ID = s.Service_ID
        LEFT JOIN calendar_events ce ON ce.Event_ID = a.Event_ID
        LEFT JOIN appointment_pets ap ON ap.Appointment_ID = a.Appointment_ID
        LEFT JOIN pets event_pet ON event_pet.Pet_ID = ap.Pet_ID
        LEFT JOIN species event_species ON event_species.Species_ID = event_pet.Species_ID
        WHERE a.Owner_ID = ?
          AND COALESCE(ce.Event_Date, DATE(a.Appointment_Date)) >= CURDATE()
          AND a.Status NOT IN ('Cancelled', 'Completed')
        GROUP BY a.Appointment_ID
        ORDER BY COALESCE(ce.Event_Date, DATE(a.Appointment_Date)), a.Appointment_Date ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

while($row = $result->fetch_assoc()) {
    $response['schedule'][] = $row;
}

echo json_encode($response);
?>
