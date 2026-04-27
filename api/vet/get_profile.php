<?php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php'; 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Join staff and users tables to get the Vet's profile data
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

$vet = $query->fetch_assoc();

echo json_encode([
    "status" => "success",
    "username" => $vet['Username'],
    "first_name" => $vet['First_name'],
    "last_name" => $vet['Last_name'],
    "position" => $vet['Position'],
    "email" => $vet['Email']
]);
?>