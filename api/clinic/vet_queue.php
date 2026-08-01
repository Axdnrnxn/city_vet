<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db_connection.php';

if ($conn instanceof mysqli) $conn->set_charset("utf8mb4");

function respond($payload, $code = 200) {
    http_response_code($code);
    echo json_encode($payload);
    exit();
}

function requireRole($roles) {
    if (!isset($_SESSION['user_id'], $_SESSION['role_id']) || !in_array((int)$_SESSION['role_id'], $roles, true)) {
        respond(["status" => "error", "message" => "Access denied."], 403);
    }
}

function auditLog($conn, $action, $table, $recordId = 0) {
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if (!$userId) return;
    $stmt = $conn->prepare("INSERT INTO audit_logs (User_ID, Action, Table_Affected, Record_ID) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issi", $userId, $action, $table, $recordId);
        $stmt->execute();
    }
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $mode = $_GET['mode'] ?? 'queue';

    if ($mode === 'roster') {
        requireRole([1, 2, 4]);
        $eventId = (int)($_GET['event_id'] ?? 0);
        if (!$eventId) respond(["status" => "error", "message" => "Missing event ID."], 422);

        $stmt = $conn->prepare("
            SELECT ce.Title, ce.Event_Date, ce.Max_Slots,
                   a.Appointment_ID, a.Status,
                   o.First_name, o.Last_name, o.Contact_number,
                   GROUP_CONCAT(CONCAT(p.Name, ' (', COALESCE(sp.Species_Name, 'Pet'), ')') ORDER BY p.Name SEPARATOR ', ') AS Pets,
                   COUNT(ap.Pet_ID) AS Pet_Count
            FROM calendar_events ce
            LEFT JOIN appointments a ON a.Event_ID = ce.Event_ID AND a.Status != 'Cancelled'
            LEFT JOIN owners o ON o.Owner_ID = a.Owner_ID
            LEFT JOIN appointment_pets ap ON ap.Appointment_ID = a.Appointment_ID
            LEFT JOIN pets p ON p.Pet_ID = ap.Pet_ID
            LEFT JOIN species sp ON sp.Species_ID = p.Species_ID
            WHERE ce.Event_ID = ?
            GROUP BY a.Appointment_ID, ce.Event_ID
            ORDER BY o.Last_name ASC, o.First_name ASC
        ");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        $event = null;
        while ($row = $result->fetch_assoc()) {
            if (!$event) {
                $event = [
                    "Title" => $row["Title"],
                    "Event_Date" => $row["Event_Date"],
                    "Max_Slots" => $row["Max_Slots"]
                ];
            }
            if ($row['Appointment_ID']) $rows[] = $row;
        }
        respond(["status" => "success", "event" => $event, "roster" => $rows]);
    }

    requireRole([1, 2]);
    $sql = "
        SELECT a.Appointment_ID, a.Pet_ID, a.Owner_ID, a.Service_ID, a.Appointment_Date, a.Status, a.Notes,
               p.Name AS Pet_Name, p.Gender, p.Weight,
               o.First_name AS Owner_First, o.Last_name AS Owner_Last, o.Contact_number, o.Address,
               sp.Species_Name, b.Breed_Name, s.Service_Name
        FROM appointments a
        JOIN pets p ON p.Pet_ID = a.Pet_ID
        JOIN owners o ON o.Owner_ID = a.Owner_ID
        LEFT JOIN species sp ON sp.Species_ID = p.Species_ID
        LEFT JOIN breeds b ON b.Breed_ID = p.Breed_ID
        LEFT JOIN services s ON s.Service_ID = a.Service_ID
        WHERE DATE(a.Appointment_Date) = CURDATE()
          AND a.Status = 'In Consultation'
        ORDER BY a.Appointment_Date ASC
    ";
    $result = $conn->query($sql);
    $queue = [];
    while ($row = $result->fetch_assoc()) {
        $row['Time'] = date('h:i A', strtotime($row['Appointment_Date']));
        $queue[] = $row;
    }
    respond(["status" => "success", "queue" => $queue]);
}

if ($method !== 'POST') respond(["status" => "error", "message" => "Invalid request method."], 405);

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = $_POST;
$action = $input['action'] ?? '';

