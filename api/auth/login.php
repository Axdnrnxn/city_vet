<?php
// File: api/auth/login.php
session_start();
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

$data = json_decode(file_get_contents("php://input"), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Email and Password are required."]);
    exit();
}

// Check Active Users Only
$stmt = $conn->prepare("SELECT User_ID, Username, Password_Hash, Role_ID FROM Users WHERE Email = ? AND Status = 'active'");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    if (password_verify($password, $user['Password_Hash'])) {
        // Set Session
        $_SESSION['user_id'] = $user['User_ID'];
        $_SESSION['role_id'] = $user['Role_ID'];
        $_SESSION['username'] = $user['Username'];

        // Role-Based Redirect Logic
        $redirect = 'index.html'; // Default
        if ($user['Role_ID'] == 1) { 
            $redirect = 'views/admin/services_master.html'; // Admin Dashboard
        } elseif ($user['Role_ID'] == 2) { 
            $redirect = 'views/vet/dashboard.html'; // Vet Dashboard
        } elseif ($user['Role_ID'] == 3) { 
            $redirect = 'views/owners/dashboard.html'; // Owner Dashboard
        }

        echo json_encode(["status" => "success", "message" => "Login Successful", "redirect" => $redirect]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid Password."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "User not found."]);
}
?>