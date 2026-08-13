<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../../config/db_connection.php';

if (!isset($_SESSION['user_id']) || (int)$_SESSION['role_id'] !== 1) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Access denied. Administrators only.']);
    exit();
}

function scalar($conn, $sql) {
    $result = $conn->query($sql);
    $row = $result ? $result->fetch_row() : [0];
    return $row[0] ?? 0;
}

function rows($conn, $sql) {
    $result = $conn->query($sql);
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}

try {
    $thisMonth = "Appointment_Date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND Appointment_Date < DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH)";
    $lastMonth = "Appointment_Date >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH) AND Appointment_Date < DATE_FORMAT(CURDATE(), '%Y-%m-01')";
    $thisRevenue = scalar($conn, "SELECT COALESCE(SUM(s.Price), 0) FROM appointments a JOIN services s ON s.Service_ID = a.Service_ID WHERE a.Status = 'Completed' AND $thisMonth");
    $lastRevenue = scalar($conn, "SELECT COALESCE(SUM(s.Price), 0) FROM appointments a JOIN services s ON s.Service_ID = a.Service_ID WHERE a.Status = 'Completed' AND $lastMonth");
    $thisAppointments = scalar($conn, "SELECT COUNT(*) FROM appointments WHERE $thisMonth");
    $lastAppointments = scalar($conn, "SELECT COUNT(*) FROM appointments WHERE $lastMonth");
    $thisClients = scalar($conn, "SELECT COUNT(*) FROM owners WHERE Registration_Date >= DATE_FORMAT(CURDATE(), '%Y-%m-01') AND Registration_Date < DATE_ADD(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH)");
    $lastClients = scalar($conn, "SELECT COUNT(*) FROM owners WHERE Registration_Date >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 1 MONTH) AND Registration_Date < DATE_FORMAT(CURDATE(), '%Y-%m-01')");
    $thisNoShow = scalar($conn, "SELECT COUNT(*) FROM appointments WHERE Status IN ('No Show', 'Cancelled') AND $thisMonth");
    $lastNoShow = scalar($conn, "SELECT COUNT(*) FROM appointments WHERE Status IN ('No Show', 'Cancelled') AND $lastMonth");
    $thisRevenue = (float)$thisRevenue;
    $lastRevenue = (float)$lastRevenue;
    $thisAppointments = (int)$thisAppointments;
    $lastAppointments = (int)$lastAppointments;
    $thisClients = (int)$thisClients;
    $lastClients = (int)$lastClients;
    $thisNoShow = (int)$thisNoShow;
    $lastNoShow = (int)$lastNoShow;
    $thisNoShowRate = $thisAppointments ? round($thisNoShow * 100 / $thisAppointments, 1) : 0;
    $lastNoShowRate = $lastAppointments ? round($lastNoShow * 100 / $lastAppointments, 1) : 0;

    $funnel = rows($conn, "SELECT Status, COUNT(*) AS total FROM appointments WHERE Appointment_Date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) GROUP BY Status");
    $trend = rows($conn, "SELECT DATE_FORMAT(a.Appointment_Date, '%Y-%m') AS month, COUNT(*) AS appointments, COALESCE(SUM(CASE WHEN a.Status = 'Completed' THEN s.Price ELSE 0 END), 0) AS revenue FROM appointments a LEFT JOIN services s ON s.Service_ID = a.Service_ID WHERE a.Appointment_Date >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 5 MONTH) GROUP BY DATE_FORMAT(a.Appointment_Date, '%Y-%m') ORDER BY month");
    $noShowTrend = rows($conn, "SELECT DATE_FORMAT(Appointment_Date, '%Y-%m') AS month, COUNT(*) AS appointments, SUM(Status IN ('No Show', 'Cancelled')) AS missed FROM appointments WHERE Appointment_Date >= DATE_SUB(DATE_FORMAT(CURDATE(), '%Y-%m-01'), INTERVAL 5 MONTH) GROUP BY DATE_FORMAT(Appointment_Date, '%Y-%m') ORDER BY month");
    $services = rows($conn, "SELECT COALESCE(s.Service_Name, 'Unassigned service') AS service_name, COUNT(*) AS volume, COALESCE(SUM(CASE WHEN a.Status = 'Completed' THEN s.Price ELSE 0 END), 0) AS revenue FROM appointments a LEFT JOIN services s ON s.Service_ID = a.Service_ID WHERE a.Appointment_Date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH) GROUP BY a.Service_ID, s.Service_Name ORDER BY revenue DESC, volume DESC LIMIT 8");
    $species = rows($conn, "SELECT COALESCE(sp.Species_Name, 'Unspecified') AS species_name, COUNT(*) AS total FROM pets p LEFT JOIN species sp ON sp.Species_ID = p.Species_ID WHERE p.Status = 'active' GROUP BY sp.Species_Name ORDER BY total DESC");
    $peak = rows($conn, "SELECT WEEKDAY(Appointment_Date) AS weekday, HOUR(Appointment_Date) AS hour, COUNT(*) AS total FROM appointments WHERE Appointment_Date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) GROUP BY WEEKDAY(Appointment_Date), HOUR(Appointment_Date)");
    $events = rows($conn, "SELECT ce.Title, ce.Event_Date, ce.Max_Slots, COUNT(ap.Appointment_Pet_ID) AS registered, SUM(CASE WHEN a.Status = 'Confirmed' THEN 1 ELSE 0 END) AS confirmed FROM calendar_events ce LEFT JOIN appointments a ON a.Event_ID = ce.Event_ID LEFT JOIN appointment_pets ap ON ap.Appointment_ID = a.Appointment_ID WHERE ce.Event_Type = 'SpayNeuter' GROUP BY ce.Event_ID ORDER BY ce.Event_Date DESC LIMIT 6");
    $vaccination = [
        'active_pets' => (int)scalar($conn, "SELECT COUNT(*) FROM pets WHERE Status = 'active'"),
        'up_to_date' => (int)scalar($conn, "SELECT COUNT(*) FROM (SELECT p.Pet_ID, MAX(v.Next_Due_Date) AS next_due FROM pets p LEFT JOIN vaccinations v ON v.Pet_ID = p.Pet_ID WHERE p.Status = 'active' GROUP BY p.Pet_ID HAVING next_due >= CURDATE()) x"),
        'overdue' => (int)scalar($conn, "SELECT COUNT(*) FROM (SELECT p.Pet_ID, MAX(v.Next_Due_Date) AS next_due FROM pets p LEFT JOIN vaccinations v ON v.Pet_ID = p.Pet_ID WHERE p.Status = 'active' GROUP BY p.Pet_ID HAVING next_due IS NULL OR next_due < CURDATE()) x")
    ];

    echo json_encode([
        'status' => 'success',
        'kpis' => [
            'revenue' => $thisRevenue, 'appointments' => $thisAppointments, 'clients' => $thisClients,
            'no_show_rate' => $thisNoShowRate,
            'deltas' => [
                'revenue' => $lastRevenue ? round(($thisRevenue - $lastRevenue) * 100 / $lastRevenue, 1) : null,
                'appointments' => $lastAppointments ? round(($thisAppointments - $lastAppointments) * 100 / $lastAppointments, 1) : null,
                'clients' => $lastClients ? round(($thisClients - $lastClients) * 100 / $lastClients, 1) : null,
                'no_show_rate' => round($thisNoShowRate - $lastNoShowRate, 1)
            ]
        ],
        'funnel' => $funnel, 'trend' => $trend, 'no_show_trend' => $noShowTrend, 'services' => $services,
        'species' => $species, 'peak' => $peak, 'events' => $events, 'vaccination' => $vaccination
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    error_log('City Vet reports error: ' . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Unable to load reports.']);
}
