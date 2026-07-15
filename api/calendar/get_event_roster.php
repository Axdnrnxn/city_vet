<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db_connection.php';

if ($conn instanceof mysqli) {
    $conn->set_charset("utf8mb4");
}

function jsonResponse($payload, $code = 200) {
    http_response_code($code);
    echo json_encode($payload);
    exit();
}

function requireRole($roles) {
    if (!isset($_SESSION['user_id'], $_SESSION['role_id'])) {
        jsonResponse(["status" => "error", "message" => "Unauthorized"], 401);
    }

    if (!in_array((int)$_SESSION['role_id'], $roles, true)) {
        jsonResponse(["status" => "error", "message" => "Access denied"], 403);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(["status" => "error", "message" => "Invalid request method"], 405);
}

requireRole([1, 2, 4]);

$eventId = (int)($_GET['event_id'] ?? 0);
if (!$eventId) {
    jsonResponse(["status" => "error", "message" => "Missing event ID"], 422);
}

$stmt = $conn->prepare(" 
    SELECT
        ce.Event_ID,
        ce.Title,
        ce.Event_Date,
        ce.Max_Slots,
        a.Appointment_ID,
        a.Status,
        CONCAT(COALESCE(o.First_name, ''), ' ', COALESCE(o.Last_name, '')) AS Owner_Name,
        o.Contact_number,
        COUNT(DISTINCT ap.Pet_ID) AS Pet_Count,
        GROUP_CONCAT(DISTINCT p.Name ORDER BY p.Name SEPARATOR ', ') AS Pet_Names
    FROM calendar_events ce
    LEFT JOIN appointments a
        ON a.Event_ID = ce.Event_ID
        AND a.Status != 'Cancelled'
    LEFT JOIN owners o
        ON o.Owner_ID = a.Owner_ID
    LEFT JOIN appointment_pets ap
        ON ap.Appointment_ID = a.Appointment_ID
    LEFT JOIN pets p
        ON p.Pet_ID = ap.Pet_ID
    WHERE ce.Event_ID = ?
    GROUP BY ce.Event_ID, ce.Title, ce.Event_Date, ce.Max_Slots, a.Appointment_ID, a.Status
    ORDER BY o.Last_name, o.First_name, a.Appointment_ID
");

$stmt->bind_param("i", $eventId);
$stmt->execute();
$result = $stmt->get_result();

$roster = [];
$event = null;

while ($row = $result->fetch_assoc()) {
    if (!$event) {
        $event = [
            "Event_ID" => (int)$row['Event_ID'],
            "Title" => $row['Title'],
            "Event_Date" => $row['Event_Date'],
            "Max_Slots" => (int)$row['Max_Slots']
        ];
    }

    if ($row['Appointment_ID']) {
        $roster[] = [
            "Appointment_ID" => (int)$row['Appointment_ID'],
            "Status" => $row['Status'],
            "Owner_Name" => trim($row['Owner_Name']),
            "Contact_number" => $row['Contact_number'],
            "Pet_Count" => (int)$row['Pet_Count'],
            "Pet_Names" => $row['Pet_Names']
        ];
    }
}

jsonResponse([
    "status" => "success",
    "event" => $event,
    "roster" => $roster
]);
