<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

// Fetching all active owners for the searchable datalist
$sql = "SELECT Owner_ID, First_name, Last_name, Contact_number FROM owners WHERE Status = 'active' ORDER BY First_name ASC";
$result = $conn->query($sql);

$owners = [];
while($row = $result->fetch_assoc()) {
    $owners[] = $row;
}
echo json_encode($owners);
?>