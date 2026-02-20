<?php
// File: api/auth/register.php
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);

// 1. Validate Empty Fields
if (empty($data['email']) || empty($data['password']) || empty($data['fname']) || empty($data['lname'])) {
    echo json_encode(["status" => "error", "message" => "Please fill in all required fields."]);
    exit();
}

// 2. Validate Password Match
if ($data['password'] !== $data['confirm_password']) {
    echo json_encode(["status" => "error", "message" => "Passwords do not match."]);
    exit();
}

// 3. Check for Duplicate Email/Username
$check = $conn->prepare("SELECT User_ID FROM users WHERE Email = ? OR Username = ?");
// We use the Email as the Username for simplicity
$check->bind_param("ss", $data['email'], $data['email']); 
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "This Email is already registered."]);
    exit();
}

// 4. Begin Transaction (Create User + Create Owner)
$conn->begin_transaction();
try {
    // A. Create User Account (Role 3 = Pet Owner)
    $hashed_pass = password_hash($data['password'], PASSWORD_BCRYPT);
    $role_id = 3;
    
    // Insert into USERS
    $stmt1 = $conn->prepare("INSERT INTO users (Role_ID, Username, Password_Hash, Email, Phone_number) VALUES (?, ?, ?, ?, ?)");
    $stmt1->bind_param("issss", $role_id, $data['email'], $hashed_pass, $data['email'], $data['phone']);
    $stmt1->execute();
    $new_user_id = $conn->insert_id;

    // B. Create Owner Profile
    // Combine Address + Barangay for full address
    $full_address = $data['address'] . ", " . $data['barangay'];
    
    // Insert into OWNERS
    $stmt2 = $conn->prepare("INSERT INTO owners (User_ID, First_name, Last_name, Contact_Number, Address) VALUES (?, ?, ?, ?, ?)");
    $stmt2->bind_param("issss", $new_user_id, $data['fname'], $data['lname'], $data['phone'], $full_address);
    $stmt2->execute();

    // Commit Transaction
    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Registration Successful! Please Login."]);

} catch (Exception $e) {
    $conn->rollback(); // Undo if error
    echo json_encode(["status" => "error", "message" => "System Error: " . $e->getMessage()]);
}
?>