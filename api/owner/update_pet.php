<?php
// api/owner/update_pet.php
session_start();
require_once('../../config/db_connection.php');
header('Content-Type: application/json');

function writeAuditLog($conn, $userId, $action, $tableAffected, $recordId = 0) {
    if (!$userId) return;

    $stmt = $conn->prepare("INSERT INTO audit_logs (User_ID, Action, Table_Affected, Record_ID) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issi", $userId, $action, $tableAffected, $recordId);
        $stmt->execute();
        $stmt->close();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        echo json_encode(["status" => "error", "message" => "Unauthorized"]);
        exit();
    }

    $actorId = (int)$_SESSION['user_id'];
    $roleId = isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : 0;
    $pet_id = (int)($_POST['pet_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $species_id = (int)($_POST['species_id'] ?? 0);
    $breed_id = !empty($_POST['breed_id']) ? (int)$_POST['breed_id'] : null;
    $gender = $_POST['gender'] ?? 'Unknown';
    $birthdate = $_POST['birthdate'] ?? '';
    $color = trim($_POST['color'] ?? '');
    $weight = $_POST['weight'] ?? 0;

    if (!$pet_id || $name === '' || !$species_id || empty($birthdate)) {
        echo json_encode(["status" => "error", "message" => "Pet name, species, and birthdate are required."]);
        exit();
    }

    if (strtotime($birthdate) > time()) {
        echo json_encode(["status" => "error", "message" => "Birthdate cannot be in the future."]);
        exit();
    }

    if (!in_array($gender, ['Male', 'Female', 'Unknown'])) {
        echo json_encode(["status" => "error", "message" => "Invalid pet gender."]);
        exit();
    }

    if (!is_numeric($weight) || $weight < 0) {
        echo json_encode(["status" => "error", "message" => "Weight must be a valid number."]);
        exit();
    }

    if ($roleId === 1 || $roleId === 4) {
        $access_stmt = $conn->prepare("SELECT Pet_ID FROM pets WHERE Pet_ID = ? AND Status != 'archived'");
        $access_stmt->bind_param("i", $pet_id);
    } else {
        $access_stmt = $conn->prepare("
            SELECT p.Pet_ID
            FROM pets p
            JOIN owners o ON p.Owner_ID = o.Owner_ID
            WHERE p.Pet_ID = ? AND o.User_ID = ? AND p.Status != 'archived'
        ");
        $access_stmt->bind_param("ii", $pet_id, $actorId);
    }
    $access_stmt->execute();
    if ($access_stmt->get_result()->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "Pet not found or access denied."]);
        exit();
    }

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
        writeAuditLog($conn, $actorId, "Update Pet Record", "pets", (int)$pet_id);
        echo json_encode(["status" => "success", "message" => "Pet updated successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid Request Method"]);
}
?>