if ($action === 'create_walkin_queue') {
    requireRole([1, 4]);
    $ownerId = (int)($input['owner_id'] ?? 0);
    $petId = (int)($input['pet_id'] ?? 0);
    $serviceId = (int)($input['service_id'] ?? 0);
    $notes = trim($input['notes'] ?? 'Walk-in consultation');

    if (!$ownerId || !$petId || !$serviceId) {
        respond(["status" => "error", "message" => "Owner, pet, and service are required."], 422);
    }

    $check = $conn->prepare("SELECT Pet_ID FROM pets WHERE Pet_ID = ? AND Owner_ID = ? AND Status = 'active'");
    $check->bind_param("ii", $petId, $ownerId);
    $check->execute();
    if (!$check->get_result()->fetch_assoc()) {
        respond(["status" => "error", "message" => "Selected pet does not belong to this owner."], 422);
    }

    $appointmentDate = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("
        INSERT INTO appointments (Owner_ID, Pet_ID, Service_ID, Appointment_Date, Status, Notes)
        VALUES (?, ?, ?, ?, 'Checked In', ?)
    ");
    $stmt->bind_param("iiiss", $ownerId, $petId, $serviceId, $appointmentDate, $notes);
    if ($stmt->execute()) {
        $appointmentId = $conn->insert_id;
        auditLog($conn, "Create Walk-in Queue Item", "appointments", $appointmentId);
        respond(["status" => "success", "appointment_id" => $appointmentId]);
    }

    respond(["status" => "error", "message" => "Unable to add walk-in to queue."], 500);
}

if ($action === 'update_status') {
    requireRole([1, 4]);
    $appointmentId = (int)($input['appointment_id'] ?? 0);
    $status = $input['status'] ?? '';
    $allowed = ['Checked In', 'In Consultation', 'No Show', 'Completed', 'Cancelled'];
    if (!$appointmentId || !in_array($status, $allowed, true)) {
        respond(["status" => "error", "message" => "Invalid status update."], 422);
    }

    $stmt = $conn->prepare("UPDATE appointments SET Status = ? WHERE Appointment_ID = ?");
    $stmt->bind_param("si", $status, $appointmentId);
    if ($stmt->execute()) {
        auditLog($conn, "Update Queue Status to {$status}", "appointments", $appointmentId);
        respond(["status" => "success"]);
    }
    respond(["status" => "error", "message" => "Unable to update queue status."], 500);
}

if ($action === 'save_consultation') {
    requireRole([1, 2, 4]);
    $appointmentId = (int)($input['appointment_id'] ?? 0);
    $petId = (int)($input['pet_id'] ?? 0);
    $treatment = trim($input['treatment'] ?? '');
    $notes = trim($input['notes'] ?? '');
    $followUpDate = trim($input['follow_up_date'] ?? '');
    $vetId = (int)$_SESSION['user_id'];

    if (!$appointmentId || !$petId || $treatment === '') {
        respond(["status" => "error", "message" => "Appointment, pet, and treatment are required."], 422);
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("
            INSERT INTO medical_records (Appointment_ID, Pet_ID, Vet_ID, Treatment, Visit_Date, Notes)
            VALUES (?, ?, ?, ?, NOW(), ?)
        ");
        $stmt->bind_param("iiiss", $appointmentId, $petId, $vetId, $treatment, $notes);
        $stmt->execute();
        $recordId = $conn->insert_id;

        $update = $conn->prepare("UPDATE appointments SET Status = 'Completed', Vet_ID = ? WHERE Appointment_ID = ?");
        $update->bind_param("ii", $vetId, $appointmentId);
        $update->execute();

        $followUpId = null;
        if ($followUpDate !== '') {
            $appt = $conn->prepare("SELECT Owner_ID, Service_ID FROM appointments WHERE Appointment_ID = ?");
            $appt->bind_param("i", $appointmentId);
            $appt->execute();
            $apptRow = $appt->get_result()->fetch_assoc();
            if ($apptRow) {
                $followUpDateTime = $followUpDate . ' 08:00:00';
                $followNotes = "Follow-up appointment from consultation #{$recordId}.";
                $serviceId = $apptRow['Service_ID'] ? (int)$apptRow['Service_ID'] : null;
                $insertFollow = $conn->prepare("
                    INSERT INTO appointments (Owner_ID, Pet_ID, Service_ID, Vet_ID, Appointment_Date, Status, Notes)
                    VALUES (?, ?, ?, ?, ?, 'Confirmed', ?)
                ");
                $ownerId = (int)$apptRow['Owner_ID'];
                $insertFollow->bind_param("iiiiss", $ownerId, $petId, $serviceId, $vetId, $followUpDateTime, $followNotes);
                $insertFollow->execute();
                $followUpId = $conn->insert_id;

                $notice = $conn->prepare("
                    INSERT INTO notifications (User_ID, Title, Message, Type)
                    SELECT o.User_ID, 'Follow-up Appointment Scheduled',
                           CONCAT('A follow-up appointment has been scheduled on ', DATE_FORMAT(?, '%M %d, %Y'), '.'),
                           'Appointment'
                    FROM owners o WHERE o.Owner_ID = ?
                ");
                $notice->bind_param("si", $followUpDateTime, $ownerId);
                $notice->execute();
            }
        }

        auditLog($conn, "Save Vet Consultation", "medical_records", $recordId);
        $conn->commit();
        respond(["status" => "success", "record_id" => $recordId, "follow_up_id" => $followUpId]);
    } catch (Exception $e) {
        $conn->rollback();
        respond(["status" => "error", "message" => $e->getMessage()], 500);
    }
}

respond(["status" => "error", "message" => "Unknown action."], 400);
