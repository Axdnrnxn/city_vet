<?php
session_start();
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db_connection.php';

if (isset($conn) && $conn instanceof mysqli) {
    $conn->set_charset("utf8mb4");
}

function jsonResponse($payload, $code = 200) {
    http_response_code($code);
    echo json_encode($payload);
    exit();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'], $_SESSION['role_id'])) {
        jsonResponse(["status" => "error", "message" => "Unauthorized"], 401);
    }
}

function requireRole($roles) {
    requireLogin();
    if (!in_array((int)$_SESSION['role_id'], $roles, true)) {
        jsonResponse(["status" => "error", "message" => "Access denied"], 403);
    }
}

function writeAuditLog($conn, $userId, $action, $tableAffected, $recordId = 0) {
    if (!$userId) return;

    $stmt = $conn->prepare("INSERT INTO audit_logs (User_ID, Action, Table_Affected, Record_ID) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issi", $userId, $action, $tableAffected, $recordId);
        $stmt->execute();
        $stmt->close();
    }
}

function notifyUsersByRole($conn, $roleId, $title, $message, $type = 'SpayNeuter') {
    $users = $conn->prepare("SELECT User_ID FROM users WHERE Role_ID = ? AND Status = 'active'");
    $users->bind_param("i", $roleId);
    $users->execute();
    $result = $users->get_result();

    $notice = $conn->prepare("INSERT INTO notifications (User_ID, Title, Message, Type) VALUES (?, ?, ?, ?)");
    while ($row = $result->fetch_assoc()) {
        $uid = (int)$row['User_ID'];
        $notice->bind_param("isss", $uid, $title, $message, $type);
        $notice->execute();
    }
    $notice->close();
    $users->close();
}

function notifyUser($conn, $userId, $title, $message, $type = 'SpayNeuter') {
    $stmt = $conn->prepare("INSERT INTO notifications (User_ID, Title, Message, Type) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $userId, $title, $message, $type);
    $stmt->execute();
    $stmt->close();
}

function approvedPetCount($conn, $eventId) {
    $stmt = $conn->prepare("
        SELECT COUNT(ap.Appointment_Pet_ID) AS used_slots
        FROM appointments a
        JOIN appointment_pets ap ON ap.Appointment_ID = a.Appointment_ID
        WHERE a.Event_ID = ? AND a.Status = 'Confirmed'
    ");
    $stmt->bind_param("i", $eventId);
    $stmt->execute();
    return (int)$stmt->get_result()->fetch_assoc()['used_slots'];
}

function sendOwnerEmail($email, $subject, $message) {
    if (!$email) return false;
    $headers = "From: City Veterinary Clinic <no-reply@cityvet.local>\r\n";
    return @mail($email, $subject, $message, $headers);
}

function sendOwnerSms($phone, $message) {
    // Replace this with Semaphore/Twilio/iTexMo integration credentials for production SMS.
    return !empty($phone) && !empty($message);
}

requireLogin();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $mode = $_GET['mode'] ?? 'events';

    if ($mode === 'pending') {
        requireRole([1, 4]);

        $sql = "
            SELECT
                a.Appointment_ID,
                a.Event_ID,
                a.Appointment_Date,
                a.Status,
                ce.Title,
                ce.Event_Date,
                o.First_name,
                o.Last_name,
                o.Contact_number,
                u.Email,
                COUNT(ap.Pet_ID) AS Pet_Count,
                GROUP_CONCAT(p.Name ORDER BY p.Name SEPARATOR ', ') AS Pet_Names
            FROM appointments a
            JOIN calendar_events ce ON ce.Event_ID = a.Event_ID
            JOIN owners o ON o.Owner_ID = a.Owner_ID
            JOIN users u ON u.User_ID = o.User_ID
            JOIN appointment_pets ap ON ap.Appointment_ID = a.Appointment_ID
            JOIN pets p ON p.Pet_ID = ap.Pet_ID
            WHERE a.Status = 'Pending'
            GROUP BY a.Appointment_ID
            ORDER BY ce.Event_Date ASC, a.Appointment_ID ASC
        ";
        $result = $conn->query($sql);
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        jsonResponse(["status" => "success", "bookings" => $rows]);
    }

    $sql = "
        SELECT
            ce.Event_ID,
            ce.Title,
            ce.Event_Date,
            ce.Max_Slots,
            ce.Status,
            COALESCE(COUNT(ap.Appointment_Pet_ID), 0) AS Used_Slots
        FROM calendar_events ce
        LEFT JOIN appointments a
            ON a.Event_ID = ce.Event_ID
            AND a.Status = 'Confirmed'
        LEFT JOIN appointment_pets ap ON ap.Appointment_ID = a.Appointment_ID
        WHERE ce.Status != 'Cancelled'
        GROUP BY ce.Event_ID
        ORDER BY ce.Event_Date ASC
    ";
    $result = $conn->query($sql);
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $row['Remaining_Slots'] = max(0, (int)$row['Max_Slots'] - (int)$row['Used_Slots']);
        $row['Is_Full'] = $row['Remaining_Slots'] <= 0;
        $events[] = $row;
    }
    jsonResponse(["status" => "success", "events" => $events]);
}

