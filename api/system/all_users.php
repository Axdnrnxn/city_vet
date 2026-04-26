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
}
?>