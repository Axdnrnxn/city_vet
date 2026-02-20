<?php
// File: api/master/services.php
session_start();
header("Content-Type: application/json");

// 1. SECURITY GATEKEEPER (RBAC)
// Check if user is logged in AND is an Administrator (Role ID 1)
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 1) {
    http_response_code(403);
    echo json_encode(["status" => "error", "message" => "Access Denied: Administrators only."]);
    exit();
}

require_once '../../config/db_connection.php';

// 2. DATA VALIDATION HELPER
function validateInput($data) {
    $errors = [];
    if (empty($data['service_name'])) $errors[] = "Service Name is required.";
    if (empty($data['category_id'])) $errors[] = "Category is required.";
    if (!isset($data['price']) || !is_numeric($data['price'])) $errors[] = "Valid Price is required.";
    return $errors;
}

$method = $_SERVER['REQUEST_METHOD'];

// =========================================================
// 3. READ (List Records) - Only shows 'Active' by default
// =========================================================
if ($method === 'GET') {
    // Join with Categories to get the readable name (e.g., "Surgery" instead of "2")
    $sql = "SELECT s.Service_ID, s.Service_Name, s.Price, s.Status, c.Category_Name, s.Category_ID 
            FROM Services s
            JOIN Service_Categories c ON s.Category_ID = c.Category_ID
            WHERE s.Status = 'active' 
            ORDER BY s.Service_Name ASC";
            
    $result = $conn->query($sql);
    $services = [];
    while ($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
    echo json_encode($services);
    exit();
}

// =========================================================
// HANDLE POST REQUESTS (Create, Update, Soft Delete)
// =========================================================
if ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $action = $data['action'] ?? '';

    // -----------------------------------------------------
    // SCENARIO A: CREATE (Add New Record)
    // -----------------------------------------------------
    if ($action === 'create') {
        $errors = validateInput($data);
        if (!empty($errors)) {
            echo json_encode(["status" => "error", "message" => $errors[0]]);
            exit();
        }

        // Check No Duplicates
        $check = $conn->prepare("SELECT Service_ID FROM Services WHERE Service_Name = ? AND Status = 'active'");
        $check->bind_param("s", $data['service_name']);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Service name already exists."]);
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO Services (Category_ID, Service_Name, Price) VALUES (?, ?, ?)");
        $stmt->bind_param("isd", $data['category_id'], $data['service_name'], $data['price']);
        
        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Service created successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database error: " . $conn->error]);
        }
    }

    // -----------------------------------------------------
    // SCENARIO B: UPDATE (Edit Existing Record)
    // -----------------------------------------------------
    elseif ($action === 'update') {
        $errors = validateInput($data);
        if (!empty($errors)) {
            echo json_encode(["status" => "error", "message" => $errors[0]]);
            exit();
        }

        // Check duplicates (excluding self)
        $check = $conn->prepare("SELECT Service_ID FROM Services WHERE Service_Name = ? AND Service_ID != ? AND Status = 'active'");
        $check->bind_param("si", $data['service_name'], $data['service_id']);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Service name already exists."]);
            exit();
        }

        $stmt = $conn->prepare("UPDATE Services SET Category_ID=?, Service_Name=?, Price=? WHERE Service_ID=?");
        $stmt->bind_param("isdi", $data['category_id'], $data['service_name'], $data['price'], $data['service_id']);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Service updated."]);
        }
    }

    // -----------------------------------------------------
    // SCENARIO C: SOFT DELETE (Set Status to Inactive)
    // -----------------------------------------------------
    elseif ($action === 'delete') {
        if (empty($data['service_id'])) {
            echo json_encode(["status" => "error", "message" => "ID required."]);
            exit();
        }

        // Soft Delete: Update status instead of deleting row
        $stmt = $conn->prepare("UPDATE Services SET Status = 'inactive' WHERE Service_ID = ?");
        $stmt->bind_param("i", $data['service_id']);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Service archived."]);
        }
    }
}
?>