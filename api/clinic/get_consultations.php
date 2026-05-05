<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

try {
    $sql = "SELECT c.Consultation_ID, c.Consultation_Date as Visit_Date, c.Subject as Treatment, 
                   c.Concern_Description as Symptoms, p.Name as Pet_Name, o.First_name as Owner_Name
            FROM consultations c
            JOIN pets p ON c.Pet_ID = p.Pet_ID
            JOIN owners o ON c.Owner_ID = o.Owner_ID
            WHERE c.Status != 'Cancelled'
            ORDER BY c.Consultation_Date DESC";
    
    $result = $conn->query($sql);
    $records = [];

    while($row = $result->fetch_assoc()) {
        $records[] = $row;
    }

    // Stats based on your specific date column
    $today = $conn->query("SELECT COUNT(*) as count FROM consultations WHERE DATE(Consultation_Date) = CURDATE()")->fetch_assoc();
    $total = $conn->query("SELECT COUNT(*) as count FROM consultations")->fetch_assoc();

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