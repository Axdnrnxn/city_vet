<?php
// api/owner/add_pet.php
require_once('../../config/db_connection.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $owner_id = $_POST['owner_id'];
    $name = $_POST['name'];
    $species_id = $_POST['species_id'];
    $breed_id = $_POST['breed_id'] ?? null;
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $color = $_POST['color'];
    $weight = $_POST['weight'];

    // --- IMAGE UPLOAD LOGIC ---
    $profile_pic = null;
    if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] == 0) {
        $target_dir = "../../uploads/pets/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true); // Create folder if missing
        
        $file_extension = pathinfo($_FILES["pet_image"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid() . "." . $file_extension; // Generate random name like 60a1b...jpg
        $target_file = $target_dir . $file_name;
        
        if (move_uploaded_file($_FILES["pet_image"]["tmp_name"], $target_file)) {
            $profile_pic = $file_name;
        }
    }

    $sql = "INSERT INTO pets (Owner_ID, Name, Species_ID, Breed_ID, Gender, Birthdate, Color_Markings, Weight, Profile_Pic, Status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isiisssds", $owner_id, $name, $species_id, $breed_id, $gender, $birthdate, $color, $weight, $profile_pic);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Pet registered successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
    }
}
?>