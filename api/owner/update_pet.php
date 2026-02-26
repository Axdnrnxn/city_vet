<?php
// api/owner/update_pet.php
require_once('../../config/db_connection.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = $_POST['pet_id'];
    $name = $_POST['name'];
    $species_id = $_POST['species_id'];
    $breed_id = $_POST['breed_id'] ?? null;
    $gender = $_POST['gender'];
    $birthdate = $_POST['birthdate'];
    $color = $_POST['color'];
    $weight = $_POST['weight'];

    // --- IMAGE UPLOAD LOGIC ---
    $profile_pic = null;

    // Check if a file was selected to upload
    if (isset($_FILES['pet_image']) && $_FILES['pet_image']['name'] != "") {
        
        if ($_FILES['pet_image']['error'] !== 0) {
            echo json_encode(["status" => "error", "message" => "Image Upload Error Code: " . $_FILES['pet_image']['error']]);
            exit();
        }

        $target_dir = "../../uploads/pets/";
        
        // Final fallback to create folder if you forgot Step 1
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["pet_image"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid() . "." . $file_extension;
        $target_file = $target_dir . $file_name;
        
        // Attempt to move the file
        if (move_uploaded_file($_FILES["pet_image"]["tmp_name"], $target_file)) {
            $profile_pic = $file_name;
        } else {
            // LOUD ERROR: It will tell you if the folder is missing or blocked!
            echo json_encode(["status" => "error", "message" => "Failed to move uploaded file to the 'uploads/pets/' folder. Check folder permissions."]);
            exit();
        }
    }

    // Dynamic SQL Query
    if ($profile_pic) {
        $sql = "UPDATE pets SET Name=?, Species_ID=?, Breed_ID=?, Gender=?, Birthdate=?, Color_Markings=?, Weight=?, Profile_Pic=? WHERE Pet_ID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siisssdsi", $name, $species_id, $breed_id, $gender, $birthdate, $color, $weight, $profile_pic, $pet_id);
    } else {
        $sql = "UPDATE pets SET Name=?, Species_ID=?, Breed_ID=?, Gender=?, Birthdate=?, Color_Markings=?, Weight=? WHERE Pet_ID=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("siisssdi", $name, $species_id, $breed_id, $gender, $birthdate, $color, $weight, $pet_id);
    }

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Pet updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request Method"]);
}
?>