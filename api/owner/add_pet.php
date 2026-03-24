<?php
session_start();
require_once('../../config/db_connection.php');
header('Content-Type: application/json');

// 1. Session Check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// 2. Resolve Owner_ID from User_ID (to fix Foreign Key error)
$owner_query = $conn->prepare("SELECT Owner_ID FROM owners WHERE User_ID = ? LIMIT 1");
$owner_query->bind_param("i", $user_id);
$owner_query->execute();
$owner_result = $owner_query->get_result();

if ($owner_result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Owner profile not found."]);
    exit;
}

$owner_data = $owner_result->fetch_assoc();
$owner_id = $owner_data['Owner_ID'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $species_id = $_POST['species_id'] ?? null;
    $breed_id = !empty($_POST['breed_id']) ? $_POST['breed_id'] : null;
    $gender = $_POST['gender'] ?? 'Unknown';
    $birthdate = $_POST['birthdate'] ?? '';
    $color = $_POST['color'] ?? '';
    $weight = $_POST['weight'] ?? 0;

    // --- IMAGE UPLOAD LOGIC (Fixed Syntax) ---
    $profile_pic = null;
    if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] == 0) {
        $target_dir = "../../uploads/pets/";
        if (!is_dir($target_dir)) mkdir($target_dir, 0777, true); 
        
        $file_extension = pathinfo($_FILES["pet_image"]["name"], PATHINFO_EXTENSION);
        $file_name = uniqid() . "." . $file_extension; 
        
        // Ensure there is a semicolon at the end of this line!
        if (move_uploaded_file($_FILES["pet_image"]["tmp_name"], $target_dir . $file_name)) {
            $profile_pic = $file_name;
        }
    }

    // 3. Prepare and Execute INSERT
    $sql = "INSERT INTO pets (Owner_ID, Name, Species_ID, Breed_ID, Gender, Birthdate, Color_Markings, Weight, Profile_Pic, Status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
            
    $stmt = $conn->prepare($sql);
    
    // Type definitions: i = int, s = string, d = double (for weight)
    $stmt->bind_param("isiisssds", $owner_id, $name, $species_id, $breed_id, $gender, $birthdate, $color, $weight, $profile_pic);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Pet registered successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database error: " . $stmt->error]);
    }
}
?>