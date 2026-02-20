<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

try {
    // 1. Fetch records from medical_records joined with pets and owners
    $sql = "SELECT m.Record_ID as Consultation_ID, m.Visit_Date, m.Treatment, m.Notes as Symptoms, 
               p.Name as Pet_Name, o.First_name as Owner_Name
        FROM medical_records m
        JOIN pets p ON m.Pet_ID = p.Pet_ID
        JOIN owners o ON p.Owner_ID = o.Owner_ID
        WHERE m.is_deleted = 0  /* Only show active records */
        ORDER BY m.Visit_Date DESC";
    
    $result = $conn->query($sql);
    $records = [];

    while($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    // 2. Dashboard Stats
    $today = $conn->query("SELECT COUNT(*) as count FROM medical_records WHERE DATE(Visit_Date) = CURDATE()")->fetch_assoc();
    $total = $conn->query("SELECT COUNT(*) as count FROM medical_records")->fetch_assoc();

    echo json_encode([
        "status" => "success",
        "today" => $today['count'],
        "total" => $total['count'],
        "records" => $records
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>