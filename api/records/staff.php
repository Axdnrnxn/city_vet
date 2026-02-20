<?php
// File: api/records/staff.php
header("Content-Type: application/json");
require_once '../../config/db_connection.php';

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents("php://input"), true);

// --- GET: Fetch All Staff ---
if ($method === 'GET') {
    $sql = "SELECT s.Staff_ID, s.First_name, s.Last_name, s.Position, s.Status, 
                   u.User_ID, u.Email, u.Phone_number, r.Role_name 
            FROM staff s
            JOIN users u ON s.User_ID = u.User_ID
            JOIN roles r ON u.Role_ID = r.Role_ID
            ORDER BY s.Last_name ASC";
    
    $result = $conn->query($sql);
    $staff = [];
    while ($row = $result->fetch_assoc()) {
        $staff[] = $row;
    }
    echo json_encode($staff);
    exit();
}

// --- POST: Add or Update Staff ---
if ($method === 'POST') {
    $action = $input['action'] ?? '';

    // 1. ADD NEW EMPLOYEE
    if ($action === 'create') {
        if(empty($input['email']) || empty($input['password'])) {
            echo json_encode(["status" => "error", "message" => "Credentials required"]); exit();
        }

        $conn->begin_transaction();
        try {
            // A. Create User Login
            $hashed_pass = password_hash($input['password'], PASSWORD_BCRYPT);
            $role_id = $input['role_id']; // 1=Admin, 2=Vet, etc.
            
            $stmt1 = $conn->prepare("INSERT INTO users (Role_ID, Username, Password_Hash, Email, Phone_number) VALUES (?, ?, ?, ?, ?)");
            $username = explode('@', $input['email'])[0]; // Simple username gen
            $stmt1->bind_param("issss", $role_id, $username, $hashed_pass, $input['email'], $input['phone']);
            $stmt1->execute();
            $user_id = $conn->insert_id;

            // B. Create Staff Profile
            $stmt2 = $conn->prepare("INSERT INTO staff (User_ID, First_name, Last_name, Position) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("isss", $user_id, $input['fname'], $input['lname'], $input['position']);
            $stmt2->execute();

            $conn->commit();
            echo json_encode(["status" => "success", "message" => "Employee added successfully"]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
        }
    }

    // 2. TOGGLE STATUS (Soft Delete/Reactivate)
    if ($action === 'toggle_status') {
        $new_status = $input['current_status'] === 'active' ? 'resigned' : 'active';
        $stmt = $conn->prepare("UPDATE staff SET Status = ? WHERE Staff_ID = ?");
        $stmt->bind_param("si", $new_status, $input['staff_id']);
        
        // Also update User login status
        $stmt2 = $conn->prepare("UPDATE users SET Status = ? WHERE User_ID = (SELECT User_ID FROM staff WHERE Staff_ID = ?)");
        $user_status = $new_status === 'active' ? 'active' : 'inactive';
        $stmt2->bind_param("si", $user_status, $input['staff_id']);

        if ($stmt->execute() && $stmt2->execute()) {
            echo json_encode(["status" => "success", "message" => "Status updated"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Update failed"]);
        }
    }
}
?>