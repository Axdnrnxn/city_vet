<?php
session_start();
header("Content-Type: application/json");
// Disable error reporting to browser to prevent HTML tags in JSON output
error_reporting(0); 
require_once '../../config/db_connection.php'; 

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Priority: use the ID passed from the Admin form
    $owner_id = isset($_POST['owner_id']) ? intval($_POST['owner_id']) : null;

    // Fallback: If no ID in POST, use current session (for Pet Owners)
    if (!$owner_id) {
        $user_id = $_SESSION['user_id'];
        $owner_q = $conn->query("SELECT Owner_ID FROM owners WHERE User_ID = $user_id");
        if($owner_q && $owner_q->num_rows > 0){
            $owner = $owner_q->fetch_assoc();
            $owner_id = $owner['Owner_ID'];
        }
    }

    if (!$owner_id) {
        echo json_encode(["status" => "error", "message" => "Invalid Owner ID"]);
        exit();
    }

    $name = $_POST['name'];
    $species_id = $_POST['species_id'];
    $breed_id = $_POST['breed_id'];
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $weight = $_POST['weight'];
    $color = $_POST['color'];

    if (empty($name) || empty($species_id)) {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO pets (Owner_ID, Name, Species_ID, Breed_ID, Gender, Birthdate, Weight, Color_Markings, Status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')");
    $stmt->bind_param("isiissss", $owner_id, $name, $species_id, $breed_id, $gender, $birthdate, $weight, $color);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Pet registered successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }
}
?>