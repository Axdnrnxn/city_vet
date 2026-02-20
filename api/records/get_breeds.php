<?php
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

// Fetch all active breeds and join with species for clarity
$sql = "SELECT Breed_ID, Species_ID, Breed_Name FROM breeds WHERE Status = 'active'";
$result = $conn->query($sql);

$breedsBySpecies = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $s_id = $row['Species_ID'];
        if (!isset($breedsBySpecies[$s_id])) {
            $breedsBySpecies[$s_id] = [];
        }
        $breedsBySpecies[$s_id][] = [
            'id' => $row['Breed_ID'],
            'name' => $row['Breed_Name']
        ];
    }
}

echo json_encode($breedsBySpecies);
?>