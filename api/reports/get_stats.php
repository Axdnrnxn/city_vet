<?php
require_once('../../config/db_connection.php');
header('Content-Type: application/json');

try {
    // 1. Service Audit Value (Sum of prices from appointments marked 'Completed')
    // Since medical_records doesn't have Price, we look at Appointments for the Audit Value
    $valueQuery = $conn->query("
        SELECT SUM(s.Price) as total_value 
        FROM appointments a
        JOIN services s ON a.Service_ID = s.Service_ID
        WHERE a.Status = 'Completed' AND MONTH(a.Appointment_Date) = MONTH(CURRENT_DATE)
    ");
    $totalValue = $valueQuery->fetch_assoc()['total_value'] ?? 0;

    // 2. Total Services Logged (This Month)
    $recordCount = $conn->query("SELECT COUNT(*) as total FROM medical_records WHERE MONTH(Visit_Date) = MONTH(CURRENT_DATE) AND is_deleted = 0");
    
    // 3. New Clients & Active Pets
    $clientCount = $conn->query("SELECT COUNT(*) as total FROM owners WHERE MONTH(Registration_Date) = MONTH(CURRENT_DATE)");
    $petCount = $conn->query("SELECT COUNT(*) as total FROM pets WHERE Status != 'archived'");

    // 4. Monthly Trend (Based on completed appointments with pricing)
    $trendQuery = $conn->query("
        SELECT DATE_FORMAT(a.Appointment_Date, '%b') as month, SUM(s.Price) as amount 
        FROM appointments a
        JOIN services s ON a.Service_ID = s.Service_ID
        WHERE a.Status = 'Completed'
        GROUP BY MONTH(a.Appointment_Date) 
        ORDER BY a.Appointment_Date ASC LIMIT 6
    ");
    $trend = [];
    while($row = $trendQuery->fetch_assoc()) { $trend[] = $row; }

    // 5. Species Distribution
    $speciesQuery = $conn->query("
        SELECT sp.Species_Name, COUNT(p.Pet_ID) as count 
        FROM pets p 
        JOIN species sp ON p.Species_ID = sp.Species_ID
        WHERE p.Status != 'archived'
        GROUP BY sp.Species_Name
    ");
    $speciesDist = [];
    while($row = $speciesQuery->fetch_assoc()) { $speciesDist[] = $row; }

    // 6. Recent Service Logs
    $recentQuery = $conn->query("
        SELECT mr.Visit_Date, o.Last_name, mr.Treatment
        FROM medical_records mr
        JOIN pets p ON mr.Pet_ID = p.Pet_ID
        JOIN owners o ON p.Owner_ID = o.Owner_ID
        WHERE mr.is_deleted = 0
        ORDER BY mr.Visit_Date DESC LIMIT 5
    ");
    $recent = [];
    while($row = $recentQuery->fetch_assoc()) { $recent[] = $row; }

    echo json_encode([
        'kpis' => [
            'value' => number_format($totalValue, 2),
            'clients' => $clientCount->fetch_assoc()['total'],
            'appts' => $recordCount->fetch_assoc()['total'],
            'pets' => $petCount->fetch_assoc()['total']
        ],
        'trend' => $trend,
        'species' => $speciesDist,
        'recent' => $recent
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}