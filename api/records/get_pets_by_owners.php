<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

$owner_id = isset($_GET['owner_id']) ? intval($_GET['owner_id']) : 0;

$sql = "SELECT p.Pet_ID, p.Name, b.Breed_Name 
        FROM pets p 
        LEFT JOIN breeds b ON p.Breed_ID = b.Breed_ID 
        WHERE p.Owner_ID = ? AND (p.Status = 'active' OR p.Status = 'Active')";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$result = $stmt->get_result();

$pets = [];
while($row = $result->fetch_assoc()) {
    $pets[] = $row;
}
echo json_encode($pets);
?>