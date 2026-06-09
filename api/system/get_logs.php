<?php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

// Only Admin (Role 1) can view logs
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

// Updated query to match your exact columns
$sql = "SELECT l.Log_ID, l.Action, l.Table_Affected, l.Record_ID, l.Timestamp, COALESCE(u.Username, 'Unknown') AS Username 
        FROM audit_logs l 
        LEFT JOIN users u ON l.User_ID = u.User_ID 
        ORDER BY l.Timestamp DESC 
        LIMIT 100";

$result = $conn->query($sql);
$logs = [];

if ($result) {
    while($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
}

echo json_encode($logs);
?>
