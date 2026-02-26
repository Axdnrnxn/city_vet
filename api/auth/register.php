<?php
// File: api/auth/register.php
header("Content-Type: application/json; charset=UTF-8");
require_once '../../config/db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);
if (is_null($data)) { $data = $_POST; }

// 1. Validate Fields
if (empty($data['email']) || empty($data['password']) || empty($data['fname']) || empty($data['lname'])) {
    echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
    exit();
}

// 2. Check for Duplicate Email
$check = $conn->prepare("SELECT User_ID FROM users WHERE Email = ?");
$check->bind_param("s", $data['email']); 
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "This Email is already registered."]);
    exit();
}

// 3. Begin Transaction
$conn->begin_transaction();
try {
    $hashed_pass = password_hash($data['password'], PASSWORD_BCRYPT);
    $role_id = 3; // Role 3 = Pet Owner
    
    // A. Insert into USERS
    $stmt1 = $conn->prepare("INSERT INTO users (Role_ID, Username, Password_Hash, Email, Phone_number) VALUES (?, ?, ?, ?, ?)");
    $stmt1->bind_param("issss", $role_id, $data['email'], $hashed_pass, $data['email'], $data['phone']);
    $stmt1->execute();
    $new_user_id = $conn->insert_id;

    // B. Insert into OWNERS
    $full_address = ($data['address'] ?? '') . ", " . ($data['barangay'] ?? '');
    $stmt2 = $conn->prepare("INSERT INTO owners (User_ID, First_name, Last_name, Contact_Number, Address) VALUES (?, ?, ?, ?, ?)");
    $stmt2->bind_param("issss", $new_user_id, $data['fname'], $data['lname'], $data['phone'], $full_address);
    $stmt2->execute();

    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Registration Successful! Please Login."]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "System Error: " . $e->getMessage()]);
}

$conn->close();
?>