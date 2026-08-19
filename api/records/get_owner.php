<?php
// File: api/records/get_owner.php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

// Archived records are an administrator-only recovery feature.
if (!isset($_SESSION['user_id']) || (int)($_SESSION['role_id'] ?? 0) !== 1) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Administrator access required"]);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "No ID provided"]);
    exit();
}

$owner_id = intval($_GET['id']);

// 1. Get Owner Details (Join with Users for Email)
$sql_owner = "SELECT o.*, u.Email, u.Username 
              FROM owners o 
              JOIN users u ON o.User_ID = u.User_ID 
              WHERE o.Owner_ID = ?";
$stmt = $conn->prepare($sql_owner);
$stmt->bind_param("i", $owner_id);
$stmt->execute();
$owner_result = $stmt->get_result();

if ($owner_result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Owner not found"]);
    exit();
}

$owner_data = $owner_result->fetch_assoc();

// 2. Get Owner's Pets
$sql_pets = "SELECT p.*, s.Species_Name, b.Breed_Name 
             FROM pets p
             LEFT JOIN species s ON p.Species_ID = s.Species_ID
             LEFT JOIN breeds b ON p.Breed_ID = b.Breed_ID
             WHERE p.Owner_ID = ? AND p.Status != 'archived'";
$stmt_pets = $conn->prepare($sql_pets);
$stmt_pets->bind_param("i", $owner_id);
$stmt_pets->execute();
$pets_result = $stmt_pets->get_result();

$pets_data = [];
while ($pet = $pets_result->fetch_assoc()) {
    $pets_data[] = $pet;
}

// Keep archived pets separate so they cannot be mistaken for active records.
$archived_stmt = $conn->prepare("SELECT p.*, s.Species_Name, b.Breed_Name
    FROM pets p
    LEFT JOIN species s ON p.Species_ID = s.Species_ID
    LEFT JOIN breeds b ON p.Breed_ID = b.Breed_ID
    WHERE p.Owner_ID = ? AND p.Status = 'archived'
    ORDER BY p.Pet_ID DESC");
$archived_stmt->bind_param("i", $owner_id);
$archived_stmt->execute();
$archived_result = $archived_stmt->get_result();
$archived_pets = [];
while ($pet = $archived_result->fetch_assoc()) {
    $archived_pets[] = $pet;
}

// 3. Return Combined Data
echo json_encode([
    "status" => "success",
    "owner" => $owner_data,
    "pets" => $pets_data,
    "archived_pets" => $archived_pets
]);
?>
