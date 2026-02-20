<?php
// File: api/auth/login.php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

// 1. Find User
$stmt = $conn->prepare("SELECT User_ID, Username, Password_Hash, Role_ID FROM users WHERE Email = ? AND Status = 'active'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    // 2. Verify Password
    if (password_verify($password, $user['Password_Hash'])) {
        $_SESSION['user_id'] = $user['User_ID'];
        $_SESSION['role_id'] = $user['Role_ID'];
        $_SESSION['username'] = $user['Username'];

        // 3. Set Redirect URL based on Role
        $redirect = 'index.html'; 
        if ($user['Role_ID'] == 1) { $redirect = 'views/admin/dashboard.html'; } // Admin
        if ($user['Role_ID'] == 2) { $redirect = 'views/vet/dashboard.html'; }   // Vet
        if ($user['Role_ID'] == 3) { $redirect = 'views/owner/dashboard.html'; }// Owner

        echo json_encode(["status" => "success", "message" => "Welcome back!", "redirect" => $redirect]);
    } else {
        echo json_encode(["status" => "error", "message" => "Incorrect Password."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Account not found."]);
}
?>