<?php
// 1. Point to your config file
require_once('../../config/db_connection.php');

header('Content-Type: application/json');

try {
    // 2. Your config uses $conn, so we check for that
    if (!isset($conn)) {
        throw new Exception("Database connection variable (\$conn) not found.");
    }

    // 3. MySQLi Query syntax
    $query = "SELECT Diagnosis_ID, Diagnosis_Name FROM diagnosis_master ORDER BY Diagnosis_Name ASC";
    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    // 4. Fetch data into an array
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // 5. Return JSON
    echo json_encode($data);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error", 
        "message" => $e->getMessage()
    ]);
}
?>