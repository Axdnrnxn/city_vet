<?php
require_once('../../config/db_connection.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = $_POST['pet_id'];
    $name = $_POST['name'];
    $species_id = $_POST['species_id'];
    $breed_id = $_POST['breed_id'];
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $color = $_POST['color'];
    $weight = $_POST['weight'];

    $sql = "UPDATE pets SET 
            Name = ?, 
            Species_ID = ?, 
            Breed_ID = ?, 
            Gender = ?, 
            Birthdate = ?, 
            Color_Markings = ?, 
            Weight = ? 
            WHERE Pet_ID = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siisssdi", $name, $species_id, $breed_id, $gender, $birthdate, $color, $weight, $pet_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Pet updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => $conn->error]);
    }
}
?>