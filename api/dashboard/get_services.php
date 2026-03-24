<?php
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

try {
    // Matches your exact table columns: Service_ID, Service_Name, Price, Status
    // We only fetch 'active' ones so 'Anti-Rabies Shot' (inactive) won't show up.
    $sql = "SELECT Service_ID, Service_Name, Price 
            FROM services 
            WHERE Status = 'active'";
    
    $result = $conn->query($sql);

    if (!$result) {
        throw new Exception("Query Failed: " . $conn->error);
    }

    $services = [];
    while($row = $result->fetch_assoc()) {
        $services[] = $row;
    }

    // This sends the clean list to your Walk-in Management panel
    echo json_encode($services);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>