if ($method !== 'POST') {
    jsonResponse(["status" => "error", "message" => "Invalid request method"], 405);
}

$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    $input = $_POST;
}
$action = $input['action'] ?? '';

if ($action === 'create_event') {
    requireRole([1, 4]);

    $title = trim($input['title'] ?? '');
    $eventDate = trim($input['event_date'] ?? '');
    $maxSlots = (int)($input['max_slots'] ?? 0);
    $createdBy = (int)$_SESSION['user_id'];

    if ($title === '' || $eventDate === '' || $maxSlots < 1) {
        jsonResponse(["status" => "error", "message" => "Please provide title, date, and available slots."], 422);
    }

    $dateObj = DateTime::createFromFormat('Y-m-d', $eventDate);
    if (!$dateObj || $dateObj->format('Y-m-d') !== $eventDate) {
        jsonResponse(["status" => "error", "message" => "Invalid event date."], 422);
    }

    $stmt = $conn->prepare("INSERT INTO calendar_events (Title, Event_Date, Max_Slots, Created_By) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssii", $title, $eventDate, $maxSlots, $createdBy);

    if (!$stmt->execute()) {
        $message = $conn->errno === 1062 ? "An event already exists on this date." : "Unable to create event.";
        jsonResponse(["status" => "error", "message" => $message], 400);
    }

    $eventId = $conn->insert_id;
    writeAuditLog($conn, $createdBy, "Create Spay/Neuter Event", "calendar_events", $eventId);
    notifyUsersByRole(
        $conn,
        3,
        "New Spay & Neuter Schedule",
        "{$title} is available on {$eventDate}. Book your registered pets while slots are available."
    );

    jsonResponse(["status" => "success", "message" => "Event created and pet owners were notified.", "event_id" => $eventId]);
}

