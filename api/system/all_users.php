<?php
header("Content-Type: application/json");

// 1. Include the connection file
include_once '../../config/db_connection.php'; 

// 2. Check if $conn exists (this variable comes from db_connection.php)
if (!$conn) {
    die(json_encode(["error" => "Database connection variable not found"]));
}

$method = $_SERVER['REQUEST_METHOD'];

// --- GET: Fetch All Users ---
if ($method === 'GET') {
    // We join the 'users' table with a 'roles' table to get the name of the role
    $query = "SELECT 
                u.User_ID, 
                u.Username, 
                u.Email, 
                u.Phone_number, 
                u.Status, 
                u.Role_ID
              FROM users u 
              ORDER BY u.Created_At DESC";

    $result = $conn->query($query);
    
    $users = [];
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }

    echo json_encode($users);
}

// --- POST: Update or Toggle Status ---
if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if ($data->action === 'update_credentials') {
        $email = $data->email;
        $id = $data->staff_id; // Using staff_id from your JS
        
        if (!empty($data->password)) {
            // Update Email and Password
            $pass_hash = password_hash($data->password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET Email = ?, Password_Hash = ? WHERE User_ID = ?");
            $stmt->bind_param("ssi", $email, $pass_hash, $id);
        } else {
            // Update Email only
            $stmt = $conn->prepare("UPDATE users SET Email = ? WHERE User_ID = ?");
            $stmt->bind_param("si", $email, $id);
        }
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        } else {
            echo json_encode(["status" => "error", "message" => $conn->error]);
        }
    }

    if ($data->action === 'toggle_status') {
        $newStatus = ($data->current_status === 'active') ? 'inactive' : 'active';
        $stmt = $conn->prepare("UPDATE users SET Status = ? WHERE User_ID = ?");
        $stmt->bind_param("si", $newStatus, $data->staff_id);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success"]);
        }
    }

    // Add this inside the if ($method === 'POST') block in api/system/all_users.php

if ($data->action === 'update_profile') {
    session_start();
    $userId = $_SESSION['user_id'];

    if (!$userId) {
        die(json_encode(["status" => "error", "message" => "Unauthorized session."]));
    }

    // 1. SAFELY GRAB VARIABLES
    $current_password_input = isset($data->current_password) ? $data->current_password : '';
    $username = isset($data->username) ? $data->username : '';
    $email = isset($data->email) ? $data->email : '';
    $new_password = isset($data->password) ? $data->password : '';

    // 2. VERIFY CURRENT PASSWORD
    $verify_stmt = $conn->prepare("SELECT Password_Hash FROM users WHERE User_ID = ?");
    $verify_stmt->bind_param("i", $userId);
    $verify_stmt->execute();
    $result = $verify_stmt->get_result();
    $user_data = $result->fetch_assoc();

    if (!$user_data || !password_verify($current_password_input, $user_data['Password_Hash'])) {
        die(json_encode(["status" => "error", "message" => "Verification failed. Incorrect current password."]));
    }

    // 3. BUILD USERS TABLE UPDATE (This updates Username, Email, and optionally Password)
    if (!empty($new_password)) {
        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET Username = ?, Email = ?, Password_Hash = ? WHERE User_ID = ?");
        $stmt->bind_param("sssi", $username, $email, $new_hash, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE users SET Username = ?, Email = ? WHERE User_ID = ?");
        $stmt->bind_param("ssi", $username, $email, $userId);
    }

    // 4. EXECUTE AND SYNC TABLES
    if ($stmt->execute()) {
        
        // --- FIXED: SYNCHRONIZE WITH THE OWNERS TABLE ---
        // We ONLY update First_name here. Email is already updated in the users table above.
        $update_owner = $conn->prepare("UPDATE owners SET First_name = ? WHERE User_ID = ?");
        $update_owner->bind_param("si", $username, $userId);
        $update_owner->execute();

        // Update the active session
        $_SESSION['username'] = $username; 

        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Update failed."]);
    }
    
    // Always exit to ensure no extra HTML is printed
    exit(); 
}
}
?>