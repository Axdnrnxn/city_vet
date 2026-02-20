<?php
// File: api/auth/register.php
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

// 1. Get Input
$data = json_decode(file_get_contents("php://input"), true);

// 2. Validate Input
if (empty($data['email']) || empty($data['password']) || empty($data['first_name']) || empty($data['last_name']) || empty($data['username'])) {
    echo json_encode(["status" => "error", "message" => "All fields are required."]);
    exit();
}

// 3. Check for Duplicates (Email or Username)
$check = $conn->prepare("SELECT User_ID FROM Users WHERE Email = ? OR Username = ?");
$check->bind_param("ss", $data['email'], $data['username']);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "Email or Username already taken."]);
    exit();
}

// 4. Create User Account (Role ID 3 = Pet Owner)
$conn->begin_transaction();
try {
    // A. Insert into USERS table
    $hashed_password = password_hash($data['password'], PASSWORD_BCRYPT);
    $role_id = 3; // Default to Pet Owner
    
    $stmt = $conn->prepare("INSERT INTO Users (Role_ID, Username, Password_Hash, Email, Phone_number) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $role_id, $data['username'], $hashed_password, $data['email'], $data['phone']);
    $stmt->execute();
    $new_user_id = $conn->insert_id;

    // B. Insert into OWNERS table (Linked Profile)
    $stmt2 = $conn->prepare("INSERT INTO Owners (User_ID, First_name, Last_name, Address) VALUES (?, ?, ?, ?)");
    $stmt2->bind_param("isss", $new_user_id, $data['first_name'], $data['last_name'], $data['address']);
    $stmt2->execute();

    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Account created! You can now login."]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Registration failed: " . $e->getMessage()]);
}
?>