if ($action === 'book_event') {
    requireRole([3]);

    $eventId = (int)($input['event_id'] ?? 0);
    $petIds = $input['pet_ids'] ?? [];
    if (!is_array($petIds)) $petIds = [$petIds];
    $petIds = array_values(array_unique(array_filter(array_map('intval', $petIds))));

    if (!$eventId || count($petIds) === 0) {
        jsonResponse(["status" => "error", "message" => "Select an event and at least one pet."], 422);
    }

    $userId = (int)$_SESSION['user_id'];
    $ownerStmt = $conn->prepare("SELECT Owner_ID FROM owners WHERE User_ID = ? AND Status = 'active'");
    $ownerStmt->bind_param("i", $userId);
    $ownerStmt->execute();
    $owner = $ownerStmt->get_result()->fetch_assoc();
    if (!$owner) {
        jsonResponse(["status" => "error", "message" => "Owner profile not found."], 404);
    }
    $ownerId = (int)$owner['Owner_ID'];

    $conn->begin_transaction();
    try {
        $eventStmt = $conn->prepare("SELECT Event_ID, Title, Event_Date, Max_Slots, Status FROM calendar_events WHERE Event_ID = ? FOR UPDATE");
        $eventStmt->bind_param("i", $eventId);
        $eventStmt->execute();
        $event = $eventStmt->get_result()->fetch_assoc();

        if (!$event || $event['Status'] !== 'Open') {
            throw new Exception("This Spay/Neuter event is not available for booking.");
        }

        $usedSlots = approvedPetCount($conn, $eventId);
        if ($usedSlots >= (int)$event['Max_Slots']) {
            throw new Exception("This event is already fully booked.");
        }
        if ($usedSlots + count($petIds) > (int)$event['Max_Slots']) {
            throw new Exception("Only " . ((int)$event['Max_Slots'] - $usedSlots) . " slot(s) remain for this event.");
        }

        $petStmt = $conn->prepare("SELECT Pet_ID FROM pets WHERE Pet_ID = ? AND Owner_ID = ? AND Status = 'active'");
        foreach ($petIds as $petId) {
            $petStmt->bind_param("ii", $petId, $ownerId);
            $petStmt->execute();
            if ($petStmt->get_result()->num_rows === 0) {
                throw new Exception("One or more selected pets are not active under your account.");
            }
        }

        $dupStmt = $conn->prepare("
            SELECT p.Name
            FROM appointment_pets ap
            JOIN appointments a ON a.Appointment_ID = ap.Appointment_ID
            JOIN pets p ON p.Pet_ID = ap.Pet_ID
            WHERE a.Event_ID = ?
              AND ap.Pet_ID = ?
              AND a.Status IN ('Pending', 'Confirmed')
            LIMIT 1
        ");
        foreach ($petIds as $petId) {
            $dupStmt->bind_param("ii", $eventId, $petId);
            $dupStmt->execute();
            $duplicate = $dupStmt->get_result()->fetch_assoc();
            if ($duplicate) {
                throw new Exception($duplicate['Name'] . " already has a booking request for this event.");
            }
        }

        $firstPetId = $petIds[0];
        $appointmentDate = $event['Event_Date'] . " 08:00:00";
        $notes = "Spay/Neuter event booking for " . count($petIds) . " pet(s).";
        $apptStmt = $conn->prepare("
            INSERT INTO appointments (Event_ID, Owner_ID, Pet_ID, Service_ID, Appointment_Date, Status, Notes)
            VALUES (?, ?, ?, NULL, ?, 'Pending', ?)
        ");
        $apptStmt->bind_param("iiiss", $eventId, $ownerId, $firstPetId, $appointmentDate, $notes);
        $apptStmt->execute();
        $appointmentId = $conn->insert_id;

        $joinStmt = $conn->prepare("INSERT INTO appointment_pets (Appointment_ID, Pet_ID) VALUES (?, ?)");
        foreach ($petIds as $petId) {
            $joinStmt->bind_param("ii", $appointmentId, $petId);
            $joinStmt->execute();
        }

        notifyUsersByRole(
            $conn,
            4,
            "Pending Spay/Neuter Booking",
            "A pet owner submitted a booking request for {$event['Title']} on {$event['Event_Date']}."
        );
        writeAuditLog($conn, $userId, "Request Spay/Neuter Booking", "appointments", $appointmentId);

        $conn->commit();
        jsonResponse(["status" => "success", "message" => "Booking request submitted. Please wait for staff confirmation."]);
    } catch (Exception $e) {
        $conn->rollback();
        jsonResponse(["status" => "error", "message" => $e->getMessage()], 400);
    }
}

if ($action === 'confirm_booking') {
    requireRole([1, 4]);

    $appointmentId = (int)($input['appointment_id'] ?? 0);
    if (!$appointmentId) {
        jsonResponse(["status" => "error", "message" => "Missing appointment ID."], 422);
    }

    $conn->begin_transaction();
    try {
        $apptStmt = $conn->prepare("
            SELECT
                a.Appointment_ID,
                a.Event_ID,
                a.Status,
                ce.Title,
                ce.Event_Date,
                ce.Max_Slots,
                o.User_ID,
                o.Contact_number,
                u.Email
            FROM appointments a
            JOIN calendar_events ce ON ce.Event_ID = a.Event_ID
            JOIN owners o ON o.Owner_ID = a.Owner_ID
            JOIN users u ON u.User_ID = o.User_ID
            WHERE a.Appointment_ID = ?
            FOR UPDATE
        ");
        $apptStmt->bind_param("i", $appointmentId);
        $apptStmt->execute();
        $appt = $apptStmt->get_result()->fetch_assoc();

        if (!$appt || $appt['Status'] !== 'Pending') {
            throw new Exception("This booking is no longer pending.");
        }

        $eventLock = $conn->prepare("SELECT Event_ID FROM calendar_events WHERE Event_ID = ? FOR UPDATE");
        $eventId = (int)$appt['Event_ID'];
        $eventLock->bind_param("i", $eventId);
        $eventLock->execute();
        $eventLockResult = $eventLock->get_result();
        if ($eventLockResult) {
            $eventLockResult->free();
        }
        $eventLock->close();

        $countStmt = $conn->prepare("SELECT COUNT(*) AS Pet_Count FROM appointment_pets WHERE Appointment_ID = ?");
        $countStmt->bind_param("i", $appointmentId);
        $countStmt->execute();
        $petCount = (int)$countStmt->get_result()->fetch_assoc()['Pet_Count'];

        $usedSlots = approvedPetCount($conn, $eventId);
        if ($usedSlots + $petCount > (int)$appt['Max_Slots']) {
            throw new Exception("Not enough slots remain for this booking.");
        }

        $update = $conn->prepare("UPDATE appointments SET Status = 'Confirmed', Confirmed_At = NOW() WHERE Appointment_ID = ?");
        $update->bind_param("i", $appointmentId);
        $update->execute();

        $remaining = (int)$appt['Max_Slots'] - ($usedSlots + $petCount);
        if ($remaining <= 0) {
            $close = $conn->prepare("UPDATE calendar_events SET Status = 'Closed' WHERE Event_ID = ?");
            $close->bind_param("i", $eventId);
            $close->execute();
        }

        $message = "Your Spay/Neuter appointment for {$appt['Title']} on {$appt['Event_Date']} has been confirmed. Please reply or contact the clinic to confirm attendance.";
        notifyUser($conn, (int)$appt['User_ID'], "Confirm Your Attendance", $message);
        $emailSent = sendOwnerEmail($appt['Email'], "City Vet Spay/Neuter Attendance Confirmation", $message);
        $smsQueued = sendOwnerSms($appt['Contact_number'], $message);
        writeAuditLog($conn, (int)$_SESSION['user_id'], "Confirm Spay/Neuter Booking", "appointments", $appointmentId);

        $conn->commit();
        jsonResponse([
            "status" => "success",
            "message" => "Booking confirmed. Owner notification was created.",
            "remaining_slots" => $remaining,
            "email_sent" => $emailSent,
            "sms_queued" => $smsQueued
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        jsonResponse(["status" => "error", "message" => $e->getMessage()], 400);
    }
}

jsonResponse(["status" => "error", "message" => "Unknown action."], 400);
