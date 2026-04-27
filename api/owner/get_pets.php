<?php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php'; // Adjust path if needed

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// 1. Get Owner ID, First Name, AND Email by joining owners and users tables
$owner_query = $conn->query("
    SELECT o.Owner_ID, o.First_name, u.Email 
    FROM owners o
    JOIN users u ON o.User_ID = u.User_ID
    WHERE o.User_ID = $user_id
");

if ($owner_query->num_rows == 0) {
    echo json_encode(["error" => "Owner profile not found"]);
    exit();
}

$owner = $owner_query->fetch_assoc();
$owner_id = $owner['Owner_ID'];
$email = $owner['Email']; // We now have the email from the database!

// 2. Get Pets
$pets_query = $conn->query("SELECT Pet_ID, Name, Status FROM pets WHERE Owner_ID = $owner_id");
$pets = [];

while ($row = $pets_query->fetch_assoc()) {
    $pets[] = $row;
}

// 3. Send everything back to the frontend
echo json_encode([
    "owner_name" => $owner['First_name'],
    "email" => $email, // <-- This is what your frontend was missing
    "pets" => $pets
]);
?>