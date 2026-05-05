<?php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php'; 

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$user_id = $_SESSION['user_id'];
$query = $conn->prepare("SELECT Username, Email FROM users WHERE User_ID = ?");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    echo json_encode(["error" => "Profile not found"]);
    exit();
}

$admin = $result->fetch_assoc();
echo json_encode([
    "status" => "success",
    "username" => $admin['Username'],
    "email" => $admin['Email']
]);
?>