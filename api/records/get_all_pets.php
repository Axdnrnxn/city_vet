<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

// Corrected table name: 'owners'
$sql = "SELECT p.Pet_ID, p.Name, CONCAT(o.First_name, ' ', o.Last_name) as Owner_Name 
        FROM pets p 
        JOIN owners o ON p.Owner_ID = o.Owner_ID 
        WHERE p.Status = 'active'
        ORDER BY p.Name ASC";

$result = $conn->query($sql);
$pets = [];

if($result) {
    while($row = $result->fetch_assoc()) {
        $pets[] = $row;
    }
}
echo json_encode($pets);
?>