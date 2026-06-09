<?php
session_start();
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once('../../config/db_connection.php');
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$readOnlyActions = ['get_species', 'get_breeds'];

if (!in_array($action, $readOnlyActions) && (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1)) {
    echo json_encode(["status" => "error", "message" => "Access denied. Administrators only."]);
    exit();
}

try {
    if (!$conn) {
        throw new Exception("Database connection failed.");
    }

    // 1. FETCH SPECIES
    if ($action == 'get_species') {
        $result = $conn->query("SELECT * FROM species WHERE Status = 'active' ORDER BY Species_Name ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    } 

    // 2. FETCH BREEDS (Joining with Species)
    elseif ($action == 'get_breeds') {
        $sql = "SELECT b.Breed_ID, b.Breed_Name, s.Species_Name, b.Species_ID 
                FROM breeds b 
                INNER JOIN species s ON b.Species_ID = s.Species_ID 
                WHERE b.Status = 'active'
                ORDER BY b.Breed_Name ASC";
        $result = $conn->query($sql);
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
    }

    // 3. SAVE SPECIES
    elseif ($action == 'save_species') {
        $name = $_POST['item_name'];
        $stmt = $conn->prepare("INSERT INTO species (Species_Name, Status) VALUES (?, 'active')");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            throw new Exception($stmt->error);
        }
    }

    // 4. SAVE BREED
    elseif ($action == 'save_breed') {
        $name = $_POST['item_name'];
        $s_id = $_POST['Species_ID'];
        $stmt = $conn->prepare("INSERT INTO breeds (Breed_Name, Species_ID, Status) VALUES (?, ?, 'active')");
        $stmt->bind_param("si", $name, $s_id);
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            throw new Exception($stmt->error);
        }
    }

    // 5. DELETE ACTIONS (Soft delete)
    elseif ($action == 'delete_species') {
        $id = $_GET['id'];
        $stmt = $conn->prepare("UPDATE species SET Status = 'inactive' WHERE Species_ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["status" => "success"]);
    }
    elseif ($action == 'delete_breed') {
        $id = $_GET['id'];
        $stmt = $conn->prepare("UPDATE breeds SET Status = 'inactive' WHERE Breed_ID = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(["status" => "success"]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
