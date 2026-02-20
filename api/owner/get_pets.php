<?php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php'; // Adjust path if needed

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error" => "Not logged in"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get Owner ID
$owner_query = $conn->query("SELECT Owner_ID, First_name FROM owners WHERE User_ID = $user_id");
if ($owner_query->num_rows == 0) {
    echo json_encode(["error" => "Owner profile not found"]);
    exit();
}
$owner = $owner_query->fetch_assoc();
$owner_id = $owner['Owner_ID'];

// Get Pets
$pets_query = $conn->query("SELECT Pet_ID, Name, Status FROM pets WHERE Owner_ID = $owner_id");
$pets = [];

while ($row = $pets_query->fetch_assoc()) {
    $pets[] = $row;
}

echo json_encode([
    "owner_name" => $owner['First_name'],
    "pets" => $pets
]);
?>