<?php
// File: api/auth/login.php
session_start();
header("Content-Type: application/json; charset=UTF-8");

// Adjust this path if your folder structure changed
require_once '../../config/db_connection.php';
require_once '../system/audit_helper.php';

function writeAuditLog($conn, $userId, $action, $tableAffected, $recordId = 0) {
    return auditLog($conn, $userId, $action, $tableAffected, $recordId, ['event_type' => 'security']);
}

$data = json_decode(file_get_contents("php://input"), true);
if (is_null($data)) { $data = $_POST; }

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    auditLog($conn, null, 'Login Failed', 'sessions', 0, ['event_type' => 'security', 'outcome' => 'failure', 'metadata' => ['reason' => 'missing_credentials']]);
    echo json_encode(["status" => "error", "message" => "Please provide both email and password."]);
    exit();
}

// 1. Find User
$stmt = $conn->prepare("SELECT User_ID, Username, Password_Hash, Role_ID FROM users WHERE Email = ? AND Status = 'active' LIMIT 1");
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
        $_SESSION['logged_in'] = true;
        $_SESSION['last_activity'] = time();

        writeAuditLog($conn, (int)$user['User_ID'], "Login", "sessions", 0);

        // 3. Set Redirect URL based on Role
        // Note: Adjust these paths to match your actual folder names
        $redirect = 'index.html'; 
        if ($user['Role_ID'] == 1) { $redirect = 'views/admin/dashboard.html'; } // Admin
        if ($user['Role_ID'] == 2) { $redirect = 'views/vet/dashboard.html'; }   // Vet
        if ($user['Role_ID'] == 3) { $redirect = 'views/owner/dashboard.html'; }// Owner
        if ($user['Role_ID'] == 4) { $redirect = 'views/staff/dashboard_staff.html'; }// Staff

        echo json_encode([
            "status" => "success", 
            "message" => "Welcome back!", 
            "redirect" => $redirect,
            "role" => $user['Role_ID']
        ]);
    } else {
        auditLog($conn, (int)$user['User_ID'], 'Login Failed', 'sessions', 0, ['event_type' => 'security', 'outcome' => 'failure', 'metadata' => ['reason' => 'invalid_credentials']]);
        echo json_encode(["status" => "error", "message" => "Incorrect Password."]);
    }
} else {
    auditLog($conn, null, 'Login Failed', 'sessions', 0, ['event_type' => 'security', 'outcome' => 'failure', 'metadata' => ['reason' => 'invalid_credentials']]);
    echo json_encode(["status" => "error", "message" => "Account not found or inactive."]);
}

$stmt->close();
$conn->close();
?>
