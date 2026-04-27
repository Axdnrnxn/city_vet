<?php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 4) {
    echo json_encode(["error" => "Unauthorized: Staff access only"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Join staff and users tables
$query = $conn->query("
    SELECT s.First_name, s.Last_name, s.Position, u.Email, u.Username 
    FROM staff s
    JOIN users u ON s.User_ID = u.User_ID
    WHERE s.User_ID = $user_id
");

if ($query->num_rows == 0) {
    echo json_encode(["error" => "Staff profile not found"]);
    exit();
}

$staff = $query->fetch_assoc();

echo json_encode([
    "status" => "success",
    "username" => $staff['Username'],
    "first_name" => $staff['First_name'],
    "last_name" => $staff['Last_name'],
    "position" => $staff['Position'] ?? 'Clinic Staff',
    "email" => $staff['Email']
]);
?>